<?php
require 'CloseApiClient.php';
require 'apiKey.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────────
$client = new CloseApiClient(CLOSE_API_KEY);
$leadId = trim($_GET['lead_id'] ?? '');

if (empty($leadId)) {
    die('Missing lead_id parameter. Usage: export.php?lead_id=lead_xxx');
}

// ── Fetch lead info ───────────────────────────────────────────────────────────
$lead     = $client->getLead($leadId);
$leadName = $lead['display_name'] ?? $leadId;

// ── Helper: paginate any activity endpoint ────────────────────────────────────
function fetchAll(CloseApiClient $client, string $endpoint, array $params = []): array
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

// ── Helper: format a UTC timestamp to readable local-ish string ───────────────
function fmt(string $ts): string
{
    if (empty($ts)) return '(no date)';
    try {
        $dt = new DateTime($ts);
        return $dt->format('Y-m-d H:i:s') . ' UTC';
    } catch (Exception $e) {
        return $ts;
    }
}

// ── Helper: format duration in seconds to mm:ss ───────────────────────────────
function fmtDuration(int $seconds): string
{
    $m = intdiv($seconds, 60);
    $s = $seconds % 60;
    return sprintf('%d:%02d', $m, $s);
}

// ── Fetch all activity types for this lead ────────────────────────────────────
$emails = fetchAll($client, 'activity/email',    ['lead_id' => $leadId]);
$sms    = fetchAll($client, 'activity/sms',      ['lead_id' => $leadId]);
$calls  = fetchAll($client, 'activity/call',     ['lead_id' => $leadId]);
$notes  = fetchAll($client, 'activity/note',     ['lead_id' => $leadId]);

// ── Merge all into one flat list with a type tag ──────────────────────────────
$all = [];

foreach ($emails as $item) {
    $all[] = [
        'date' => $item['date_created'] ?? '',
        'type' => 'EMAIL',
        'data' => $item,
    ];
}

foreach ($sms as $item) {
    $all[] = [
        'date' => $item['date_created'] ?? '',
        'type' => 'SMS',
        'data' => $item,
    ];
}

foreach ($calls as $item) {
    $all[] = [
        'date' => $item['date_created'] ?? '',
        'type' => 'CALL',
        'data' => $item,
    ];
}

foreach ($notes as $item) {
    $all[] = [
        'date' => $item['date_created'] ?? '',
        'type' => 'NOTE',
        'data' => $item,
    ];
}

// ── Sort all by date ascending ────────────────────────────────────────────────
usort($all, fn($a, $b) => strcmp($a['date'], $b['date']));

// ── Output as plain text file download ───────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9_\-]/i', '_', $leadName) . '_export.txt"');

echo '=================================================================================' . "\n";
echo 'LEAD EXPORT' . "\n";
echo 'Lead    : ' . $leadName . "\n";
echo 'ID      : ' . $leadId . "\n";
echo 'Exported: ' . (new DateTime())->format('Y-m-d H:i:s') . ' UTC' . "\n";
echo 'Total   : ' . count($all) . ' activities (' . count($emails) . ' emails, ' . count($sms) . ' SMS, ' . count($calls) . ' calls, ' . count($notes) . ' notes)' . "\n";
echo '=================================================================================' . "\n\n";

foreach ($all as $entry) {
    $type = $entry['type'];
    $d    = $entry['data'];
    $date = fmt($entry['date']);

    echo '---------------------------------------------------------------------------------' . "\n";

    switch ($type) {

        case 'EMAIL':
            $direction = strtolower($d['direction'] ?? '') === 'outbound' ? 'SENT' : 'RECEIVED';
            echo '[EMAIL - ' . $direction . ']  ' . $date . "\n";
            echo 'From    : ' . ($d['sender'] ?? '?') . "\n";
            echo 'To      : ' . implode(', ', (array)($d['to'] ?? [])) . "\n";
            if (!empty($d['subject'])) {
                echo 'Subject : ' . $d['subject'] . "\n";
            }
            if (!empty($d['cc'])) {
                echo 'CC      : ' . implode(', ', (array)$d['cc']) . "\n";
            }
            echo "\n";
            // body_text is plain text version; fall back to body_html stripped
            $body = $d['body_text'] ?? strip_tags($d['body_html'] ?? '');
            echo trim($body) . "\n";
            break;

        case 'SMS':
            $direction = strtolower($d['direction'] ?? '') === 'outbound' ? 'SENT' : 'RECEIVED';
            echo '[SMS - ' . $direction . ']  ' . $date . "\n";
            echo 'From    : ' . ($d['local_phone'] ?? '?') . "\n";
            echo 'To      : ' . ($d['remote_phone'] ?? '?') . "\n";
            echo "\n";
            echo trim($d['text'] ?? '(no text)') . "\n";
            break;

        case 'CALL':
            $direction = strtolower($d['direction'] ?? '') === 'outbound' ? 'OUTBOUND' : 'INBOUND';
            $duration  = isset($d['duration']) ? fmtDuration((int)$d['duration']) : '?';
            $status    = $d['disposition'] ?? $d['status'] ?? '?';
            echo '[CALL - ' . $direction . ']  ' . $date . "\n";
            echo 'Phone   : ' . ($d['remote_phone'] ?? '?') . "\n";
            echo 'Duration: ' . $duration . "\n";
            echo 'Status  : ' . $status . "\n";
            if (!empty($d['note'])) {
                echo "\n";
                echo trim($d['note']) . "\n";
            }
            break;

        case 'NOTE':
            echo '[NOTE]  ' . $date . "\n";
            echo "\n";
            echo trim($d['note'] ?? '(empty)') . "\n";
            break;
    }

    echo "\n";
}

echo '=================================================================================' . "\n";
echo 'END OF EXPORT' . "\n";
echo '=================================================================================' . "\n";