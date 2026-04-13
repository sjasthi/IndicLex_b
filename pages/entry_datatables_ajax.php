<?php
// ============================================================
// pages/entry_datatables_ajax.php
// Server-side DataTables for entries in ONE dictionary (?dictionary_id=)
// ============================================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$dictionary_id = (int) ($_GET['dictionary_id'] ?? 0);
if ($dictionary_id < 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'draw'            => (int) ($_GET['draw'] ?? 1),
        'recordsTotal'    => 0,
        'recordsFiltered' => 0,
        'data'            => [],
        'error'           => 'dictionary_id required',
    ]);
    exit;
}

header('Content-Type: application/json');

$draw    = intval($_GET['draw']   ?? 1);
$start   = intval($_GET['start']  ?? 0);
$length  = intval($_GET['length'] ?? 25);
$search  = $db->real_escape_string($_GET['search']['value'] ?? '');

$columns = ['de.id', 'de.word', 'de.telugu', 'de.hindi', 'de.transliteration', 'de.part_of_speech'];
$order_col = intval($_GET['order'][0]['column'] ?? 0);
$order_dir = strtoupper($_GET['order'][0]['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$order_by  = $columns[$order_col] ?? 'de.id';

$dict_esc = (int) $dictionary_id;
$base = "FROM dictionary_entries de WHERE de.dictionary_id = {$dict_esc}";

$total = (int) $db->query("SELECT COUNT(*) AS c {$base}")->fetch_assoc()['c'];

$where = '';
if ($search !== '') {
    $s = "%{$search}%";
    $where = " AND (de.word LIKE '{$s}' OR de.telugu LIKE '{$s}' OR de.hindi LIKE '{$s}'
              OR de.transliteration LIKE '{$s}' OR de.part_of_speech LIKE '{$s}')";
}

$filtered = (int) $db->query("SELECT COUNT(*) AS c {$base} {$where}")->fetch_assoc()['c'];

$sql = "SELECT de.id, de.word, de.telugu, de.hindi, de.transliteration, de.part_of_speech
        {$base} {$where}
        ORDER BY {$order_by} {$order_dir}
        LIMIT {$length} OFFSET {$start}";

$result = $db->query($sql);
$data   = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'               => $row['id'],
        'word'             => htmlspecialchars($row['word']),
        'telugu'           => htmlspecialchars($row['telugu'] ?: '—'),
        'hindi'            => htmlspecialchars($row['hindi'] ?: '—'),
        'transliteration'  => htmlspecialchars($row['transliteration'] ?: '—'),
        'part_of_speech'   => htmlspecialchars($row['part_of_speech'] ?: '—'),
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered,
    'data'            => $data,
]);
