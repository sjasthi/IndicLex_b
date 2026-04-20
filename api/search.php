<?php
// ============================================================
// api/search.php — Public REST search (GET)
// Pretty URL: /api/search (see .htaccess)
// Parameters: q, dict, mode  — optional: limit, offset
// ============================================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/dictionary_search.php';

header('Content-Type: application/json; charset=utf-8');

function api_json(array $data, int $code): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_json(['ok' => false, 'error' => 'Method not allowed. Use GET.'], 405);
}

$q    = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$dict = isset($_GET['dict']) ? trim((string) $_GET['dict']) : 'all';
$mode = isset($_GET['mode']) ? trim((string) $_GET['mode']) : 'substring';

$limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

if ($q === '') {
    api_json(['ok' => false, 'error' => 'Missing or empty required parameter: q'], 400);
}

if (strlen($q) > 200) {
    api_json(['ok' => false, 'error' => 'Parameter q exceeds maximum length (200 characters)'], 400);
}

if (!in_array($mode, indiclex_valid_search_modes(), true)) {
    api_json([
        'ok'    => false,
        'error' => 'Invalid mode. Allowed: exact, prefix, suffix, substring',
    ], 400);
}

if ($limit < 1 || $limit > 100) {
    api_json(['ok' => false, 'error' => 'Parameter limit must be between 1 and 100'], 400);
}

if ($offset < 0) {
    api_json(['ok' => false, 'error' => 'Parameter offset must be non-negative'], 400);
}

$dict_norm = $dict === '' ? 'all' : $dict;
$dict_meta = ['id' => 'all', 'name' => 'All dictionaries'];

if ($dict_norm !== 'all') {
    if (!ctype_digit($dict_norm)) {
        api_json(['ok' => false, 'error' => 'Parameter dict must be "all" or a numeric dictionary id'], 400);
    }
    $dict_id = (int) $dict_norm;
    $check   = indiclex_dictionary_exists($db, $dict_id);
    if (!$check['ok']) {
        api_json(['ok' => false, 'error' => 'Database error'], 500);
    }
    if (!$check['exists']) {
        api_json(['ok' => false, 'error' => 'Dictionary not found'], 404);
    }
    $dict_norm = $dict_id;
    $stmt      = $db->prepare('SELECT id, name FROM dictionaries WHERE id = ? LIMIT 1');
    if (!$stmt) {
        api_json(['ok' => false, 'error' => 'Database error'], 500);
    }
    $stmt->bind_param('i', $dict_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $dict_meta = ['id' => (int) $row['id'], 'name' => (string) $row['name']];
}

$run = indiclex_dictionary_search($db, $q, $mode, $dict_norm, $limit, $offset);
if (!$run['ok']) {
    api_json(['ok' => false, 'error' => 'Search failed'], 500);
}

$out_rows = [];
foreach ($run['rows'] as $r) {
    $out_rows[] = [
        'id'                 => isset($r['id']) ? (int) $r['id'] : null,
        'word'               => $r['word'] ?? '',
        'telugu'             => $r['telugu'] ?? '',
        'hindi'              => $r['hindi'] ?? '',
        'transliteration'    => $r['transliteration'] ?? '',
        'part_of_speech'     => $r['part_of_speech'] ?? '',
        'example_source'     => $r['example_source'] ?? '',
        'example_target'     => $r['example_target'] ?? '',
        'dictionary_id'      => isset($r['dictionary_id']) ? (int) $r['dictionary_id'] : null,
        'dictionary_name'    => $r['dictionary_name'] ?? '',
    ];
}

api_json([
    'ok'         => true,
    'query'      => $q,
    'mode'       => $mode,
    'dictionary' => $dict_meta,
    'total'      => $run['total'],
    'limit'      => $limit,
    'offset'     => $offset,
    'results'    => $out_rows,
], 200);
