<?php
// ============================================================
// pages/admin_dashboard.php
// ============================================================
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

require_once __DIR__ . '/../includes/admin_stats_data.php';

// Per-dictionary word counts
$dict_stats = [];
$r = $db->query("
    SELECT d.id, d.name, d.source_lang, d.target_lang,
           COUNT(de.id) AS word_count
    FROM dictionaries d
    LEFT JOIN dictionary_entries de ON de.dictionary_id = d.id
    GROUP BY d.id, d.name, d.source_lang, d.target_lang
    ORDER BY word_count DESC
");
if ($r) while ($row = $r->fetch_assoc()) $dict_stats[] = $row;

// Last 10 entries only
$recent = [];
$r = $db->query("
    SELECT de.word, de.telugu, de.hindi, d.name AS dictionary_name
    FROM dictionary_entries de
    JOIN dictionaries d ON de.dictionary_id = d.id
    ORDER BY de.id DESC LIMIT 10
");
if ($r) while ($row = $r->fetch_assoc()) $recent[] = $row;
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Admin Dashboard</h1>
        <p class="admin-subtitle">Welcome back, <strong><?php echo htmlspecialchars(current_user()['username']); ?></strong></p>
      </div>
      <div class="admin-header-actions d-flex flex-wrap gap-2">
        <a href="index.php?page=admin_dictionaries" class="btn btn-outline-primary btn-sm">📚 Dictionaries</a>
        <a href="index.php?page=admin_entries" class="btn btn-outline-primary btn-sm">📝 Entries</a>
        <a href="index.php?page=admin_import" class="btn btn-primary btn-sm">📥 Import</a>
        <a href="index.php?page=logout"       class="btn btn-outline-secondary btn-sm">Sign Out</a>
      </div>
    </div>

    <?php require __DIR__ . '/../includes/admin_stats_cards.php'; ?>

    <div class="row g-4 mb-4">

      <!-- Dictionary breakdown -->
      <div class="col-md-7">
        <div class="admin-card">
          <div class="admin-card-header"><h5>📊 Word Count per Dictionary</h5></div>
          <div class="admin-card-body">
            <table class="table table-hover mb-0">
              <thead>
                <tr><th>#</th><th>Dictionary</th><th>Source</th><th>Target</th><th>Words</th><th>Coverage</th></tr>
              </thead>
              <tbody>
                <?php foreach ($dict_stats as $i => $d):
                  $pct = $total_words > 0 ? round(($d['word_count'] / $total_words) * 100) : 0;
                ?>
                  <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($d['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($d['source_lang']); ?></td>
                    <td><?php echo htmlspecialchars($d['target_lang']); ?></td>
                    <td><span class="admin-word-count"><?php echo number_format($d['word_count']); ?></span></td>
                    <td>
                      <div class="admin-progress">
                        <div class="admin-progress-bar" style="width:<?php echo $pct; ?>%"></div>
                      </div>
                      <small><?php echo $pct; ?>%</small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Language + Recent -->
      <div class="col-md-5">
        <div class="admin-card mb-4">
          <div class="admin-card-header"><h5>🌐 Language Breakdown</h5></div>
          <div class="admin-card-body">
            <div class="lang-breakdown">
              <div class="lang-row">
                <span class="lang-name">🇺🇸 English</span>
                <span class="lang-count"><?php echo number_format($total_words); ?> words</span>
              </div>
              <div class="lang-row">
                <span class="lang-name">🇮🇳 Telugu</span>
                <span class="lang-count"><?php echo number_format($with_telugu); ?></span>
                <span class="lang-pct"><?php echo $total_words > 0 ? round(($with_telugu / $total_words) * 100) : 0; ?>%</span>
              </div>
              <div class="lang-row">
                <span class="lang-name">🇮🇳 Hindi</span>
                <span class="lang-count"><?php echo number_format($with_hindi); ?></span>
                <span class="lang-pct"><?php echo $total_words > 0 ? round(($with_hindi / $total_words) * 100) : 0; ?>%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header"><h5>🕐 Recently Added</h5></div>
          <div class="admin-card-body p-0">
            <ul class="admin-recent-list">
              <?php foreach ($recent as $entry): ?>
                <li class="admin-recent-item">
                  <div class="admin-recent-word"><?php echo htmlspecialchars($entry['word']); ?></div>
                  <div class="admin-recent-trans"><?php echo htmlspecialchars($entry['telugu'] ?: '—'); ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- DataTable — server-side, loads only what's visible -->
    <div class="admin-card">
      <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📋 All Dictionary Entries</h5>
        <div class="d-flex gap-2">
          <a href="index.php?page=upload&action=export&format=csv"  class="btn btn-outline-success btn-sm">CSV</a>
          <a href="index.php?page=upload&action=export&format=json" class="btn btn-outline-primary btn-sm">JSON</a>
          <a href="index.php?page=upload&action=export&format=html" class="btn btn-outline-secondary btn-sm">HTML</a>
        </div>
      </div>
      <div class="admin-card-body">
        <table id="entriesTable" class="table table-hover table-sm" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>English</th>
              <th>Telugu</th>
              <th>Hindi</th>
              <th>Part of Speech</th>
              <th>Dictionary</th>
            </tr>
          </thead>
          <!-- tbody is empty — DataTables fetches via Ajax -->
          <tbody></tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/JS/admin_crud.js"></script>
<script>
$(document).ready(function () {
    $('#entriesTable').DataTable({
        // ── Server-side processing ──────────────────────────
        // Only fetches the rows currently visible — much faster
        processing:  true,
        serverSide:  true,
        ajax:        'index.php?page=datatables_ajax',

        columns: [
            { data: 'id',              width: '50px' },
            { data: 'word'             },
            { data: 'telugu'           },
            { data: 'hindi'            },
            { data: 'part_of_speech'   },
            { data: 'dictionary_name'  },
        ],

        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']],

        language: {
            processing:   '<div class="spinner-border spinner-border-sm text-secondary" role="status"></div> Loading...',
            search:        'Filter:',
            lengthMenu:    'Show _MENU_ entries',
            info:          'Showing _START_–_END_ of _TOTAL_ entries',
            infoFiltered:  '(filtered from _MAX_)',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        }
    });

    refreshAdminStats();
});
</script>