<?php
require_once __DIR__ . '/CloseApiClient.php';
require_once __DIR__ . '/apiKey.php';

if (!class_exists('CloseLeadExporter')) {

class CloseLeadExporter
{
    /**
     * Fetch a lead's full activity history and return it as a formatted string.
     * Throws \Exception on API failure (missing lead, bad key, etc).
     */
    public static function getFormattedExport(string $leadId): string
    {
        $client = new CloseApiClient(CLOSE_API_KEY);

        $lead     = $client->getLead($leadId);
        $leadName = $lead['display_name'] ?? $leadId;

        $emails = self::fetchAll($client, 'activity/email', ['lead_id' => $leadId]);
        $sms    = self::fetchAll($client, 'activity/sms',   ['lead_id' => $leadId]);
        $calls  = self::fetchAll($client, 'activity/call',  ['lead_id' => $leadId]);
        $notes  = self::fetchAll($client, 'activity/note',  ['lead_id' => $leadId]);

        $all = [];
        foreach ($emails as $item) $all[] = ['date' => $item['date_created'] ?? '', 'type' => 'EMAIL', 'data' => $item];
        foreach ($sms    as $item) $all[] = ['date' => $item['date_created'] ?? '', 'type' => 'SMS',   'data' => $item];
        foreach ($calls  as $item) $all[] = ['date' => $item['date_created'] ?? '', 'type' => 'CALL',  'data' => $item];
        foreach ($notes  as $item) $all[] = ['date' => $item['date_created'] ?? '', 'type' => 'NOTE',  'data' => $item];

        usort($all, fn($a, $b) => strcmp($a['date'], $b['date']));

        $out  = str_repeat('=', 81) . "\n";
        $out .= "LEAD EXPORT\n";
        $out .= "Lead    : {$leadName}\n";
        $out .= "ID      : {$leadId}\n";
        $out .= "Exported: " . (new DateTime())->format('Y-m-d H:i:s') . " UTC\n";
        $out .= "Total   : " . count($all) . " activities (" . count($emails) . " emails, " . count($sms) . " SMS, " . count($calls) . " calls, " . count($notes) . " notes)\n";
        $out .= str_repeat('=', 81) . "\n\n";

        foreach ($all as $entry) {
            $type = $entry['type'];
            $d    = $entry['data'];
            $date = self::fmt($entry['date']);

            $out .= str_repeat('-', 81) . "\n";

            switch ($type) {
                case 'EMAIL':
                    $direction = strtolower($d['direction'] ?? '') === 'outbound' ? 'SENT' : 'RECEIVED';
                    $out .= "[EMAIL - {$direction}]  {$date}\n";
                    $out .= "From    : " . ($d['sender'] ?? '?') . "\n";
                    $out .= "To      : " . implode(', ', (array)($d['to'] ?? [])) . "\n";
                    if (!empty($d['subject'])) $out .= "Subject : {$d['subject']}\n";
                    if (!empty($d['cc']))      $out .= "CC      : " . implode(', ', (array)$d['cc']) . "\n";
                    $out .= "\n";
                    $body = $d['body_text'] ?? strip_tags($d['body_html'] ?? '');
                    $out .= trim($body) . "\n";
                    break;

                case 'SMS':
                    $direction = strtolower($d['direction'] ?? '') === 'outbound' ? 'SENT' : 'RECEIVED';
                    $out .= "[SMS - {$direction}]  {$date}\n";
                    $out .= "From    : " . ($d['local_phone']  ?? '?') . "\n";
                    $out .= "To      : " . ($d['remote_phone'] ?? '?') . "\n\n";
                    $out .= trim($d['text'] ?? '(no text)') . "\n";
                    break;

                case 'CALL':
                    $direction = strtolower($d['direction'] ?? '') === 'outbound' ? 'OUTBOUND' : 'INBOUND';
                    $duration  = isset($d['duration']) ? self::fmtDuration((int)$d['duration']) : '?';
                    $status    = $d['disposition'] ?? $d['status'] ?? '?';
                    $out .= "[CALL - {$direction}]  {$date}\n";
                    $out .= "Phone   : " . ($d['remote_phone'] ?? '?') . "\n";
                    $out .= "Duration: {$duration}\n";
                    $out .= "Status  : {$status}\n";
                    if (!empty($d['note'])) $out .= "\n" . trim($d['note']) . "\n";
                    break;

                case 'NOTE':
                    $out .= "[NOTE]  {$date}\n\n";
                    $out .= trim($d['note'] ?? '(empty)') . "\n";
                    break;
            }

            $out .= "\n";
        }

        $out .= str_repeat('=', 81) . "\n";
        $out .= "END OF EXPORT\n";
        $out .= str_repeat('=', 81) . "\n";

        return $out;
    }

    private static function fetchAll(CloseApiClient $client, string $endpoint, array $params = []): array
    {
        $results = [];
        $skip    = 0;

        while (true) {
            $page    = $client->get($endpoint, array_merge($params, ['_limit' => 100, '_skip' => $skip]));
            $batch   = $page['data']     ?? [];
            $hasMore = $page['has_more'] ?? false;

            $results = array_merge($results, $batch);
            $skip   += count($batch);

            if (!$hasMore || count($batch) === 0) break;
        }

        return $results;
    }

    private static function fmt(string $ts): string
    {
        if (empty($ts)) return '(no date)';
        try {
            $dt = new DateTime($ts);
            return $dt->format('Y-m-d H:i:s') . ' UTC';
        } catch (Exception $e) {
            return $ts;
        }
    }

    private static function fmtDuration(int $seconds): string
    {
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;
        return sprintf('%d:%02d', $m, $s);
    }
}

} // end class_exists guard

// ── Standalone behavior: still works exactly as before if you open ───────────
// ── export.php?lead_id=... directly in a browser (downloads a .txt file) ────
if (!defined('CLOSE_ADMIN_INCLUDE')) {

    $leadId = trim($_GET['lead_id'] ?? '');
    if (empty($leadId)) {
        die('Missing lead_id parameter. Usage: export.php?lead_id=lead_xxx');
    }

    try {
        $text = CloseLeadExporter::getFormattedExport($leadId);
    } catch (\Exception $e) {
        die('Error: ' . $e->getMessage());
    }

    $client   = new CloseApiClient(CLOSE_API_KEY);
    $lead     = $client->getLead($leadId);
    $leadName = $lead['display_name'] ?? $leadId;

    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9_\-]/i', '_', $leadName) . '_export.txt"');
    echo $text;
}