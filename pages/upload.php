<?php
// ============================================================
// upload.php — Bulk Excel Import + CSV/JSON/HTML Export
// Requires: composer require phpoffice/phpspreadsheet
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// ── CONFIG ──────────────────────────────────────────────────
define('MAX_FILE_SIZE',  10 * 1024 * 1024); // 10MB
define('UPLOAD_TMP_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_TYPES',  ['xlsx', 'xls', 'csv']);

// ── STATE ───────────────────────────────────────────────────
$imported   = [];
$errors     = [];
$warnings   = [];
$duplicates = [];
$success    = false;
$action     = $_POST['action'] ?? $_GET['action'] ?? '';

// ── EXPORT HANDLER ──────────────────────────────────────────
if ($action === 'export' && isset($_GET['format'])) {
    handleExport($_GET['format'], $db);
    exit;
}

// ── IMPORT HANDLER ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'import') {
    $result     = handleImport($_FILES['excel_file'] ?? null, $db);
    $imported   = $result['imported'];
    $errors     = $result['errors'];
    $warnings   = $result['warnings'];
    $duplicates = $result['duplicates'];
    $success    = $result['success'];
}

// ============================================================
// IMPORT FUNCTION
// ============================================================
function handleImport($file, $db) {
    $imported = $errors = $warnings = $duplicates = [];
    $success  = false;

    // ── File validation ──
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = uploadErrorMessage($file['error'] ?? -1);
        return compact('imported','errors','warnings','duplicates','success');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_TYPES)) {
        $errors[] = "Invalid file type '.{$ext}'. Accepted: .xlsx, .xls, .csv";
        return compact('imported','errors','warnings','duplicates','success');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        $mb = MAX_FILE_SIZE / 1024 / 1024;
        $errors[] = "File exceeds the {$mb}MB size limit. Split it into smaller batches.";
        return compact('imported','errors','warnings','duplicates','success');
    }

    // ── Move to temp dir ──
    if (!is_dir(UPLOAD_TMP_DIR)) mkdir(UPLOAD_TMP_DIR, 0755, true);
    $tmpPath = UPLOAD_TMP_DIR . uniqid('import_', true) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
        $errors[] = 'Could not save uploaded file. Check server write permissions on /uploads/.';
        return compact('imported','errors','warnings','duplicates','success');
    }

    // ── Parse file with PhpSpreadsheet ──
    try {
        if ($ext === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $reader->setInputEncoding('UTF-8');
            $reader->setDelimiter(',');
            $spreadsheet = $reader->load($tmpPath);
        } else {
            $reader = IOFactory::createReaderForFile($tmpPath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($tmpPath);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);
    } catch (\Exception $e) {
        @unlink($tmpPath);
        $errors[] = 'Could not read file: ' . $e->getMessage();
        return compact('imported','errors','warnings','duplicates','success');
    }

    @unlink($tmpPath);

    if (count($rows) < 2) {
        $errors[] = 'File appears to be empty or only has a header row.';
        return compact('imported','errors','warnings','duplicates','success');
    }

    // ── Map header row ──
    $header = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);

    foreach (['telugu', 'english'] as $col) {
        if (!in_array($col, $header)) {
            $errors[] = "Missing required column: \"{$col}\". Found: " . implode(', ', $header);
            return compact('imported','errors','warnings','duplicates','success');
        }
    }

    $colIndex = array_flip($header);

    // ── Prepare statements ──
    $checkStmt = $db->prepare("
        SELECT id FROM dictionary_entries
        WHERE dictionary_id = 1 AND word = ?
    ");
    $insertStmt = $db->prepare("
        INSERT INTO dictionary_entries (dictionary_id, word, telugu, hindi, transliteration, part_of_speech, example_source, example_target)
        VALUES (1, ?, ?, ?, ?, ?, ?, ?)
    ");

    // ── Process each row ──
    for ($i = 1; $i < count($rows); $i++) {
        $row    = $rows[$i];
        $rowNum = $i + 1;

        $english = trim((string)($row[$colIndex['word']    ?? $colIndex['english'] ?? 0] ?? ''));
        $telugu  = trim((string)($row[$colIndex['telugu']  ?? 1] ?? ''));
        $hindi   = trim((string)($row[$colIndex['hindi']   ?? 2] ?? ''));

        // Skip silently blank rows
        if ($english === '' && $telugu === '') continue;

        if ($english === '') {
            $errors[] = "Row {$rowNum}: missing English word — skipped.";
            continue;
        }

        // Duplicate detection
        $checkStmt->bind_param('s', $english);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $duplicates[] = "Row {$rowNum}: \"{$english}\" already exists — skipped.";
            continue;
        }

        // Optional fields
        $transliteration = trim((string)($row[$colIndex['transliteration'] ?? -1] ?? ''));
        $part_of_speech  = trim((string)($row[$colIndex['part_of_speech']  ?? -1] ?? ''));
        $example_source  = trim((string)($row[$colIndex['example_source']  ?? -1] ?? ''));
        $example_target  = trim((string)($row[$colIndex['example_target']  ?? -1] ?? ''));

        // Warn on unusually long values
        if (mb_strlen($english) > 200) $warnings[] = "Row {$rowNum}: English value is very long.";
        if (mb_strlen($telugu)  > 500) $warnings[] = "Row {$rowNum}: Telugu value is very long.";

        // Insert
        $insertStmt->bind_param('sssssss',
            $english, $telugu, $hindi, $transliteration,
            $part_of_speech, $example_source, $example_target
        );

        if ($insertStmt->execute()) {
            $imported[] = compact(
                'english','telugu','hindi','transliteration',
                'part_of_speech','example_source','example_target'
            );
        } else {
            $errors[] = "Row {$rowNum}: Insert failed — " . $insertStmt->error;
        }
    }

    $checkStmt->close();
    $insertStmt->close();

    $success = count($imported) > 0;
    return compact('imported','errors','warnings','duplicates','success');
}

// ============================================================
// EXPORT FUNCTION
// ============================================================
function handleExport($format, $db) {
    $result = $db->query("
        SELECT de.word, de.telugu, de.hindi,
               de.transliteration, de.part_of_speech,
               de.example_source, de.example_target,
               d.name AS dictionary_name
        FROM dictionary_entries de
        JOIN dictionaries d ON de.dictionary_id = d.id
        ORDER BY de.word ASC
    ");

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Export query failed: ' . $db->error]);
        exit;
    }

    $rows      = $result->fetch_all(MYSQLI_ASSOC);
    $timestamp = date('Y-m-d');

    switch ($format) {

        case 'csv':
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"dictionary_{$timestamp}.csv\"");
            echo "\xEF\xBB\xBF"; // BOM for Excel UTF-8
            $out = fopen('php://output', 'w');
            fputcsv($out, ['word','telugu','hindi','transliteration','part_of_speech','example_source','example_target','dictionary']);
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
            break;

        case 'json':
            header('Content-Type: application/json; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"dictionary_{$timestamp}.json\"");
            echo json_encode([
                'exported' => $timestamp,
                'total'    => count($rows),
                'entries'  => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"dictionary_{$timestamp}.html\"");
            echo buildHtmlExport($rows, $timestamp);
            break;

        default:
            http_response_code(400);
            echo 'Invalid export format.';
    }
}

function buildHtmlExport($rows, $timestamp) {
    $count = count($rows);
    $html  = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>
    <title>DictionaryHub Export — {$timestamp}</title>
    <style>
      body { font-family: Georgia, serif; max-width: 960px; margin: 2rem auto; padding: 0 1rem; color: #1a1a2e; }
      h1 { font-size: 2rem; margin-bottom: 0.25rem; }
      .meta { color: #888; font-size: 0.9rem; margin-bottom: 2rem; }
      table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
      th { text-align: left; padding: 0.6rem 0.8rem; background: #1a1a2e; color: #fff; }
      td { padding: 0.6rem 0.8rem; border-bottom: 1px solid #eee; vertical-align: top; }
      .word { font-size: 1.1rem; font-weight: bold; }
      tr:nth-child(even) td { background: #faf8f4; }
    </style></head><body>
    <h1>📖 DictionaryHub — English–Telugu–Hindi Dictionary</h1>
    <p class='meta'>Exported on {$timestamp} · {$count} entries</p>
    <table><thead><tr>
      <th>English</th><th>Telugu</th><th>Hindi</th>
      <th>Transliteration</th><th>Part of Speech</th>
      <th>Example (Source)</th><th>Example (Target)</th>
    </tr></thead><tbody>";

    foreach ($rows as $r) {
        $html .= "<tr>
            <td class='word'>" . htmlspecialchars($r['word']            ?? '') . "</td>
            <td>"              . htmlspecialchars($r['telugu']          ?? '') . "</td>
            <td>"              . htmlspecialchars($r['hindi']           ?? '') . "</td>
            <td><em>"          . htmlspecialchars($r['transliteration'] ?? '') . "</em></td>
            <td>"              . htmlspecialchars($r['part_of_speech']  ?? '') . "</td>
            <td>"              . htmlspecialchars($r['example_source']  ?? '') . "</td>
            <td>"              . htmlspecialchars($r['example_target']  ?? '') . "</td>
        </tr>";
    }

    $html .= "</tbody></table></body></html>";
    return $html;
}

// ============================================================
// HELPERS
// ============================================================
function uploadErrorMessage($code) {
    return match($code) {
        UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload_max_filesize limit. Ask Bluehost to increase it in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form MAX_FILE_SIZE limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Try again.',
        UPLOAD_ERR_NO_FILE    => 'No file was selected.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server temporary directory is missing. Contact your host.',
        UPLOAD_ERR_CANT_WRITE => 'Server failed to write the file. Check disk space.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
        default               => "Unknown upload error (code {$code}).",
    };
}

function toBytes($val) {
    $val  = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $num  = (int)$val;
    return match($last) {
        'g' => $num * 1024 * 1024 * 1024,
        'm' => $num * 1024 * 1024,
        'k' => $num * 1024,
        default => $num,
    };
}

// ── Word count for display ──
$wordCount = 0;
$countResult = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries");
if ($countResult) {
    $wordCount = $countResult->fetch_assoc()['total'];
}
?>

<section class="upload-section">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <div class="section-label">Import & Export</div>
      <h1 class="page-title">Dictionary Data</h1>
      <p class="page-subtitle">Bulk import Telugu–English words from Excel, or export your dictionary in CSV, JSON, or HTML.</p>
    </div>

    <div class="row g-4 justify-content-center mb-5">
      <div class="col-auto">
        <div class="stat-pill">
          <span class="stat-pill-num"><?php echo number_format($wordCount); ?></span>
          <span class="stat-pill-label">words in database</span>
        </div>
      </div>
    </div>

    <div class="row g-4">

      <!-- ── LEFT: IMPORT ── -->
      <div class="col-lg-7">

        <!-- FORMAT GUIDE -->
        <div class="format-card">
          <div class="format-card-header">
            <span>📋</span>
            <h5>Required File Format</h5>
          </div>
          <p class="format-desc">Upload an <strong>.xlsx</strong>, <strong>.xls</strong>, or <strong>.csv</strong> file. Row 1 must be a header with at least <code>telugu</code> and <code>english</code>:</p>
          <div class="col-table-wrap">
            <table class="col-table">
              <thead><tr><th>Column</th><th>Required</th><th>Example</th></tr></thead>
              <tbody>
                <tr><td><code>telugu</code></td>           <td><span class="badge-required">Required</span></td><td>నమస్తే</td></tr>
                <tr><td><code>english</code></td>          <td><span class="badge-required">Required</span></td><td>Hello / Greetings</td></tr>
                <tr><td><code>transliteration</code></td>  <td><span class="badge-optional">Optional</span></td><td>Namaste</td></tr>
                <tr><td><code>part_of_speech</code></td>   <td><span class="badge-optional">Optional</span></td><td>interjection</td></tr>
                <tr><td><code>example_telugu</code></td>    <td><span class="badge-optional">Optional</span></td><td>నమస్తే, మీరు ఎలా ఉన్నారు?</td></tr>
                <tr><td><code>example_english</code></td>  <td><span class="badge-optional">Optional</span></td><td>Hello, how are you?</td></tr>
              </tbody>
            </table>
          </div>
          <p class="format-example-label">Example rows:</p>
          <pre class="format-pre">telugu,english,transliteration,part_of_speech,example_telugu,example_english
నమస్తే,Hello,Namaste,interjection,నమస్తే, మీరు ఎలా ఉన్నారు?,Hello, how are you?
నీళ్ళు,Water,Neellu,noun,నాకు నీళ్ళు కావాలి.,I need water.</pre>
        </div>

        <!-- UPLOAD FORM -->
        <div class="upload-card">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import">
            <div class="drop-zone" id="dropZone">
              <div class="drop-icon">📂</div>
              <p class="drop-title">Drag & drop your file here</p>
              <p class="drop-sub">.xlsx, .xls, or .csv &nbsp;·&nbsp; max 10MB</p>
              <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv" class="drop-input">
              <div class="drop-filename" id="dropFilename"></div>
            </div>
            <button type="submit" class="btn-primary-custom upload-btn">⬆ Upload & Import</button>
          </form>
        </div>

        <!-- ERRORS -->
        <?php if (!empty($errors)): ?>
          <div class="result-block error-block">
            <div class="result-block-header">⛔ <?php echo count($errors); ?> error<?php echo count($errors) !== 1 ? 's' : ''; ?></div>
            <ul class="result-list">
              <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- WARNINGS -->
        <?php if (!empty($warnings)): ?>
          <div class="result-block warning-block">
            <div class="result-block-header">⚠️ <?php echo count($warnings); ?> warning<?php echo count($warnings) !== 1 ? 's' : ''; ?></div>
            <ul class="result-list">
              <?php foreach ($warnings as $w): ?><li><?php echo htmlspecialchars($w); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- DUPLICATES -->
        <?php if (!empty($duplicates)): ?>
          <div class="result-block duplicate-block">
            <div class="result-block-header">🔁 <?php echo count($duplicates); ?> duplicate<?php echo count($duplicates) !== 1 ? 's' : ''; ?> skipped</div>
            <ul class="result-list">
              <?php foreach ($duplicates as $d): ?><li><?php echo htmlspecialchars($d); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- SUCCESS + PREVIEW TABLE -->
        <?php if ($success): ?>
          <div class="result-block success-block">
            <div class="result-block-header">✓ <?php echo count($imported); ?> word<?php echo count($imported) !== 1 ? 's' : ''; ?> imported successfully</div>
          </div>

          <div class="preview-table-wrap">
            <table class="preview-table">
              <thead>
                <tr>
                  <th>Telugu</th><th>Transliteration</th><th>English</th>
                  <th>Part of Speech</th><th>Example (Telugu)</th><th>Example (English)</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($imported as $entry): ?>
                  <tr>
                    <td><strong class="hindi-word"><?php echo htmlspecialchars($entry['telugu']); ?></strong></td>
                    <td class="phonetic-cell"><?php echo htmlspecialchars($entry['transliteration']) ?: '—'; ?></td>
                    <td><?php echo htmlspecialchars($entry['english']); ?></td>
                    <td><?php echo htmlspecialchars($entry['part_of_speech']) ?: '—'; ?></td>
                    <td class="muted-cell"><?php echo htmlspecialchars($entry['example_telugu']) ?: '—'; ?></td>
                    <td class="muted-cell"><?php echo htmlspecialchars($entry['example_english']) ?: '—'; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>

      <!-- ── RIGHT: EXPORT + SERVER INFO ── -->
      <div class="col-lg-5">

        <!-- EXPORT -->
        <div class="export-card">
          <div class="format-card-header">
            <span>⬇️</span>
            <h5>Export Dictionary</h5>
          </div>
          <p class="format-desc">Download all <?php echo number_format($wordCount); ?> words from your database.</p>
          <div class="export-buttons">
            <a href="index.php?page=upload&action=export&format=csv"  class="export-btn">
              <span class="export-btn-icon">📄</span>
              <div><div class="export-btn-title">Export CSV</div><div class="export-btn-sub">UTF-8, Excel compatible</div></div>
            </a>
            <a href="index.php?page=upload&action=export&format=json" class="export-btn">
              <span class="export-btn-icon">{ }</span>
              <div><div class="export-btn-title">Export JSON</div><div class="export-btn-sub">Pretty-printed, Unicode</div></div>
            </a>
            <a href="index.php?page=upload&action=export&format=html" class="export-btn">
              <span class="export-btn-icon">🌐</span>
              <div><div class="export-btn-title">Export HTML</div><div class="export-btn-sub">Printable table view</div></div>
            </a>
          </div>
        </div>

        <!-- SERVER INFO -->
        <div class="server-card">
          <div class="format-card-header">
            <span>🛠️</span>
            <h5>Server Configuration</h5>
          </div>
          <p class="format-desc">Live PHP settings. If any show ⚠ ask Bluehost to update <code>php.ini</code>.</p>
          <div class="server-rows">
            <?php
            $checks = [
              ['label' => 'upload_max_filesize', 'value' => ini_get('upload_max_filesize'),      'ok' => toBytes(ini_get('upload_max_filesize')) >= 10 * 1024 * 1024],
              ['label' => 'post_max_size',       'value' => ini_get('post_max_size'),            'ok' => toBytes(ini_get('post_max_size'))       >= 10 * 1024 * 1024],
              ['label' => 'max_execution_time',  'value' => ini_get('max_execution_time') . 's', 'ok' => (int)ini_get('max_execution_time')     >= 60],
              ['label' => 'memory_limit',        'value' => ini_get('memory_limit'),             'ok' => toBytes(ini_get('memory_limit'))        >= 128 * 1024 * 1024],
              ['label' => '/uploads/ writable',  'value' => is_writable(UPLOAD_TMP_DIR) ? 'Yes' : 'No', 'ok' => is_writable(UPLOAD_TMP_DIR)],
            ];
            foreach ($checks as $c): ?>
              <div class="server-row">
                <span class="server-key"><?php echo $c['label']; ?></span>
                <span class="server-val"><?php echo $c['value']; ?></span>
                <span class="server-status <?php echo $c['ok'] ? 'status-ok' : 'status-warn'; ?>">
                  <?php echo $c['ok'] ? '✓' : '⚠'; ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="server-hint">Recommended: upload_max_filesize ≥ 10M, memory_limit ≥ 128M, max_execution_time ≥ 60</p>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  const dropZone  = document.getElementById('dropZone');
  const excelFile = document.getElementById('excelFile');
  const filename  = document.getElementById('dropFilename');

  dropZone.addEventListener('click', () => excelFile.click());

  excelFile.addEventListener('change', () => {
    if (excelFile.files.length > 0) {
      filename.textContent = '📄 ' + excelFile.files[0].name;
      dropZone.classList.add('has-file');
    }
  });

  dropZone.addEventListener('dragover',  (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
  dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('drag-over'));
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      const dt = new DataTransfer();
      dt.items.add(files[0]);
      excelFile.files = dt.files;
      filename.textContent = '📄 ' + files[0].name;
      dropZone.classList.add('has-file');
    }
  });
</script>