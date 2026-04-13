<?php
// ============================================================
// pages/admin_crud_api.php — JSON API for admin CRUD + stats
// Standalone (no layout). Session admin required.
// ============================================================

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

function json_out($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function stats_payload($db)
{
    $total_words = 0;
    $r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries");
    if ($r) {
        $total_words = (int) $r->fetch_assoc()['total'];
    }

    $total_dicts = 0;
    $r = $db->query("SELECT COUNT(*) AS total FROM dictionaries");
    if ($r) {
        $total_dicts = (int) $r->fetch_assoc()['total'];
    }

    $total_users = 0;
    $r = $db->query("SELECT COUNT(*) AS total FROM users");
    if ($r) {
        $total_users = (int) $r->fetch_assoc()['total'];
    }

    $with_telugu = 0;
    $r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries WHERE telugu != '' AND telugu IS NOT NULL");
    if ($r) {
        $with_telugu = (int) $r->fetch_assoc()['total'];
    }

    $with_hindi = 0;
    $r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries WHERE hindi != '' AND hindi IS NOT NULL");
    if ($r) {
        $with_hindi = (int) $r->fetch_assoc()['total'];
    }

    return [
        'ok'            => true,
        'total_words'   => $total_words,
        'total_dicts'   => $total_dicts,
        'total_users'   => $total_users,
        'with_telugu'   => $with_telugu,
        'with_hindi'    => $with_hindi,
    ];
}

// Merge POST params (form + optional JSON body)
$raw = file_get_contents('php://input');
$json = null;
if ($raw !== '' && $raw !== false) {
    $json = json_decode($raw, true);
}
$in = array_merge($_GET, $_POST);
if (is_array($json)) {
    $in = array_merge($in, $json);
}

$action = isset($in['action']) ? trim((string) $in['action']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($action === '' || $action === 'stats')) {
    json_out(stats_payload($db));
}

if ($action === 'stats') {
    json_out(stats_payload($db));
}

if ($action === 'entry_get') {
    $id = (int) ($in['id'] ?? 0);
    if ($id < 1) {
        json_out(['ok' => false, 'error' => 'Valid id is required.'], 400);
    }
    $stmt = $db->prepare("
        SELECT id, dictionary_id, word, telugu, hindi, transliteration, part_of_speech, example_source, example_target
        FROM dictionary_entries WHERE id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if (!$row) {
        json_out(['ok' => false, 'error' => 'Entry not found.'], 404);
    }
    json_out([
        'ok'    => true,
        'entry' => [
            'id'               => (int) $row['id'],
            'dictionary_id'    => (int) $row['dictionary_id'],
            'word'             => $row['word'],
            'telugu'           => $row['telugu'],
            'hindi'            => $row['hindi'],
            'transliteration'  => $row['transliteration'],
            'part_of_speech'   => $row['part_of_speech'],
            'example_source'   => $row['example_source'],
            'example_target'   => $row['example_target'],
        ],
    ]);
}

if ($action === 'dictionaries') {
    $rows = [];
    $r = $db->query("
        SELECT d.id, d.name, d.description, d.source_lang, d.target_lang, d.created_at,
               COUNT(de.id) AS entry_count
        FROM dictionaries d
        LEFT JOIN dictionary_entries de ON de.dictionary_id = d.id
        GROUP BY d.id, d.name, d.description, d.source_lang, d.target_lang, d.created_at
        ORDER BY d.name ASC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $rows[] = [
                'id'           => (int) $row['id'],
                'name'         => $row['name'],
                'description'  => $row['description'],
                'source_lang'  => $row['source_lang'],
                'target_lang'  => $row['target_lang'],
                'created_at'   => $row['created_at'],
                'entry_count'  => (int) $row['entry_count'],
            ];
        }
    }
    json_out(['ok' => true, 'dictionaries' => $rows]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$uid = (int) current_user()['id'];

if ($action === 'dictionary_create') {
    $name = trim((string) ($in['name'] ?? ''));
    $description = trim((string) ($in['description'] ?? ''));
    $source_lang = trim((string) ($in['source_lang'] ?? 'Telugu'));
    $target_lang = trim((string) ($in['target_lang'] ?? 'English'));

    if ($name === '') {
        json_out(['ok' => false, 'error' => 'Name is required.'], 400);
    }

    $stmt = $db->prepare("
        INSERT INTO dictionaries (name, description, source_lang, target_lang, created_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('ssssi', $name, $description, $source_lang, $target_lang, $uid);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        json_out(['ok' => false, 'error' => $err ?: 'Insert failed.'], 500);
    }
    $newId = $stmt->insert_id;
    $stmt->close();
    $resp = stats_payload($db);
    $resp['ok'] = true;
    $resp['id'] = (int) $newId;
    json_out($resp);
}

if ($action === 'dictionary_update') {
    $id = (int) ($in['id'] ?? 0);
    $name = trim((string) ($in['name'] ?? ''));
    $description = trim((string) ($in['description'] ?? ''));
    $source_lang = trim((string) ($in['source_lang'] ?? 'Telugu'));
    $target_lang = trim((string) ($in['target_lang'] ?? 'English'));

    if ($id < 1 || $name === '') {
        json_out(['ok' => false, 'error' => 'Valid id and name are required.'], 400);
    }

    $stmt = $db->prepare("
        UPDATE dictionaries SET name = ?, description = ?, source_lang = ?, target_lang = ?
        WHERE id = ?
    ");
    $stmt->bind_param('ssssi', $name, $description, $source_lang, $target_lang, $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        json_out(['ok' => false, 'error' => $err ?: 'Update failed.'], 500);
    }
    $stmt->close();
    $resp = stats_payload($db);
    $resp['ok'] = true;
    json_out($resp);
}

if ($action === 'dictionary_delete') {
    $id = (int) ($in['id'] ?? 0);
    if ($id < 1) {
        json_out(['ok' => false, 'error' => 'Valid id is required.'], 400);
    }

    $stmt = $db->prepare('DELETE FROM dictionaries WHERE id = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        json_out(['ok' => false, 'error' => $err ?: 'Delete failed.'], 500);
    }
    $stmt->close();
    $resp = stats_payload($db);
    $resp['ok'] = true;
    json_out($resp);
}

if ($action === 'entry_create') {
    $dictionary_id = (int) ($in['dictionary_id'] ?? 0);
    $word = trim((string) ($in['word'] ?? ''));
    $telugu = trim((string) ($in['telugu'] ?? ''));
    $hindi = trim((string) ($in['hindi'] ?? ''));
    $transliteration = trim((string) ($in['transliteration'] ?? ''));
    $part_of_speech = trim((string) ($in['part_of_speech'] ?? ''));
    $example_source = trim((string) ($in['example_source'] ?? ''));
    $example_target = trim((string) ($in['example_target'] ?? ''));

    if ($dictionary_id < 1 || $word === '') {
        json_out(['ok' => false, 'error' => 'Dictionary and word are required.'], 400);
    }

    $chk = $db->prepare('SELECT id FROM dictionaries WHERE id = ?');
    $chk->bind_param('i', $dictionary_id);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows === 0) {
        $chk->close();
        json_out(['ok' => false, 'error' => 'Dictionary not found.'], 404);
    }
    $chk->close();

    $stmt = $db->prepare("
        INSERT INTO dictionary_entries
        (dictionary_id, word, telugu, hindi, transliteration, part_of_speech, example_source, example_target)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'isssssss',
        $dictionary_id,
        $word,
        $telugu,
        $hindi,
        $transliteration,
        $part_of_speech,
        $example_source,
        $example_target
    );
    if (!$stmt->execute()) {
        $errno = $stmt->errno;
        $err = $stmt->error;
        $stmt->close();
        if ($errno === 1062) {
            json_out(['ok' => false, 'error' => 'That word already exists in this dictionary.'], 409);
        }
        json_out(['ok' => false, 'error' => $err ?: 'Insert failed.'], 500);
    }
    $newId = $stmt->insert_id;
    $stmt->close();
    $resp = stats_payload($db);
    $resp['ok'] = true;
    $resp['id'] = (int) $newId;
    json_out($resp);
}

if ($action === 'entry_update') {
    $id = (int) ($in['id'] ?? 0);
    $dictionary_id = (int) ($in['dictionary_id'] ?? 0);
    $word = trim((string) ($in['word'] ?? ''));
    $telugu = trim((string) ($in['telugu'] ?? ''));
    $hindi = trim((string) ($in['hindi'] ?? ''));
    $transliteration = trim((string) ($in['transliteration'] ?? ''));
    $part_of_speech = trim((string) ($in['part_of_speech'] ?? ''));
    $example_source = trim((string) ($in['example_source'] ?? ''));
    $example_target = trim((string) ($in['example_target'] ?? ''));

    if ($id < 1 || $dictionary_id < 1 || $word === '') {
        json_out(['ok' => false, 'error' => 'Valid id, dictionary, and word are required.'], 400);
    }

    $stmt = $db->prepare("
        UPDATE dictionary_entries SET
            dictionary_id = ?, word = ?, telugu = ?, hindi = ?, transliteration = ?,
            part_of_speech = ?, example_source = ?, example_target = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        'isssssssi',
        $dictionary_id,
        $word,
        $telugu,
        $hindi,
        $transliteration,
        $part_of_speech,
        $example_source,
        $example_target,
        $id
    );
    if (!$stmt->execute()) {
        $errno = $stmt->errno;
        $err = $stmt->error;
        $stmt->close();
        if ($errno === 1062) {
            json_out(['ok' => false, 'error' => 'That word already exists in this dictionary.'], 409);
        }
        json_out(['ok' => false, 'error' => $err ?: 'Update failed.'], 500);
    }
    $stmt->close();
    $resp = stats_payload($db);
    $resp['ok'] = true;
    json_out($resp);
}

if ($action === 'entry_delete') {
    $id = (int) ($in['id'] ?? 0);
    if ($id < 1) {
        json_out(['ok' => false, 'error' => 'Valid id is required.'], 400);
    }

    $stmt = $db->prepare('DELETE FROM dictionary_entries WHERE id = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        json_out(['ok' => false, 'error' => $err ?: 'Delete failed.'], 500);
    }
    $stmt->close();
    $resp = stats_payload($db);
    $resp['ok'] = true;
    json_out($resp);
}

json_out(['ok' => false, 'error' => 'Unknown action.'], 400);
