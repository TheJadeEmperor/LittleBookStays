<?php
require 'apiKey.php'; 

/**
 * Close CRM REST API Client
 *
 * Docs: https://developer.close.com
 *
 * SETUP (WAMP):
 *   1. Open this file and set YOUR_API_KEY_HERE below  
 *   2. Visit http://localhost/close_api/CloseApiClient.php in your browser
 *      — or run:  php CloseApiClient.php  in your terminal
 */
 

// =============================================================================
// CloseApiClient  —  base class
// =============================================================================

class CloseApiClient
{
    private const BASE_URL = 'https://api.close.com/api/v1';

    private  $apiKey;
    private     $timeout;

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    /**
     * @param string $apiKey  Your Close API key (Settings -> Developer -> API Keys)
     * @param int    $timeout HTTP timeout in seconds
     */
    public function __construct(string $apiKey, int $timeout = 30)
    {
        if (empty(trim($apiKey)) || $apiKey === 'YOUR_API_KEY_HERE') {
            throw new \InvalidArgumentException(
                'Please set your Close API key at the top of CloseApiClient.php'
            );
        }

        $this->apiKey  = trim($apiKey);
        $this->timeout = $timeout;
    }

    // -------------------------------------------------------------------------
    // Public HTTP helpers
    // -------------------------------------------------------------------------

    /** GET  — e.g. $client->get('me')  or  $client->get('lead', ['_limit'=>5]) */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, null, $params);
    }

    /** POST — e.g. $client->post('lead', ['name' => 'Acme']) */
    public function post(string $endpoint, array $body = []): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    /** PUT  — e.g. $client->put('lead/lead_123', ['name' => 'New Name']) */
    public function put(string $endpoint, array $body = []): array
    {
        return $this->request('PUT', $endpoint, $body);
    }

    /** DELETE — e.g. $client->delete('lead/lead_123') */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    // -------------------------------------------------------------------------
    // Convenience methods
    // -------------------------------------------------------------------------

    /** Fetch your own user profile — great connection test */
    public function me(): array
    {
        return $this->get('me');
    }

    /**
     * List leads.
     *
     * @param  string $query  Full-text search (optional)
     * @param  int    $limit  Results per page (max 200)
     * @param  int    $skip   Pagination offset
     */
    public function listLeads(string $query = '', int $limit = 100, int $skip = 0): array
    {
        $params = ['_limit' => $limit, '_skip' => $skip];
        if ($query !== '') {
            $params['query'] = $query;
        }
        return $this->get('lead', $params);
    }

    /** Get a single lead by ID, e.g. 'lead_abc123' */
    public function getLead(string $leadId): array
    {
        return $this->get("lead/{$leadId}");
    }

    /**
     * Create a lead.
     *
     * @param  string $name   Company / lead name
     * @param  array  $extra  Extra fields: url, status_id, contacts, addresses...
     */
    public function createLead(string $name, array $extra = []): array
    {
        return $this->post('lead', array_merge(['name' => $name], $extra));
    }

    // -------------------------------------------------------------------------
    // Core request engine
    // -------------------------------------------------------------------------

    /**
     * Execute a cURL request with HTTP Basic Auth (API key as username, no password).
     *
     * Authentication scheme per Close docs:
     *   Authorization: Basic base64("YOUR_API_KEY:")
     *   The colon after the key signals an empty password.
     *
     * @throws \RuntimeException on cURL error or non-2xx response
     */
    private function request(
        string  $method,
        string  $endpoint,
        ?array  $body   = null,
        array   $params = []
    ): array {
        $url = $this->buildUrl($endpoint, $params);

        $ch = curl_init();

        // Auth: API key = username, password always empty
        curl_setopt($ch, CURLOPT_USERPWD,        $this->apiKey . ':');
        curl_setopt($ch, CURLOPT_HTTPAUTH,        CURLAUTH_BASIC);

        // Core
        curl_setopt($ch, CURLOPT_URL,             $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch, CURLOPT_TIMEOUT,         $this->timeout);

        // HTTPS — required for WAMP; ensure cacert.pem is set in php.ini
        // (curl.cainfo = "C:/wamp64/bin/php/phpX.X.X/cacert.pem")
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);

        // Follow redirects (handles any http->https redirect)
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);
        curl_setopt($ch, CURLOPT_MAXREDIRS,       5);

        // Headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        // Method-specific
        switch (strtoupper($method)) {
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;

            case 'POST':
                curl_setopt($ch, CURLOPT_POST,        true);
                curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($body ?? new \stdClass()));
                break;

            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS,    json_encode($body ?? new \stdClass()));
                break;

            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            default:
                curl_close($ch);
                throw new \InvalidArgumentException("Unsupported HTTP method: {$method}");
        }

        $rawResponse = curl_exec($ch);
        $httpCode    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl    = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $curlErrno   = curl_errno($ch);
        $curlError   = curl_error($ch);
        curl_close($ch);

        // cURL-level failure (DNS, SSL, timeout...)
        if ($rawResponse === false || $curlErrno !== 0) {
            throw new \RuntimeException(
                "cURL error #{$curlErrno}: {$curlError}\n" .
                "Attempted URL : {$url}\n" .
                "Final URL     : {$finalUrl}\n\n" .
                "WAMP SSL tip  : download cacert.pem from https://curl.se/ca/cacert.pem\n" .
                "and set  curl.cainfo = \"C:/wamp64/bin/php/phpX.X.X/cacert.pem\"  in php.ini"
            );
        }

        // Decode JSON
        $decoded = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "JSON decode failed: " . json_last_error_msg() . "\n" .
                "HTTP {$httpCode}  URL: {$finalUrl}\n" .
                "Raw response: " . substr($rawResponse, 0, 500)
            );
        }

        // HTTP errors
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorDetail = $decoded['error'] ?? $decoded['message'] ?? json_encode($decoded);
            throw new \RuntimeException(
                "Close API HTTP {$httpCode}: {$errorDetail}\n" .
                "URL: {$finalUrl}"
            );
        }

        return $decoded;
    }

    /**
     * Build the full request URL.
     * All Close API endpoints end with a trailing slash: /api/v1/me/
     */
    private function buildUrl(string $endpoint, array $params = []): string
    {
        $endpoint = trim($endpoint, '/');           // normalise input
        $url      = self::BASE_URL . '/' . $endpoint . '/';

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}


// =============================================================================
// CONNECTION TEST
// Runs when you open this file in a browser OR execute: php CloseApiClient.php
// =============================================================================

try {
    $client = new CloseApiClient(CLOSE_API_KEY);

    $isCli = (php_sapi_name() === 'cli');
    $nl    = $isCli ? "\n" : "<br>";

    if (!$isCli) {
        echo '<pre style="font-family:monospace;font-size:14px;padding:20px;">';
    }

    echo '=== Close CRM — Connection Test ===' . $nl . $nl;

    // 1. Who am I?
    $me = $client->me();

    echo 'Authentication : OK' . $nl;
    echo 'Name           : ' . ($me['display_name'] ?? 'N/A') . $nl;
    echo 'Email          : ' . ($me['email']        ?? 'N/A') . $nl;
    echo 'User ID        : ' . ($me['id']           ?? 'N/A') . $nl;
    echo 'Org ID         : ' . ($me['organizations'][0]['id'] ?? 'N/A') . $nl;
    echo $nl;

    // 2. List first 5 leads
    $leads = $client->listLeads('', 5);
    $rows  = $leads['data'] ?? [];
    $total = $leads['total_results'] ?? '?';

    echo 'First ' . count($rows) . ' lead(s) (total in account: ' . $total . '):' . $nl;
    if (empty($rows)) {
        echo '  (no leads found)' . $nl;
    } else {
        foreach ($rows as $lead) {
            echo '  [' . $lead['id'] . ']  ' . $lead['display_name'] . $nl;
        }
    }

    echo $nl . 'All done — CloseApiClient is ready to use.' . $nl;

    if (!$isCli) {
        echo '</pre>';
    }

} catch (\Exception $e) {
    $isCli = (php_sapi_name() === 'cli');
    $msg   = $e->getMessage();

    if ($isCli) {
        echo "ERROR:\n{$msg}\n";
    } else {
        echo '<pre style="color:red;font-family:monospace;padding:20px;">';
        echo "ERROR:\n" . htmlspecialchars($msg);
        echo '</pre>';
    }
    exit(1);
}