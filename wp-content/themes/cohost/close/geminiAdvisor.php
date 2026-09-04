<?php

if (!class_exists('GeminiSalesAdvisor')) {

class GeminiSalesAdvisor
{
    // Stable, free-tier-eligible model as of this writing.
    // If Google renames/deprecates this, swap the string below — nothing
    // else in this file needs to change.
    private const MODEL = 'gemini-3.6-flash';

    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /**
     * Send a lead's activity transcript to Gemini and get back sales advice.
     *
     * @param  string $leadName   Display name of the lead, for context in the prompt.
     * @param  string $transcript The formatted transcript from CloseLeadExporter.
     * @return string             The advice text returned by Gemini.
     * @throws \Exception on missing key, HTTP failure, or unexpected response shape.
     */
    public static function getAdvice(string $leadName, string $transcript): string
    {
        if (!defined('GEM_API_KEY') || empty(trim(GEM_API_KEY))) {
            throw new \Exception('GEM_API_KEY is not set. Add it in close/apiKey.php.');
        }

        // Gemini's free tier has a generous but real context window; trim
        // extremely long transcripts so requests don't fail or get truncated
        // server-side. ~60,000 characters is a safe margin.
        $maxChars = 60000;
        if (strlen($transcript) > $maxChars) {
            $transcript = "...(earlier activity truncated)...\n\n" . substr($transcript, -$maxChars);
        }

        $systemInstruction =
            "You are an experienced sales coach reviewing the full communication history " .
            "between a property manager and one of their leads/clients. Based ONLY on the transcript " .
            "provided, give practical, specific sales advice. Structure your response with " .
            "these sections, using the exact headers below:\n\n" .
            "STATUS: Where the deal currently stands, in one or two sentences.\n" .
            "OBJECTIONS: Any hesitations, concerns, or objections the lead has raised.\n" .
            "SENTIMENT: The lead's overall tone/engagement level (hot, warm, cold, unresponsive, etc.) and why.\n" .
            "NEXT STEP: The single most important recommended next action.\n" .
            "DRAFT MESSAGE: A short draft follow-up message (email or text) the rep could send now.\n\n" .
            "Be concise and concrete. If the transcript is too thin to assess something, say so " .
            "plainly rather than guessing.";

        $userPrompt = "Lead name: {$leadName}\n\nTranscript:\n\n{$transcript}";

        $requestBody = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $userPrompt]],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => 3072,
            ],
        ];

        $url = self::ENDPOINT . self::MODEL . ':generateContent?key=' . rawurlencode(GEM_API_KEY);

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($requestBody),
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            throw new \Exception('Request to Gemini failed: ' . $response->get_error_message());
        }

        $httpCode = wp_remote_retrieve_response_code($response);
        $rawBody  = wp_remote_retrieve_body($response);
        $decoded  = json_decode($rawBody, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $errMsg = $decoded['error']['message'] ?? $rawBody;
            throw new \Exception("Gemini API HTTP {$httpCode}: {$errMsg}");
        }

        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($text === null) {
            // Common cause: response was blocked by safety filters, or the
            // shape changed. Surface whatever we got for debugging.
            $finishReason = $decoded['candidates'][0]['finishReason'] ?? 'unknown';
            throw new \Exception(
                "Gemini returned no text (finishReason: {$finishReason}). Raw response: "
                . substr($rawBody, 0, 500)
            );
        }

        $finishReason = $decoded['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'MAX_TOKENS') {
            // The model had more to say but hit the output length cap.
            // Flag this clearly instead of silently handing back a cut-off answer.
            $text = trim($text) . "\n\n[⚠ Response was cut off by the length limit. "
                . "Try again, or shorten the transcript if this keeps happening.]";
        }

        return trim($text);
    }
}

} // end class_exists guard
