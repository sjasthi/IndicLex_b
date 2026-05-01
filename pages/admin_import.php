<?php
// ============================================================
// pages/admin_import.php
// ============================================================
require_once __DIR__ . '/../includes/db.php';

// Fetch dictionaries from DB for dropdown
$dictionaries = [];
$result = $db->query("SELECT id, name FROM dictionaries ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dictionaries[] = $row;
    }
}

// Fetch recent entries to show what's in the database
$selected_dict = isset($_GET['dict_preview']) ? intval($_GET['dict_preview']) : 0;
$preview_rows  = [];
$preview_total = 0;

if ($selected_dict > 0) {
    $r = $db->prepare("SELECT word, telugu, hindi FROM dictionary_entries WHERE dictionary_id = ? ORDER BY id DESC LIMIT 20");
    $r->bind_param('i', $selected_dict);
    $r->execute();
    $preview_rows = $r->get_result()->fetch_all(MYSQLI_ASSOC);
    $r->close();

    $r = $db->prepare("SELECT COUNT(*) AS total FROM dictionary_entries WHERE dictionary_id = ?");
    $r->bind_param('i', $selected_dict);
    $r->execute();
    $preview_total = $r->get_result()->fetch_assoc()['total'];
    $r->close();
}
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">Data Import / Export</h2>
            <p class="text-muted">Bulk upload new dictionary entries or export existing ones.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✓ <strong><?php echo intval($_GET['success']); ?> entries imported successfully.</strong>
        <?php if (!empty($_GET['skipped']) && intval($_GET['skipped']) > 0): ?>
          <?php echo intval($_GET['skipped']); ?> duplicate(s) skipped.
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ⛔ <strong><?php echo htmlspecialchars($_GET['error']); ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── IMPORT FORM ── -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📥 Import Dictionary Data</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?page=import" method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label for="dictSelect" class="form-label fw-bold">Target Dictionary</label>
                            <select class="form-select" id="dictSelect" name="dictionary_id" required>
                                <option value="">-- Select a Dictionary --</option>
                                <?php foreach ($dictionaries as $d): ?>
                                  <option value="<?php echo $d['id']; ?>">
                                    <?php echo htmlspecialchars($d['name']); ?>
                                  </option>
                                <?php endforeach; ?>
                                <?php if (empty($dictionaries)): ?>
                                  <option value="1">English–Telugu–Hindi</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fileUpload" class="form-label fw-bold">Upload File</label>
                            <input class="form-control" type="file" id="fileUpload" name="dictionary_file" accept=".csv,.xlsx,.xls" required>
                            <div class="form-text">
                                Accepted: CSV, Excel (.xlsx, .xls)<br>
                                Columns: <strong>word | telugu | hindi</strong> (header row optional)
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Run Import</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── PREVIEW DATABASE ── -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🔎 Preview Dictionary Contents</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="index.php" class="d-flex gap-2 mb-3">
                        <input type="hidden" name="page" value="admin_import">
                        <select name="dict_preview" class="form-select">
                            <option value="">-- Select dictionary to preview --</option>
                            <?php foreach ($dictionaries as $d): ?>
                              <option value="<?php echo $d['id']; ?>" <?php echo $selected_dict == $d['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                              </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-success">View</button>
                    </form>

                    <?php if ($selected_dict > 0): ?>
                        <p class="text-muted mb-2">
                            Showing last 20 of <strong><?php echo number_format($preview_total); ?></strong> entries
                        </p>
                        <?php if ($preview_total === 0): ?>
                            <div class="alert alert-warning mb-0">
                                ⚠️ No entries found in this dictionary. The import may have failed silently.
                                <hr>
                                <strong>Try these fixes:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Export as <strong>CSV</strong> and re-import the CSV file</li>
                                    <li>Make sure Column A = English word</li>
                                    <li>Check the file isn't empty</li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr><th>English</th><th>Telugu</th><th>Hindi</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($preview_rows as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['word']   ?: '—'); ?></td>
                                                <td><?php echo htmlspecialchars($row['telugu'] ?: '—'); ?></td>
                                                <td><?php echo htmlspecialchars($row['hindi']  ?: '—'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>