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

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Import Dictionary Data</h5>
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
                            <!-- name="dictionary_file" must match import.php -->
                            <input class="form-control" type="file" id="fileUpload" name="dictionary_file" accept=".csv, .xlsx, .xls" required>
                            <div class="form-text">Accepted formats: CSV, Excel (.xlsx, .xls) — Columns: English | Telugu | Hindi</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Run Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>