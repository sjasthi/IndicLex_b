<?php
// ============================================================
// pages/import.php — English | Telugu | Hindi import
// Teacher's file format: Col A=English, Col B=Telugu, Col C=Hindi
// No header row expected
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['dictionary_file'])) {
    header('Location: index.php?page=admin_import');
    exit;
}

$file          = $_FILES['dictionary_file'];
$dictionary_id = intval($_POST['dictionary_id'] ?? 1);

// ── File upload error check ──
if ($file['error'] !== UPLOAD_ERR_OK) {
    $msg = match($file['error']) {
        UPLOAD_ERR_INI_SIZE  => 'File too large (server limit).',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit).',
        UPLOAD_ERR_PARTIAL   => 'File only partially uploaded.',
        UPLOAD_ERR_NO_FILE   => 'No file selected.',
        default              => 'Upload error code: ' . $file['error'],
    };
    header('Location: index.php?page=admin_import&error=' . urlencode($msg));
    exit;
}

try {
    // ── Ensure the dictionary record exists ──
    $check_dict = $db->prepare("SELECT id FROM dictionaries WHERE id = ?");
    $check_dict->bind_param("i", $dictionary_id);
    $check_dict->execute();
    $check_dict->store_result();

    if ($check_dict->num_rows === 0) {
        $name = 'English–Telugu–Hindi Dictionary';
        $ins  = $db->prepare("INSERT INTO dictionaries (id, name, source_lang, target_lang) VALUES (?, ?, 'English', 'Telugu/Hindi')");
        $ins->bind_param("is", $dictionary_id, $name);
        $ins->execute();
        $ins->close();
    }
    $check_dict->close();

    // ── Parse the file ──
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $worksheet   = $spreadsheet->getActiveSheet();
    $rows        = $worksheet->toArray();

    if (empty($rows)) {
        header('Location: index.php?page=admin_import&error=' . urlencode('File is empty.'));
        exit;
    }

    // ── Skip header row if present ──
    $firstRow = array_map('trim', array_map('strval', $rows[0]));
    $firstVal = strtolower($firstRow[0] ?? '');
    if (in_array($firstVal, ['word', 'english', 'telugu', 'hindi'])) {
        array_shift($rows); // remove header
    }

    // ── Prepare duplicate check and insert ──
    $check_dup = $db->prepare("
        SELECT id FROM dictionary_entries
        WHERE dictionary_id = ? AND word = ?
    ");

    $stmt = $db->prepare("
        INSERT INTO dictionary_entries (dictionary_id, word, telugu, hindi)
        VALUES (?, ?, ?, ?)
    ");

    $import_count  = 0;
    $skipped_count = 0;

    foreach ($rows as $row) {
        // Col A = English, Col B = Telugu, Col C = Hindi
        $english = trim((string)($row[0] ?? ''));
        $telugu  = trim((string)($row[1] ?? ''));
        $hindi   = trim((string)($row[2] ?? ''));

        // Skip empty or purely numeric rows
        if ($english === '' || ($telugu === '' && $hindi === '')) continue;
        if (is_numeric($english)) continue;

        // Duplicate check on English word
        $check_dup->bind_param("is", $dictionary_id, $english);
        $check_dup->execute();
        $check_dup->store_result();

        if ($check_dup->num_rows > 0) {
            $skipped_count++;
            continue;
        }

        $stmt->bind_param("isss", $dictionary_id, $english, $telugu, $hindi);
        $stmt->execute();
        $import_count++;
    }

    $check_dup->close();
    $stmt->close();

    $params = 'success=' . $import_count . '&skipped=' . $skipped_count;
    header('Location: index.php?page=admin_import&' . $params);
    exit;

} catch (Exception $e) {
    $msg = 'Error: ' . $e->getMessage();
    header('Location: index.php?page=admin_import&error=' . urlencode($msg));
    exit;
}
?>