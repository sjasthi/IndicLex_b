<?php
// ============================================================
// pages/datatables_ajax.php
// Server-side processing endpoint for DataTables
// Called via index.php?page=datatables_ajax
// ============================================================

// Block non-admins
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// ── DataTables parameters ────────────────────────────────────
$draw    = intval($_GET['draw']   ?? 1);
$start   = intval($_GET['start']  ?? 0);
$length  = intval($_GET['length'] ?? 25);
$search  = $db->real_escape_string($_GET['search']['value'] ?? '');

// ── Column ordering ──────────────────────────────────────────
$columns = ['de.id', 'de.word', 'de.telugu', 'de.hindi', 'de.part_of_speech', 'd.name'];
$order_col = intval($_GET['order'][0]['column'] ?? 0);
$order_dir = strtoupper($_GET['order'][0]['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$order_by  = $columns[$order_col] ?? 'de.id';

// ── Base query ───────────────────────────────────────────────
$base = "FROM dictionary_entries de
         JOIN dictionaries d ON de.dictionary_id = d.id";

// ── Total records (no filter) ────────────────────────────────
$total = $db->query("SELECT COUNT(*) AS c {$base}")->fetch_assoc()['c'];

// ── Filtered records ─────────────────────────────────────────
$where = '';
if ($search !== '') {
    $s     = "%{$search}%";
    $where = "WHERE (de.word LIKE '{$s}' OR de.telugu LIKE '{$s}'
                  OR de.hindi LIKE '{$s}' OR d.name LIKE '{$s}')";
}

$filtered = $db->query("SELECT COUNT(*) AS c {$base} {$where}")->fetch_assoc()['c'];

// ── Fetch page of results ─────────────────────────────────────
$sql = "SELECT de.id, de.word, de.telugu, de.hindi,
               de.part_of_speech, d.name AS dictionary_name
        {$base} {$where}
        ORDER BY {$order_by} {$order_dir}
        LIMIT {$length} OFFSET {$start}";

$result = $db->query($sql);
$data   = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'              => $row['id'],
        'word'            => htmlspecialchars($row['word']),
        'telugu'          => htmlspecialchars($row['telugu']          ?: '—'),
        'hindi'           => htmlspecialchars($row['hindi']           ?: '—'),
        'part_of_speech'  => htmlspecialchars($row['part_of_speech']  ?: '—'),
        'dictionary_name' => htmlspecialchars($row['dictionary_name']),
    ];
}

echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered,
    'data'            => $data,
]);