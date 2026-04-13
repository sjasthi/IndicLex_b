<?php
/** @var mysqli $db */
if (!isset($db)) {
    require_once __DIR__ . '/../includes/db.php';
}

$import_dict_options = [];
$res = $db->query('SELECT id, name FROM dictionaries ORDER BY name ASC');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $import_dict_options[] = $row;
    }
}

$success = isset($_GET['success']) ? (int) $_GET['success'] : null;
$skipped = isset($_GET['skipped']) ? (int) $_GET['skipped'] : null;
$error   = isset($_GET['error']) ? (string) $_GET['error'] : '';
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">Data Import / Export</h2>
            <p class="text-muted">Bulk upload new dictionary entries or export existing ones.</p>
        </div>
    </div>

    <?php if ($success !== null): ?>
      <div class="alert alert-success">Imported <strong><?php echo $success; ?></strong> row(s). Skipped duplicates: <strong><?php echo $skipped ?? 0; ?></strong>.</div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
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
                                <?php foreach ($import_dict_options as $d): ?>
                                  <option value="<?php echo (int) $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (count($import_dict_options) === 0): ?>
                              <div class="form-text text-warning">No dictionaries yet. <a href="index.php?page=admin_dictionaries">Create one</a> first.</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="fileUpload" class="form-label fw-bold">Upload File</label>
                            <input class="form-control" type="file" id="fileUpload" name="import_file" accept=".csv,.json,.xlsx,.xls" required>
                            <div class="form-text">Columns: English, Telugu, Hindi (CSV or Excel).</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" <?php echo count($import_dict_options) === 0 ? 'disabled' : ''; ?>>Run Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
