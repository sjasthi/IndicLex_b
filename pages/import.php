<?php
// ============================================================
// pages/import.php — Handles the Excel/CSV import POST
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
    $check_stmt = $db->prepare("SELECT id FROM dictionaries WHERE id = ?");
    $check_stmt->bind_param("i", $dictionary_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows === 0) {
        $name = 'Dictionary ' . $dictionary_id;
        $ins  = $db->prepare("INSERT INTO dictionaries (id, name) VALUES (?, ?)");
        $ins->bind_param("is", $dictionary_id, $name);
        $ins->execute();
        $ins->close();
    }
    $check_stmt->close();

    // ── Parse the Excel/CSV file ──
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $worksheet   = $spreadsheet->getActiveSheet();
    $rows        = $worksheet->toArray();

    // Remove header row
    array_shift($rows);

    // ── Prepare statements ──
    $check_dup = $db->prepare("
        SELECT id FROM dictionary_entries
        WHERE dictionary_id = ? AND word = ?
    ");

    $stmt = $db->prepare("
        INSERT INTO dictionary_entries (dictionary_id, word, translation)
        VALUES (?, ?, ?)
    ");

    $import_count  = 0;
    $skipped_count = 0;

    foreach ($rows as $row) {
        $word        = isset($row[0]) ? trim((string)$row[0]) : '';
        $translation = isset($row[1]) ? trim((string)$row[1]) : '';

        // Skip empty rows
        if ($word === '' || $translation === '') continue;

        // Check for duplicate before inserting
        $check_dup->bind_param("is", $dictionary_id, $word);
        $check_dup->execute();
        $check_dup->store_result();

        if ($check_dup->num_rows > 0) {
            $skipped_count++;
            continue;
        }

        $stmt->bind_param("iss", $dictionary_id, $word, $translation);
        $stmt->execute();
        $import_count++;
    }

    $check_dup->close();
    $stmt->close();

    // ── Redirect back with results ──
    $params = 'success=' . $import_count . '&skipped=' . $skipped_count;
    header('Location: index.php?page=admin_import&' . $params);
    exit;

} catch (Exception $e) {
    $msg = 'Error processing file: ' . $e->getMessage();
    header('Location: index.php?page=admin_import&error=' . urlencode($msg));
    exit;
}
?>