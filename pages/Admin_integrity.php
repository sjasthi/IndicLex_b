<?php
// ============================================================
// pages/admin_integrity.php — Data Integrity & Validation
// ============================================================
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// ── Fetch all dictionaries ───────────────────────────────────
$all_dicts = [];
$r = $db->query("SELECT id, name FROM dictionaries ORDER BY name ASC");
if ($r) while ($row = $r->fetch_assoc()) $all_dicts[] = $row;

$selected_dict = isset($_GET['dict_id']) ? intval($_GET['dict_id']) : 0;
$ran = false;

$duplicates      = [];
$missing_telugu  = [];
$missing_hindi   = [];
$missing_both    = [];
$missing_in_others = [];
$dict_name       = '';

if ($selected_dict > 0) {
    $ran = true;

    foreach ($all_dicts as $d) {
        if ($d['id'] == $selected_dict) $dict_name = $d['name'];
    }

    // ── 1. Duplicate detection (same word appears more than once) ──
    $stmt = $db->prepare("
        SELECT word, COUNT(*) AS count
        FROM dictionary_entries
        WHERE dictionary_id = ?
        GROUP BY LOWER(word)
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    $stmt->bind_param('i', $selected_dict);
    $stmt->execute();
    $duplicates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ── 2. Missing Telugu translation ──────────────────────────
    $stmt = $db->prepare("
        SELECT word, hindi
        FROM dictionary_entries
        WHERE dictionary_id = ?
          AND (telugu = '' OR telugu IS NULL)
          AND (hindi != '' AND hindi IS NOT NULL)
        ORDER BY word ASC
        LIMIT 100
    ");
    $stmt->bind_param('i', $selected_dict);
    $stmt->execute();
    $missing_telugu = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ── 3. Missing Hindi translation ───────────────────────────
    $stmt = $db->prepare("
        SELECT word, telugu
        FROM dictionary_entries
        WHERE dictionary_id = ?
          AND (hindi = '' OR hindi IS NULL)
          AND (telugu != '' AND telugu IS NOT NULL)
        ORDER BY word ASC
        LIMIT 100
    ");
    $stmt->bind_param('i', $selected_dict);
    $stmt->execute();
    $missing_hindi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ── 4. Missing BOTH translations ───────────────────────────
    $stmt = $db->prepare("
        SELECT word
        FROM dictionary_entries
        WHERE dictionary_id = ?
          AND (telugu = '' OR telugu IS NULL)
          AND (hindi  = '' OR hindi  IS NULL)
        ORDER BY word ASC
        LIMIT 100
    ");
    $stmt->bind_param('i', $selected_dict);
    $stmt->execute();
    $missing_both = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ── 5. Words in this dict missing from other dictionaries ──
    $other_ids = array_filter(array_column($all_dicts, 'id'), fn($id) => $id != $selected_dict);

    if (!empty($other_ids)) {
        $placeholders = implode(',', array_fill(0, count($other_ids), '?'));
        $types = str_repeat('i', count($other_ids));

        $sql = "
            SELECT a.word
            FROM dictionary_entries a
            WHERE a.dictionary_id = ?
              AND LOWER(a.word) NOT IN (
                  SELECT LOWER(b.word)
                  FROM dictionary_entries b
                  WHERE b.dictionary_id IN ({$placeholders})
              )
            ORDER BY a.word ASC
            LIMIT 100
        ";

        $stmt = $db->prepare($sql);
        $params = array_merge([$selected_dict], $other_ids);
        $bind_types = 'i' . $types;
        $stmt->bind_param($bind_types, ...$params);
        $stmt->execute();
        $missing_in_others = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// ── Score helper ──────────────────────────────────────────────
function integrity_score($dups, $miss_t, $miss_h, $miss_b) {
    $issues = count($dups) + count($miss_b) + (count($miss_t) * 0.5) + (count($miss_h) * 0.5);
    if ($issues === 0)  return ['score' => 100, 'label' => 'Excellent', 'color' => 'success'];
    if ($issues <= 5)   return ['score' => 90,  'label' => 'Good',      'color' => 'success'];
    if ($issues <= 20)  return ['score' => 70,  'label' => 'Fair',      'color' => 'warning'];
    if ($issues <= 50)  return ['score' => 50,  'label' => 'Poor',      'color' => 'danger'];
    return               ['score' => 30,  'label' => 'Critical',  'color' => 'danger'];
}
?>

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Data Integrity & Validation</h1>
        <p class="admin-subtitle">Run duplicate detection and missing-entry analysis on any dictionary.</p>
      </div>
      <a href="index.php?page=admin_dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>

    <!-- ── DICTIONARY SELECT ── -->
    <div class="admin-card mb-4">
      <div class="admin-card-body">
        <form method="GET" action="index.php" class="row g-3 align-items-end">
          <input type="hidden" name="page" value="admin_integrity">
          <div class="col-md-6">
            <label class="form-label fw-600">Select Dictionary to Analyse</label>
            <select name="dict_id" class="form-select" required>
              <option value="">— Select a dictionary —</option>
              <?php foreach ($all_dicts as $d): ?>
                <option value="<?php echo $d['id']; ?>" <?php echo $selected_dict == $d['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($d['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Run Analysis</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($ran): ?>

      <!-- ── INTEGRITY SCORE ── -->
      <?php $score = integrity_score($duplicates, $missing_telugu, $missing_hindi, $missing_both); ?>
      <div class="integrity-score-card mb-4">
        <div class="integrity-score-left">
          <div class="integrity-score-num text-<?php echo $score['color']; ?>">
            <?php echo $score['score']; ?>
          </div>
          <div class="integrity-score-label">/100 Integrity Score</div>
        </div>
        <div class="integrity-score-right">
          <h4><?php echo htmlspecialchars($dict_name); ?></h4>
          <span class="badge bg-<?php echo $score['color']; ?> fs-6"><?php echo $score['label']; ?></span>
          <p class="text-muted mt-2 mb-0">
            <?php echo count($duplicates); ?> duplicates &nbsp;·&nbsp;
            <?php echo count($missing_both); ?> missing all translations &nbsp;·&nbsp;
            <?php echo count($missing_telugu); ?> missing Telugu &nbsp;·&nbsp;
            <?php echo count($missing_hindi); ?> missing Hindi
          </p>
        </div>
      </div>

      <div class="row g-4">

        <!-- Duplicates -->
        <div class="col-md-6">
          <div class="admin-card h-100">
            <div class="admin-card-header" style="border-left:4px solid #dc3545;">
              <h5>🔁 Duplicate Words
                <span class="badge bg-danger ms-2"><?php echo count($duplicates); ?></span>
              </h5>
            </div>
            <div class="admin-card-body p-0" style="max-height:300px;overflow-y:auto;">
              <?php if (count($duplicates) === 0): ?>
                <p class="text-success p-3 mb-0">✓ No duplicates found.</p>
              <?php else: ?>
                <table class="table table-sm table-hover mb-0">
                  <thead><tr><th>Word</th><th>Count</th></tr></thead>
                  <tbody>
                    <?php foreach ($duplicates as $row): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td>
                        <td><span class="badge bg-danger"><?php echo $row['count']; ?>×</span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Missing both -->
        <div class="col-md-6">
          <div class="admin-card h-100">
            <div class="admin-card-header" style="border-left:4px solid #dc3545;">
              <h5>⚠️ Missing All Translations
                <span class="badge bg-danger ms-2"><?php echo count($missing_both); ?></span>
              </h5>
            </div>
            <div class="admin-card-body p-0" style="max-height:300px;overflow-y:auto;">
              <?php if (count($missing_both) === 0): ?>
                <p class="text-success p-3 mb-0">✓ All entries have at least one translation.</p>
              <?php else: ?>
                <table class="table table-sm table-hover mb-0">
                  <thead><tr><th>Word</th></tr></thead>
                  <tbody>
                    <?php foreach ($missing_both as $row): ?>
                      <tr><td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td></tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Missing Telugu -->
        <div class="col-md-6">
          <div class="admin-card h-100">
            <div class="admin-card-header" style="border-left:4px solid #ffc107;">
              <h5>🟡 Missing Telugu
                <span class="badge bg-warning text-dark ms-2"><?php echo count($missing_telugu); ?></span>
              </h5>
            </div>
            <div class="admin-card-body p-0" style="max-height:300px;overflow-y:auto;">
              <?php if (count($missing_telugu) === 0): ?>
                <p class="text-success p-3 mb-0">✓ All entries have Telugu translations.</p>
              <?php else: ?>
                <table class="table table-sm table-hover mb-0">
                  <thead><tr><th>Word</th><th>Hindi (has)</th></tr></thead>
                  <tbody>
                    <?php foreach ($missing_telugu as $row): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['hindi'] ?: '—'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Missing Hindi -->
        <div class="col-md-6">
          <div class="admin-card h-100">
            <div class="admin-card-header" style="border-left:4px solid #ffc107;">
              <h5>🟡 Missing Hindi
                <span class="badge bg-warning text-dark ms-2"><?php echo count($missing_hindi); ?></span>
              </h5>
            </div>
            <div class="admin-card-body p-0" style="max-height:300px;overflow-y:auto;">
              <?php if (count($missing_hindi) === 0): ?>
                <p class="text-success p-3 mb-0">✓ All entries have Hindi translations.</p>
              <?php else: ?>
                <table class="table table-sm table-hover mb-0">
                  <thead><tr><th>Word</th><th>Telugu (has)</th></tr></thead>
                  <tbody>
                    <?php foreach ($missing_hindi as $row): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['telugu'] ?: '—'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Missing in other dictionaries -->
        <?php if (!empty($missing_in_others)): ?>
        <div class="col-12">
          <div class="admin-card">
            <div class="admin-card-header" style="border-left:4px solid #0d6efd;">
              <h5>📋 Words in <?php echo htmlspecialchars($dict_name); ?> not found in other dictionaries
                <span class="badge bg-primary ms-2"><?php echo count($missing_in_others); ?></span>
              </h5>
            </div>
            <div class="admin-card-body">
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($missing_in_others as $row): ?>
                  <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['word']); ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
    <?php endif; ?>

  </div>
</div>