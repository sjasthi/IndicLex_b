<?php
// ============================================================
// pages/admin_reports.php — Reports & Visualizations
// ============================================================
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// ── Total words ───────────────────────────────────────────────
$total_words = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries");
if ($r) $total_words = (int)$r->fetch_assoc()['total'];

$total_dicts = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM dictionaries");
if ($r) $total_dicts = (int)$r->fetch_assoc()['total'];

$total_users = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM users");
if ($r) $total_users = (int)$r->fetch_assoc()['total'];

// ── Words per dictionary ──────────────────────────────────────
$dict_labels = [];
$dict_counts = [];
$dict_colors = ['#c9a84c','#e05a3a','#1a1a2e','#2ecc71','#3498db','#9b59b6','#e67e22','#1abc9c'];

$r = $db->query("
    SELECT d.name, COUNT(de.id) AS cnt
    FROM dictionaries d
    LEFT JOIN dictionary_entries de ON de.dictionary_id = d.id
    GROUP BY d.id, d.name
    ORDER BY cnt DESC
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $dict_labels[] = $row['name'];
        $dict_counts[] = (int)$row['cnt'];
    }
}

// ── Language coverage ────────────────────────────────────────
$with_telugu = 0;
$r = $db->query("SELECT COUNT(*) AS c FROM dictionary_entries WHERE telugu != '' AND telugu IS NOT NULL");
if ($r) $with_telugu = (int)$r->fetch_assoc()['c'];

$with_hindi = 0;
$r = $db->query("SELECT COUNT(*) AS c FROM dictionary_entries WHERE hindi != '' AND hindi IS NOT NULL");
if ($r) $with_hindi = (int)$r->fetch_assoc()['c'];

$with_both = 0;
$r = $db->query("SELECT COUNT(*) AS c FROM dictionary_entries WHERE telugu != '' AND telugu IS NOT NULL AND hindi != '' AND hindi IS NOT NULL");
if ($r) $with_both = (int)$r->fetch_assoc()['c'];

$english_only = $total_words - $with_telugu - $with_hindi + $with_both;

// ── Part of speech breakdown ──────────────────────────────────
$pos_labels = [];
$pos_counts = [];
$r = $db->query("
    SELECT COALESCE(NULLIF(TRIM(part_of_speech),''), 'unspecified') AS pos,
           COUNT(*) AS cnt
    FROM dictionary_entries
    GROUP BY pos
    ORDER BY cnt DESC
    LIMIT 8
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $pos_labels[] = $row['pos'];
        $pos_counts[] = (int)$row['cnt'];
    }
}

// ── Entries added over time (by month) ────────────────────────
$timeline_labels = [];
$timeline_counts = [];
$r = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS cnt
    FROM dictionary_entries
    GROUP BY month
    ORDER BY month ASC
    LIMIT 12
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $timeline_labels[] = $row['month'];
        $timeline_counts[] = (int)$row['cnt'];
    }
}

// ── JSON encode for JS ────────────────────────────────────────
$json_dict_labels    = json_encode($dict_labels);
$json_dict_counts    = json_encode($dict_counts);
$json_pos_labels     = json_encode($pos_labels);
$json_pos_counts     = json_encode($pos_counts);
$json_timeline_labels = json_encode($timeline_labels);
$json_timeline_counts = json_encode($timeline_counts);
$json_lang_counts    = json_encode([$with_telugu, $with_hindi, $with_both, max(0, $english_only)]);
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Reports & Statistics</h1>
        <p class="admin-subtitle">Visual breakdown of dictionary data, language coverage, and growth over time.</p>
      </div>
      <a href="index.php?page=admin_dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>

    <!-- ── SUMMARY STATS ── -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="admin-stat-card">
          <div class="admin-stat-icon">📚</div>
          <div class="admin-stat-num"><?php echo number_format($total_words); ?></div>
          <div class="admin-stat-label">Total Words</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="admin-stat-card">
          <div class="admin-stat-icon">📖</div>
          <div class="admin-stat-num"><?php echo number_format($total_dicts); ?></div>
          <div class="admin-stat-label">Dictionaries</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="admin-stat-card">
          <div class="admin-stat-icon">🇮🇳</div>
          <div class="admin-stat-num"><?php echo number_format($with_telugu); ?></div>
          <div class="admin-stat-label">Telugu Entries</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="admin-stat-card">
          <div class="admin-stat-icon">👥</div>
          <div class="admin-stat-num"><?php echo number_format($total_users); ?></div>
          <div class="admin-stat-label">Users</div>
        </div>
      </div>
    </div>

    <!-- ── ROW 1: Bar + Pie ── -->
    <div class="row g-4 mb-4">

      <!-- Bar chart: words per dictionary -->
      <div class="col-md-7">
        <div class="admin-card">
          <div class="admin-card-header"><h5>📊 Words per Dictionary</h5></div>
          <div class="admin-card-body">
            <canvas id="chartDicts" height="200"></canvas>
          </div>
        </div>
      </div>

      <!-- Pie chart: language coverage -->
      <div class="col-md-5">
        <div class="admin-card">
          <div class="admin-card-header"><h5>🌐 Language Coverage</h5></div>
          <div class="admin-card-body">
            <canvas id="chartLangs" height="220"></canvas>
            <div class="report-legend mt-3">
              <div class="report-legend-item"><span style="background:#c9a84c"></span>Has Telugu (<?php echo number_format($with_telugu); ?>)</div>
              <div class="report-legend-item"><span style="background:#e05a3a"></span>Has Hindi (<?php echo number_format($with_hindi); ?>)</div>
              <div class="report-legend-item"><span style="background:#2ecc71"></span>Has Both (<?php echo number_format($with_both); ?>)</div>
              <div class="report-legend-item"><span style="background:#aaaaaa"></span>English Only (<?php echo number_format(max(0,$english_only)); ?>)</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── ROW 2: Timeline + POS ── -->
    <div class="row g-4 mb-4">

      <!-- Line chart: entries over time -->
      <div class="col-md-8">
        <div class="admin-card">
          <div class="admin-card-header"><h5>📈 Entries Added Over Time</h5></div>
          <div class="admin-card-body">
            <?php if (count($timeline_labels) === 0): ?>
              <p class="text-muted">No timeline data available — entries may all share the same timestamp.</p>
            <?php else: ?>
              <canvas id="chartTimeline" height="180"></canvas>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Doughnut: part of speech -->
      <div class="col-md-4">
        <div class="admin-card">
          <div class="admin-card-header"><h5>🔤 Part of Speech</h5></div>
          <div class="admin-card-body">
            <?php if (count($pos_labels) === 0): ?>
              <p class="text-muted">No part-of-speech data available.</p>
            <?php else: ?>
              <canvas id="chartPos" height="260"></canvas>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ── COVERAGE TABLE ── -->
    <div class="admin-card">
      <div class="admin-card-header"><h5>📋 Translation Coverage by Dictionary</h5></div>
      <div class="admin-card-body">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Dictionary</th>
              <th>Total Words</th>
              <th>With Telugu</th>
              <th>Telugu %</th>
              <th>With Hindi</th>
              <th>Hindi %</th>
              <th>Complete</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $r = $db->query("
                SELECT d.name,
                       COUNT(de.id) AS total,
                       SUM(CASE WHEN de.telugu != '' AND de.telugu IS NOT NULL THEN 1 ELSE 0 END) AS has_telugu,
                       SUM(CASE WHEN de.hindi  != '' AND de.hindi  IS NOT NULL THEN 1 ELSE 0 END) AS has_hindi,
                       SUM(CASE WHEN de.telugu != '' AND de.telugu IS NOT NULL
                                 AND de.hindi  != '' AND de.hindi  IS NOT NULL THEN 1 ELSE 0 END) AS has_both
                FROM dictionaries d
                LEFT JOIN dictionary_entries de ON de.dictionary_id = d.id
                GROUP BY d.id, d.name
                ORDER BY total DESC
            ");
            if ($r): while ($row = $r->fetch_assoc()):
                $total   = (int)$row['total'];
                $t_pct   = $total > 0 ? round(($row['has_telugu'] / $total) * 100) : 0;
                $h_pct   = $total > 0 ? round(($row['has_hindi']  / $total) * 100) : 0;
                $b_pct   = $total > 0 ? round(($row['has_both']   / $total) * 100) : 0;
            ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                <td><?php echo number_format($total); ?></td>
                <td><?php echo number_format($row['has_telugu']); ?></td>
                <td>
                  <div class="admin-progress" style="height:6px;">
                    <div class="admin-progress-bar" style="width:<?php echo $t_pct; ?>%; background:#c9a84c;"></div>
                  </div>
                  <small><?php echo $t_pct; ?>%</small>
                </td>
                <td><?php echo number_format($row['has_hindi']); ?></td>
                <td>
                  <div class="admin-progress" style="height:6px;">
                    <div class="admin-progress-bar" style="width:<?php echo $h_pct; ?>%; background:#e05a3a;"></div>
                  </div>
                  <small><?php echo $h_pct; ?>%</small>
                </td>
                <td>
                  <span class="badge bg-<?php echo $b_pct >= 80 ? 'success' : ($b_pct >= 50 ? 'warning text-dark' : 'danger'); ?>">
                    <?php echo $b_pct; ?>%
                  </span>
                </td>
              </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
const gold    = '#c9a84c';
const accent  = '#e05a3a';
const navy    = '#1a1a2e';
const green   = '#2ecc71';
const muted   = '#aaaaaa';

const defaultFont = { family: 'DM Sans, sans-serif', size: 12 };
Chart.defaults.font = defaultFont;
Chart.defaults.color = '#6b7280';

// ── Bar: words per dictionary ─────────────────────────────────
new Chart(document.getElementById('chartDicts'), {
    type: 'bar',
    data: {
        labels:   <?php echo $json_dict_labels; ?>,
        datasets: [{
            label:           'Words',
            data:            <?php echo $json_dict_counts; ?>,
            backgroundColor: <?php echo json_encode(array_slice($dict_colors, 0, count($dict_labels))); ?>,
            borderRadius:    6,
            borderSkipped:   false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});

// ── Pie: language coverage ────────────────────────────────────
new Chart(document.getElementById('chartLangs'), {
    type: 'pie',
    data: {
        labels:   ['Has Telugu', 'Has Hindi', 'Has Both', 'English Only'],
        datasets: [{
            data:            <?php echo $json_lang_counts; ?>,
            backgroundColor: [gold, accent, green, muted],
            borderWidth:     2,
            borderColor:     '#fff',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString()}`
                }
            }
        }
    }
});

// ── Line: entries over time ───────────────────────────────────
<?php if (count($timeline_labels) > 0): ?>
new Chart(document.getElementById('chartTimeline'), {
    type: 'line',
    data: {
        labels:   <?php echo $json_timeline_labels; ?>,
        datasets: [{
            label:           'Entries Added',
            data:            <?php echo $json_timeline_counts; ?>,
            borderColor:     gold,
            backgroundColor: 'rgba(201,168,76,0.1)',
            borderWidth:     2,
            pointBackgroundColor: gold,
            pointRadius:     4,
            tension:         0.3,
            fill:            true,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});
<?php endif; ?>

// ── Doughnut: part of speech ──────────────────────────────────
<?php if (count($pos_labels) > 0): ?>
new Chart(document.getElementById('chartPos'), {
    type: 'doughnut',
    data: {
        labels:   <?php echo $json_pos_labels; ?>,
        datasets: [{
            data:            <?php echo $json_pos_counts; ?>,
            backgroundColor: [gold, accent, navy, green, '#3498db', '#9b59b6', '#e67e22', '#1abc9c'],
            borderWidth:     2,
            borderColor:     '#fff',
        }]
    },
    options: {
        responsive: true,
        cutout: '55%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 10, padding: 8, font: { size: 11 } }
            }
        }
    }
});
<?php endif; ?>
</script>