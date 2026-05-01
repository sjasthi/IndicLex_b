<?php
// ============================================================
// pages/admin_compare.php — Dictionary Comparison
// ============================================================
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// ── Fetch all dictionaries ───────────────────────────────────
$all_dicts = [];
$r = $db->query("SELECT id, name FROM dictionaries ORDER BY name ASC");
if ($r) while ($row = $r->fetch_assoc()) $all_dicts[] = $row;

// ── Get selected dictionaries ────────────────────────────────
$dict_a = isset($_GET['dict_a']) ? intval($_GET['dict_a']) : 0;
$dict_b = isset($_GET['dict_b']) ? intval($_GET['dict_b']) : 0;

$shared  = [];
$only_a  = [];
$only_b  = [];
$total_shared = 0;
$total_only_a = 0;
$total_only_b = 0;
$compared = false;

$cols = $db->query("SHOW COLUMNS FROM dictionary_entries LIKE 'word_norm'");
$compare_uses_word_norm = ($cols && $cols->num_rows > 0);

if ($dict_a > 0 && $dict_b > 0 && $dict_a !== $dict_b) {
    $compared = true;

    // Get name of each dictionary
    $name_a = $name_b = '';
    foreach ($all_dicts as $d) {
        if ($d['id'] == $dict_a) $name_a = $d['name'];
        if ($d['id'] == $dict_b) $name_b = $d['name'];
    }

    if ($compare_uses_word_norm) {
        // ── Shared (indexed join on word_norm + accurate totals) ──
        $stmt = $db->prepare("
            SELECT COUNT(*) AS n
            FROM dictionary_entries a
            INNER JOIN dictionary_entries b
              ON a.word_norm = b.word_norm AND b.dictionary_id = ?
            WHERE a.dictionary_id = ?
        ");
        $stmt->bind_param('ii', $dict_b, $dict_a);
        $stmt->execute();
        $total_shared = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT a.word, a.telugu AS telugu_a, a.hindi AS hindi_a,
                   b.telugu AS telugu_b, b.hindi AS hindi_b
            FROM dictionary_entries a
            INNER JOIN dictionary_entries b
              ON a.word_norm = b.word_norm AND b.dictionary_id = ?
            WHERE a.dictionary_id = ?
            ORDER BY a.word ASC
            LIMIT 200
        ");
        $stmt->bind_param('ii', $dict_b, $dict_a);
        $stmt->execute();
        $shared = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // ── Only in A (LEFT JOIN anti-pattern; avoids correlated NOT IN) ──
        $stmt = $db->prepare("
            SELECT COUNT(*) AS n
            FROM dictionary_entries a
            LEFT JOIN dictionary_entries b
              ON a.word_norm = b.word_norm AND b.dictionary_id = ?
            WHERE a.dictionary_id = ? AND b.id IS NULL
        ");
        $stmt->bind_param('ii', $dict_b, $dict_a);
        $stmt->execute();
        $total_only_a = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT a.word, a.telugu, a.hindi
            FROM dictionary_entries a
            LEFT JOIN dictionary_entries b
              ON a.word_norm = b.word_norm AND b.dictionary_id = ?
            WHERE a.dictionary_id = ? AND b.id IS NULL
            ORDER BY a.word ASC
            LIMIT 200
        ");
        $stmt->bind_param('ii', $dict_b, $dict_a);
        $stmt->execute();
        $only_a = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // ── Only in B ──
        $stmt = $db->prepare("
            SELECT COUNT(*) AS n
            FROM dictionary_entries b
            LEFT JOIN dictionary_entries a
              ON b.word_norm = a.word_norm AND a.dictionary_id = ?
            WHERE b.dictionary_id = ? AND a.id IS NULL
        ");
        $stmt->bind_param('ii', $dict_a, $dict_b);
        $stmt->execute();
        $total_only_b = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT b.word, b.telugu, b.hindi
            FROM dictionary_entries b
            LEFT JOIN dictionary_entries a
              ON b.word_norm = a.word_norm AND a.dictionary_id = ?
            WHERE b.dictionary_id = ? AND a.id IS NULL
            ORDER BY b.word ASC
            LIMIT 200
        ");
        $stmt->bind_param('ii', $dict_a, $dict_b);
        $stmt->execute();
        $only_b = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        // Fallback before migration (`sql/migrations/add_word_norm_and_hmong_dict.sql`): slower LOWER()/NOT IN path
        $stmt = $db->prepare("
            SELECT COUNT(*) AS n FROM dictionary_entries a
            INNER JOIN dictionary_entries b ON LOWER(a.word) = LOWER(b.word)
            WHERE a.dictionary_id = ? AND b.dictionary_id = ?
        ");
        $stmt->bind_param('ii', $dict_a, $dict_b);
        $stmt->execute();
        $total_shared = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT a.word, a.telugu AS telugu_a, a.hindi AS hindi_a,
                   b.telugu AS telugu_b, b.hindi AS hindi_b
            FROM dictionary_entries a
            JOIN dictionary_entries b ON LOWER(a.word) = LOWER(b.word)
            WHERE a.dictionary_id = ? AND b.dictionary_id = ?
            ORDER BY a.word ASC
            LIMIT 200
        ");
        $stmt->bind_param('ii', $dict_a, $dict_b);
        $stmt->execute();
        $shared = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT COUNT(*) AS n
            FROM dictionary_entries a
            WHERE a.dictionary_id = ?
              AND LOWER(a.word) NOT IN (
                  SELECT LOWER(word) FROM dictionary_entries WHERE dictionary_id = ?
              )
        ");
        $stmt->bind_param('ii', $dict_a, $dict_b);
        $stmt->execute();
        $total_only_a = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT a.word, a.telugu, a.hindi
            FROM dictionary_entries a
            WHERE a.dictionary_id = ?
              AND LOWER(a.word) NOT IN (
                  SELECT LOWER(word) FROM dictionary_entries WHERE dictionary_id = ?
              )
            ORDER BY a.word ASC
            LIMIT 200
        ");
        $stmt->bind_param('ii', $dict_a, $dict_b);
        $stmt->execute();
        $only_a = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT COUNT(*) AS n
            FROM dictionary_entries b
            WHERE b.dictionary_id = ?
              AND LOWER(b.word) NOT IN (
                  SELECT LOWER(word) FROM dictionary_entries WHERE dictionary_id = ?
              )
        ");
        $stmt->bind_param('ii', $dict_b, $dict_a);
        $stmt->execute();
        $total_only_b = (int) ($stmt->get_result()->fetch_assoc()['n'] ?? 0);
        $stmt->close();

        $stmt = $db->prepare("
            SELECT b.word, b.telugu, b.hindi
            FROM dictionary_entries b
            WHERE b.dictionary_id = ?
              AND LOWER(b.word) NOT IN (
                  SELECT LOWER(word) FROM dictionary_entries WHERE dictionary_id = ?
              )
            ORDER BY b.word ASC
            LIMIT 200
        ");
        $stmt->bind_param('ii', $dict_b, $dict_a);
        $stmt->execute();
        $only_b = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Dictionary Comparison</h1>
        <p class="admin-subtitle">Select two dictionaries to compare shared entries, unique words, and translation overlaps.</p>
      </div>
      <a href="index.php?page=admin_dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>

    <!-- ── SELECTION FORM ── -->
    <div class="admin-card mb-4">
      <div class="admin-card-body">
        <form method="GET" action="index.php" class="row g-3 align-items-end">
          <input type="hidden" name="page" value="admin_compare">
          <div class="col-md-5">
            <label class="form-label fw-600">Dictionary A</label>
            <select name="dict_a" class="form-select" required>
              <option value="">— Select dictionary —</option>
              <?php foreach ($all_dicts as $d): ?>
                <option value="<?php echo $d['id']; ?>" <?php echo $dict_a == $d['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($d['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-1 text-center pt-4">
            <span style="font-size:1.5rem; color:var(--gold);">⇄</span>
          </div>
          <div class="col-md-5">
            <label class="form-label fw-600">Dictionary B</label>
            <select name="dict_b" class="form-select" required>
              <option value="">— Select dictionary —</option>
              <?php foreach ($all_dicts as $d): ?>
                <option value="<?php echo $d['id']; ?>" <?php echo $dict_b == $d['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($d['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Compare</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($compared): ?>

      <!-- ── SUMMARY PILLS ── -->
      <div class="compare-summary-row">
        <div class="compare-pill compare-pill-shared">
          <div class="compare-pill-num"><?php echo number_format($total_shared); ?></div>
          <div class="compare-pill-label">Shared Entries</div>
        </div>
        <div class="compare-pill compare-pill-a">
          <div class="compare-pill-num"><?php echo number_format($total_only_a); ?></div>
          <div class="compare-pill-label">Only in <?php echo htmlspecialchars($name_a); ?></div>
        </div>
        <div class="compare-pill compare-pill-b">
          <div class="compare-pill-num"><?php echo number_format($total_only_b); ?></div>
          <div class="compare-pill-label">Only in <?php echo htmlspecialchars($name_b); ?></div>
        </div>
      </div>

      <?php if ($total_shared > 200 || $total_only_a > 200 || $total_only_b > 200): ?>
        <p class="text-muted small mb-4">Lists are capped at 200 rows per section; the summary numbers above are full totals.</p>
      <?php endif; ?>

      <!-- ── SHARED ENTRIES ── -->
      <?php if (count($shared) > 0): ?>
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <h5>🔗 Shared Entries — Words in both dictionaries
            <span class="badge bg-secondary ms-2"><?php echo count($shared); ?></span>
          </h5>
        </div>
        <div class="admin-card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Word</th>
                  <th><?php echo htmlspecialchars($name_a); ?> — Gloss 1</th>
                  <th><?php echo htmlspecialchars($name_a); ?> — Gloss 2</th>
                  <th><?php echo htmlspecialchars($name_b); ?> — Gloss 1</th>
                  <th><?php echo htmlspecialchars($name_b); ?> — Gloss 2</th>
                  <th>Match?</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($shared as $row):
                  $telugu_match = strtolower(trim($row['telugu_a'])) === strtolower(trim($row['telugu_b']));
                  $hindi_match  = strtolower(trim($row['hindi_a']))  === strtolower(trim($row['hindi_b']));
                ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['telugu_a'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['hindi_a']  ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['telugu_b'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['hindi_b']  ?: '—'); ?></td>
                    <td>
                      <?php if ($telugu_match && $hindi_match): ?>
                        <span class="badge bg-success">✓ Exact</span>
                      <?php elseif ($telugu_match || $hindi_match): ?>
                        <span class="badge bg-warning text-dark">~ Partial</span>
                      <?php else: ?>
                        <span class="badge bg-danger">✗ Different</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ── UNIQUE TO EACH ── -->
      <div class="row g-4">
        <div class="col-md-6">
          <div class="admin-card h-100">
            <div class="admin-card-header" style="border-left: 4px solid #c9a84c;">
              <h5>📘 Only in <?php echo htmlspecialchars($name_a); ?>
                <span class="badge bg-secondary ms-2"><?php echo count($only_a); ?></span>
              </h5>
            </div>
            <div class="admin-card-body p-0" style="max-height:400px; overflow-y:auto;">
              <?php if (count($only_a) === 0): ?>
                <p class="text-muted p-3 mb-0">No unique entries.</p>
              <?php else: ?>
                <table class="table table-sm table-hover mb-0">
                  <thead><tr><th>Word</th><th>Gloss 1</th><th>Gloss 2</th></tr></thead>
                  <tbody>
                    <?php foreach ($only_a as $row): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['telugu'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['hindi']  ?: '—'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="admin-card h-100">
            <div class="admin-card-header" style="border-left: 4px solid #e05a3a;">
              <h5>📙 Only in <?php echo htmlspecialchars($name_b); ?>
                <span class="badge bg-secondary ms-2"><?php echo count($only_b); ?></span>
              </h5>
            </div>
            <div class="admin-card-body p-0" style="max-height:400px; overflow-y:auto;">
              <?php if (count($only_b) === 0): ?>
                <p class="text-muted p-3 mb-0">No unique entries.</p>
              <?php else: ?>
                <table class="table table-sm table-hover mb-0">
                  <thead><tr><th>Word</th><th>Gloss 1</th><th>Gloss 2</th></tr></thead>
                  <tbody>
                    <?php foreach ($only_b as $row): ?>
                      <tr>
                        <td><strong><?php echo htmlspecialchars($row['word']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['telugu'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['hindi']  ?: '—'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    <?php elseif ($dict_a > 0 && $dict_b > 0 && $dict_a === $dict_b): ?>
      <div class="alert alert-warning">Please select two <strong>different</strong> dictionaries to compare.</div>
    <?php endif; ?>

  </div>
</div>