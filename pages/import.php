<?php
// ============================================================
// pages/import.php — English | Telugu | Hindi import
// Format: Col A=English, Col B=Telugu, Col C=Hindi (optional header row)
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file'])) {
    $dictionary_id = (int) ($_POST['dictionary_id'] ?? 0);
    $file            = $_FILES['import_file'];

    if ($dictionary_id < 1) {
        header('Location: index.php?page=admin_import&error=' . urlencode('Select a dictionary.'));
        exit;
    }

    $chk = $db->prepare('SELECT id FROM dictionaries WHERE id = ?');
    $chk->bind_param('i', $dictionary_id);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows === 0) {
        $chk->close();
        header('Location: index.php?page=admin_import&error=' . urlencode('Dictionary not found. Create it under Dictionaries first.'));
        exit;
    }
    $chk->close();

    if ($file['error'] !== UPLOAD_ERR_OK) {
        header('Location: index.php?page=admin_import&error=' . urlencode('File upload failed (code ' . (int) $file['error'] . ').'));
        exit;
    }

    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet   = $spreadsheet->getActiveSheet();
        $rows        = $worksheet->toArray();

        if (empty($rows)) {
            header('Location: index.php?page=admin_import&error=' . urlencode('File is empty.'));
            exit;
        }

        $firstRow = array_map('trim', array_map('strval', $rows[0]));
        $firstVal  = strtolower($firstRow[0] ?? '');
        if (in_array($firstVal, ['word', 'english', 'telugu', 'hindi'], true)) {
            array_shift($rows);
        }

        $check_dup = $db->prepare('
            SELECT id FROM dictionary_entries
            WHERE dictionary_id = ? AND word = ?
        ');
        $stmt = $db->prepare('
            INSERT INTO dictionary_entries (dictionary_id, word, telugu, hindi)
            VALUES (?, ?, ?, ?)
        ');

        $import_count  = 0;
        $skipped_count = 0;

        foreach ($rows as $row) {
            $english = trim((string) ($row[0] ?? ''));
            $telugu  = trim((string) ($row[1] ?? ''));
            $hindi   = trim((string) ($row[2] ?? ''));

            if ($english === '' || ($telugu === '' && $hindi === '')) {
                continue;
            }
            if (is_numeric($english)) {
                continue;
            }

            $check_dup->bind_param('is', $dictionary_id, $english);
            $check_dup->execute();
            $check_dup->store_result();

            if ($check_dup->num_rows > 0) {
                $skipped_count++;
                continue;
            }

            $stmt->bind_param('isss', $dictionary_id, $english, $telugu, $hindi);
            $stmt->execute();
            $import_count++;
        }

        $check_dup->close();
        $stmt->close();

        header('Location: index.php?page=admin_import&success=' . $import_count . '&skipped=' . $skipped_count);
        exit;
    } catch (Exception $e) {
        header('Location: index.php?page=admin_import&error=' . urlencode($e->getMessage()));
        exit;
    }
}

header('Location: index.php?page=admin_import&error=' . urlencode('No file uploaded.'));
exit;
