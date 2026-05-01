<?php
// ============================================================
// pages/import.php — Handles Excel/CSV import POST
// Supports two formats:
//   1. Teacher format:  Col A=English, Col B=Telugu, Col C=Hindi (no header)
//   2. Export format:   word,telugu,hindi,transliteration,part_of_speech,
//                       example_source,example_target,dictionary_name (with header)
// ============================================================

// ── Increase limits for large file processing ─────────────────
set_time_limit(300);              // 5 minutes
ini_set('memory_limit', '512M'); // enough for 8000+ row Excel files

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['dictionary_file'])) {
    header('Location: index.php?page=admin_import');
    exit;
}

$file          = $_FILES['dictionary_file'];
$dictionary_id = intval($_POST['dictionary_id'] ?? 1);

// ── File upload error check ──────────────────────────────────
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
    // ── Ensure the dictionary record exists ──────────────────
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

    // ── Parse the file ───────────────────────────────────────
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // For CSV files use the CSV reader directly — faster and no XML issues
    if ($ext === 'csv') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $reader->setInputEncoding('UTF-8');
        $reader->setDelimiter(',');
        $reader->setSheetIndex(0);
        $spreadsheet = $reader->load($file['tmp_name']);
    } else {
        try {
            $reader = IOFactory::createReaderForFile($file['tmp_name']);
            // ── Performance settings — critical for large files ──
            $reader->setReadDataOnly(true);       // skip styles, formatting
            $reader->setReadEmptyCells(false);    // skip blank cells
            if (method_exists($reader, 'setLoadSheetsOnly')) {
                $reader->setLoadSheetsOnly([0]);  // only load first sheet
            }
            $spreadsheet = $reader->load($file['tmp_name']);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $spreadsheet = $reader->load($file['tmp_name']);
        }
    }

    $worksheet = $spreadsheet->getActiveSheet();
    $rows      = $worksheet->toArray();

    if (empty($rows)) {
        header('Location: index.php?page=admin_import&error=' . urlencode('File is empty.'));
        exit;
    }

    // ── Detect format from header row ────────────────────────
    $firstRow  = array_map('trim', array_map('strval', $rows[0]));
    $firstVal  = strtolower($firstRow[0] ?? '');
    $hasHeader = in_array($firstVal, ['word', 'english', 'telugu', 'hindi']);

    // Map column names from header if present
    $colMap = [];
    if ($hasHeader) {
        foreach ($firstRow as $i => $col) {
            $colMap[strtolower($col)] = $i;
        }
        array_shift($rows); // remove header row
    }

    // ── Prepare statements ───────────────────────────────────
    $check_dup = $db->prepare("
        SELECT id FROM dictionary_entries
        WHERE dictionary_id = ? AND word = ?
    ");

    $stmt = $db->prepare("
        INSERT INTO dictionary_entries
            (dictionary_id, word, telugu, hindi, transliteration, part_of_speech, example_source, example_target)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $import_count  = 0;
    $skipped_count = 0;

    foreach ($rows as $row) {

        // ── Map columns based on format detected ─────────────
        if ($hasHeader && !empty($colMap)) {
            // Export format — use named column positions
            $english         = trim((string)($row[$colMap['word']            ?? 0] ?? ''));
            $telugu          = trim((string)($row[$colMap['telugu']          ?? 1] ?? ''));
            $hindi           = trim((string)($row[$colMap['hindi']           ?? 2] ?? ''));
            $transliteration = trim((string)($row[$colMap['transliteration'] ?? 3] ?? ''));
            $part_of_speech  = trim((string)($row[$colMap['part_of_speech']  ?? 4] ?? ''));
            $example_source  = trim((string)($row[$colMap['example_source']  ?? 5] ?? ''));
            $example_target  = trim((string)($row[$colMap['example_target']  ?? 6] ?? ''));
        } else {
            // Teacher format — positional columns
            $english         = trim((string)($row[0] ?? ''));
            $telugu          = trim((string)($row[1] ?? ''));
            $hindi           = trim((string)($row[2] ?? ''));
            $transliteration = '';
            $part_of_speech  = '';
            $example_source  = '';
            $example_target  = '';
        }

        // Skip empty or purely numeric rows
        if ($english === '') continue;
        if ($telugu === '' && $hindi === '') continue;
        if (is_numeric($english)) continue;

        // Duplicate check
        $check_dup->bind_param("is", $dictionary_id, $english);
        $check_dup->execute();
        $check_dup->store_result();

        if ($check_dup->num_rows > 0) {
            $skipped_count++;
            continue;
        }

        $stmt->bind_param(
            "isssssss",
            $dictionary_id,
            $english,
            $telugu,
            $hindi,
            $transliteration,
            $part_of_speech,
            $example_source,
            $example_target
        );
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