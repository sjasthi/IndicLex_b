<?php
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