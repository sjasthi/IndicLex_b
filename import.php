<?php
require 'vendor/autoload.php'; 
require 'home.php'; 

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dictionary_file'])) {
    $file = $_FILES['dictionary_file'];
    $dictionary_id = intval($_POST['dictionary_id']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code " . $file['error']);
    }

    try {
        // Ensure the dictionary exists in the DB first
        $check_stmt = $conn->prepare("SELECT id FROM dictionaries WHERE id = ?");
        $check_stmt->bind_param("i", $dictionary_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            $conn->query("INSERT INTO dictionaries (id, name) VALUES ($dictionary_id, 'My First Dictionary')");
        }

        // Parse the Excel/CSV file
        $spreadsheet = IOFactory::load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Remove the header row
        array_shift($rows); 
        
        $stmt = $conn->prepare("INSERT INTO dictionary_entries (dictionary_id, word, translation) VALUES (?, ?, ?)");

        $import_count = 0;
        foreach ($rows as $row) {
            $word = isset($row[0]) ? trim($row[0]) : '';
            $translation = isset($row[1]) ? trim($row[1]) : '';

            if (!empty($word) && !empty($translation)) {
                $stmt->bind_param("iss", $dictionary_id, $word, $translation);
                $stmt->execute();
                $import_count++;
            }
        }

        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h2>Success!</h2>";
        echo "<p>Imported $import_count entries into dictionary ID $dictionary_id.</p>";
        echo "<a href='index.php?page=admin_import'>Go Back</a>";
        echo "</div>";

    } catch (Exception $e) {
        echo "Error processing file: " . $e->getMessage();
    }
}
?>
