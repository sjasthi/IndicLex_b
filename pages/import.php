<?php
// ============================================================
// pages/import.php — Handles CSV and Excel import POST
// CSV  → uses PHP built-in fgetcsv (no dependencies)
// XLSX → uses PhpSpreadsheet only if available
// ============================================================

set_time_limit(300);
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['dictionary_file'])) {
    header('Location: index.php?page=admin_import');
    exit;
}

$file          = $_FILES['dictionary_file'];
$dictionary_id = intval($_POST['dictionary_id'] ?? 1);

// ── Upload error check ───────────────────────────────────────
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE  => 'File too large (server limit).',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit).',
        UPLOAD_ERR_PARTIAL   => 'File only partially uploaded.',
        UPLOAD_ERR_NO_FILE   => 'No file was selected.',
    ];
    $msg = $errors[$file['error']] ?? 'Upload error code: ' . $file['error'];
    header('Location: index.php?page=admin_import&error=' . urlencode($msg));
    exit;
}

// ── Check file type ──────────────────────────────────────────
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['csv', 'xlsx', 'xls'])) {
    header('Location: index.php?page=admin_import&error=' . urlencode('Unsupported file type. Please upload a CSV, XLSX or XLS file.'));
    exit;
}

// ── Ensure dictionary exists ─────────────────────────────────
$check = $db->prepare("SELECT id FROM dictionaries WHERE id = ?");
$check->bind_param("i", $dictionary_id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    header('Location: index.php?page=admin_import&error=' . urlencode('Selected dictionary does not exist.'));
    exit;
}
$check->close();

// ── Parse the file into $rows array ─────────────────────────
$rows = [];

if ($ext === 'csv') {

    // ── CSV: use built-in fgetcsv — no dependencies ──────────
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        header('Location: index.php?page=admin_import&error=' . urlencode('Could not open uploaded file.'));
        exit;
    }

    // Strip UTF-8 BOM if present
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        $rows[] = $row;
    }
    fclose($handle);

} else {

    // ── Excel: use PhpSpreadsheet if available ───────────────
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoload)) {
        header('Location: index.php?page=admin_import&error=' . urlencode('Excel import requires Composer dependencies. Please run "composer install" or upload a CSV file instead.'));
        exit;
    }

    require_once $autoload;

    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file['tmp_name']);
        $reader->setReadDataOnly(true);
        if (method_exists($reader, 'setReadEmptyCells')) {
            $reader->setReadEmptyCells(false);
        }
        $spreadsheet = $reader->load($file['tmp_name']);
        $worksheet   = $spreadsheet->getActiveSheet();
        $rows        = $worksheet->toArray(null, true, true, false);
    } catch (\Exception $e) {
        header('Location: index.php?page=admin_import&error=' . urlencode('Could not read Excel file: ' . $e->getMessage() . ' — Try saving as CSV instead.'));
        exit;
    }
}

if (empty($rows)) {
    header('Location: index.php?page=admin_import&error=' . urlencode('File is empty.'));
    exit;
}

// ── Detect header row ────────────────────────────────────────
$firstRow = array_map('trim', array_map('strval', $rows[0]));
$firstVal = strtolower($firstRow[0] ?? '');
$hasHeader = in_array($firstVal, ['word', 'english', 'telugu', 'hindi']);

$colMap = [];
if ($hasHeader) {
    foreach ($firstRow as $i => $col) {
        $colMap[strtolower(trim($col))] = $i;
    }
    array_shift($rows);
}

// ── Prepare DB statements ────────────────────────────────────
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

// ── Process each row ─────────────────────────────────────────
foreach ($rows as $row) {

    if ($hasHeader && !empty($colMap)) {
        $english         = trim((string)($row[$colMap['word']            ?? $colMap['english']       ?? 0] ?? ''));
        $telugu          = trim((string)($row[$colMap['telugu']          ?? 1] ?? ''));
        $hindi           = trim((string)($row[$colMap['hindi']           ?? 2] ?? ''));
        $transliteration = trim((string)($row[$colMap['transliteration'] ?? -1] ?? ''));
        $part_of_speech  = trim((string)($row[$colMap['part_of_speech']  ?? -1] ?? ''));
        $example_source  = trim((string)($row[$colMap['example_source']  ?? -1] ?? ''));
        $example_target  = trim((string)($row[$colMap['example_target']  ?? -1] ?? ''));
    } else {
        $english         = trim((string)($row[0] ?? ''));
        $telugu          = trim((string)($row[1] ?? ''));
        $hindi           = trim((string)($row[2] ?? ''));
        $transliteration = '';
        $part_of_speech  = '';
        $example_source  = '';
        $example_target  = '';
    }

    // Skip blank or invalid rows
    if ($english === '')                 continue;
    if ($telugu === '' && $hindi === '') continue;
    if (is_numeric($english))           continue;

    // Truncate to prevent "data too long" errors
    $english         = mb_substr($english,         0, 490, 'UTF-8');
    $telugu          = mb_substr($telugu,          0, 490, 'UTF-8');
    $hindi           = mb_substr($hindi,           0, 490, 'UTF-8');
    $transliteration = mb_substr($transliteration, 0, 490, 'UTF-8');
    $part_of_speech  = mb_substr($part_of_speech,  0, 490, 'UTF-8');

    // Skip duplicates
    $check_dup->bind_param("is", $dictionary_id, $english);
    $check_dup->execute();
    $check_dup->store_result();
    if ($check_dup->num_rows > 0) {
        $skipped_count++;
        continue;
    }

    // Insert
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

header('Location: index.php?page=admin_import&success=' . $import_count . '&skipped=' . $skipped_count);
exit;
?>