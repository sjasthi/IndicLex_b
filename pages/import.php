<?php
// ============================================================
// pages/import.php — English | Telugu | Hindi import
// Teacher's file format: Col A=English, Col B=Telugu, Col C=Hindi
// No header row expected
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';
// Load Composer's autoloader so we can use PhpSpreadsheet
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo '<div class="container mt-5">';
echo '<h2>Import Results</h2>';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['import_file'])) {
    $dict_id = $_POST['dictionary_id'];
    $file = $_FILES['import_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        
        echo "<div class='alert alert-success'>Successfully uploaded: <strong>" . htmlspecialchars($fileName) . "</strong></div>";

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
        try {
            // Let PhpSpreadsheet automatically figure out if it's CSV, XLSX, etc.
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            echo "<h4>Data Preview (First 5 Rows):</h4>";
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead><tr><th>Word</th><th>Translation</th></tr></thead><tbody>";

            // Loop through the rows and show a preview
            $rowCount = 0;
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) continue;

                if ($rowCount < 5) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row[0] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row[1] ?? '') . "</td>";
                    echo "</tr>";
                }
                $rowCount++;
            }
            
            echo "</tbody></table>";
            echo "<p>Total valid rows found: <strong>" . $rowCount . "</strong></p>";
            echo "<div class='alert alert-warning'>Database insertion is currently bypassed until tables are created.</div>";
            echo '<a href="index.php?page=admin_import" class="btn btn-secondary">Go Back</a>';

        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error reading file: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>File upload failed. Error code: " . $file['error'] . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No file uploaded or invalid request.</div>";
}

echo '</div>';
?>