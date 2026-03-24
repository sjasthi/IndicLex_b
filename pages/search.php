<?php
// ============================================================
// pages/search.php — Search Interface + Results + Pagination
// ============================================================
require_once __DIR__ . '/../includes/db.php';

// ── CONFIG ──────────────────────────────────────────────────
define('RESULTS_PER_PAGE', 10);

// ── GET PARAMETERS ──────────────────────────────────────────
$query       = trim($_GET['q']           ?? '');
$mode        = $_GET['mode']             ?? 'substring';
$dict_id     = $_GET['dictionary_id']   ?? 'all';
$page        = max(1, intval($_GET['p'] ?? 1));
$offset      = ($page - 1) * RESULTS_PER_PAGE;

// Validate mode
$valid_modes = ['exact', 'prefix', 'suffix', 'substring'];
if (!in_array($mode, $valid_modes)) $mode = 'substring';

// ── FETCH ALL DICTIONARIES for dropdown ─────────────────────
$dictionaries = [];
$dict_result  = $db->query("SELECT id, name FROM dictionaries ORDER BY name ASC");
if ($dict_result) {
    while ($row = $dict_result->fetch_assoc()) {
        $dictionaries[] = $row;
    }
}

// ── SEARCH LOGIC ────────────────────────────────────────────
$results    = [];
$total      = 0;
$searched   = false;
$error      = '';

if ($query !== '') {
    $searched = true;

    // Build LIKE pattern based on mode
    $pattern = match($mode) {
        'exact'     => $query,
        'prefix'    => $query . '%',
        'suffix'    => '%' . $query,
        'substring' => '%' . $query . '%',
        default     => '%' . $query . '%',
    };

    // ── Build WHERE clause ──
    // Search across English word, Telugu and Hindi translations
    if ($dict_id === 'all' || $dict_id === '') {
        $where      = "(de.word LIKE ? OR de.telugu LIKE ? OR de.hindi LIKE ? OR de.transliteration LIKE ?)";
        $count_sql  = "SELECT COUNT(*) AS total
                       FROM dictionary_entries de
                       WHERE {$where}";
        $result_sql = "SELECT de.*, d.name AS dictionary_name
                       FROM dictionary_entries de
                       JOIN dictionaries d ON de.dictionary_id = d.id
                       WHERE {$where}
                       ORDER BY de.word ASC
                       LIMIT ? OFFSET ?";

        $count_stmt = $db->prepare($count_sql);
        $count_stmt->bind_param('ssss', $pattern, $pattern, $pattern, $pattern);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];
        $count_stmt->close();

        $limit = RESULTS_PER_PAGE;
        $stmt  = $db->prepare($result_sql);
        $stmt->bind_param('ssssii', $pattern, $pattern, $pattern, $pattern, $limit, $offset);

    } else {
        $dict_id_int = intval($dict_id);
        $where       = "(de.word LIKE ? OR de.telugu LIKE ? OR de.hindi LIKE ? OR de.transliteration LIKE ?) AND de.dictionary_id = ?";
        $count_sql   = "SELECT COUNT(*) AS total
                        FROM dictionary_entries de
                        WHERE {$where}";
        $result_sql  = "SELECT de.*, d.name AS dictionary_name
                        FROM dictionary_entries de
                        JOIN dictionaries d ON de.dictionary_id = d.id
                        WHERE {$where}
                        ORDER BY de.word ASC
                        LIMIT ? OFFSET ?";

        $count_stmt = $db->prepare($count_sql);
        $count_stmt->bind_param('ssssi', $pattern, $pattern, $pattern, $pattern, $dict_id_int);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];
        $count_stmt->close();

        $limit = RESULTS_PER_PAGE;
        $stmt  = $db->prepare($result_sql);
        $stmt->bind_param('ssssiii', $pattern, $pattern, $pattern, $pattern, $dict_id_int, $limit, $offset);
    }

    $stmt->execute();
    $result  = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();

    $total_pages = ceil($total / RESULTS_PER_PAGE);
}

// ── Helper: highlight matched term in result ─────────────────
function highlight($text, $query) {
    if ($query === '') return htmlspecialchars($text);
    $escaped = preg_quote($query, '/');
    $safe    = htmlspecialchars($text);
    $safeQ   = htmlspecialchars($query);
    return preg_replace('/(' . $escaped . ')/iu', '<mark>$1</mark>', $safe);
}

// ── Build pagination URL helper ──────────────────────────────
function page_url($p, $q, $mode, $dict_id) {
    return 'index.php?page=search&q=' . urlencode($q)
         . '&mode=' . urlencode($mode)
         . '&dictionary_id=' . urlencode($dict_id)
         . '&p=' . $p;
}
?>

<section class="search-page-section">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <div class="section-label">Discover</div>
      <h1 class="page-title">Search Dictionary</h1>
      <p class="page-subtitle">Search English words with Telugu and Hindi translations across your dictionaries.</p>
    </div>

    <!-- ── SEARCH FORM ── -->
    <div class="search-form-card">
      <form method="GET" action="index.php">
        <input type="hidden" name="page" value="search">

        <div class="row g-3 align-items-end">

          <!-- Query input -->
          <div class="col-12 col-md-5">
            <label class="search-form-label">Search Word or Translation</label>
            <div class="search-wrap" style="max-width:100%;">
              <input
                type="text"
                name="q"
                value="<?php echo htmlspecialchars($query); ?>"
                placeholder='e.g. पानी or "water"'
                autocomplete="off"
              >
              <button type="submit">Search →</button>
            </div>
          </div>

          <!-- Dictionary select -->
          <div class="col-12 col-md-3">
            <label class="search-form-label">Dictionary</label>
            <select name="dictionary_id" class="pref-select w-100">
              <option value="all" <?php echo $dict_id === 'all' ? 'selected' : ''; ?>>All Dictionaries</option>
              <?php foreach ($dictionaries as $d): ?>
                <option value="<?php echo $d['id']; ?>" <?php echo $dict_id == $d['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($d['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Search mode -->
          <div class="col-12 col-md-3">
            <label class="search-form-label">Search Mode</label>
            <select name="mode" class="pref-select w-100">
              <option value="substring" <?php echo $mode === 'substring' ? 'selected' : ''; ?>>Substring — contains</option>
              <option value="exact"     <?php echo $mode === 'exact'     ? 'selected' : ''; ?>>Exact — full match</option>
              <option value="prefix"    <?php echo $mode === 'prefix'    ? 'selected' : ''; ?>>Prefix — starts with</option>
              <option value="suffix"    <?php echo $mode === 'suffix'    ? 'selected' : ''; ?>>Suffix — ends with</option>
            </select>
          </div>

          <!-- Search button (visible on mobile) -->
          <div class="col-12 col-md-1 d-md-none">
            <button type="submit" class="btn-primary-custom w-100" style="justify-content:center;">Go</button>
          </div>

        </div>

        <!-- Mode explanation -->
        <div class="mode-hint">
          <?php
          $hints = [
            'substring' => '🔍 <strong>Substring:</strong> finds any entry containing your query anywhere in the word.',
            'exact'     => '🎯 <strong>Exact:</strong> finds only entries that match your query exactly.',
            'prefix'    => '▶️ <strong>Prefix:</strong> finds entries that start with your query.',
            'suffix'    => '◀️ <strong>Suffix:</strong> finds entries that end with your query.',
          ];
          echo $hints[$mode];
          ?>
        </div>
      </form>
    </div>

    <!-- ── RESULTS ── -->
    <?php if ($searched): ?>

      <?php if (count($results) === 0): ?>
        <!-- No results -->
        <div class="no-results">
          <div class="no-results-icon">🔍</div>
          <h3>No results for "<?php echo htmlspecialchars($query); ?>"</h3>
          <p>Try a different search mode or check your spelling.</p>
          <div class="suggestion-tags mt-3">
            <?php foreach (['substring','prefix','suffix','exact'] as $m): ?>
              <?php if ($m !== $mode): ?>
                <a href="<?php echo page_url($page, $query, $m, $dict_id); ?>" class="suggestion-tag">
                  Try <?php echo $m; ?> instead
                </a>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>

      <?php else: ?>

        <!-- Results header -->
        <div class="results-meta">
          <span class="results-count">
            <?php echo number_format($total); ?> result<?php echo $total !== 1 ? 's' : ''; ?>
            for <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
            <span class="results-mode-badge"><?php echo $mode; ?></span>
          </span>
          <span class="results-page-info">
            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
          </span>
        </div>

        <!-- Result cards -->
        <div class="results-list">
          <?php foreach ($results as $entry): ?>
            <div class="result-card">
              <div class="result-card-header">
                <div class="result-left">
                  <span class="result-word">
                    <?php echo highlight($entry['word'], $query); ?>
                  </span>
                  <?php if (!empty($entry['transliteration'])): ?>
                    <span class="result-phonetic">
                      <?php echo highlight($entry['transliteration'], $query); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="result-right">
                  <?php if (!empty($entry['part_of_speech'])): ?>
                    <span class="word-card-pos"><?php echo htmlspecialchars($entry['part_of_speech']); ?></span>
                  <?php endif; ?>
                  <span class="result-dict-badge"><?php echo htmlspecialchars($entry['dictionary_name']); ?></span>
                </div>
              </div>

              <!-- Telugu translation -->
              <?php if (!empty($entry['telugu'])): ?>
                <div class="result-translation-row">
                  <span class="translation-lang-badge telugu-badge">Telugu</span>
                  <span class="result-definition"><?php echo highlight($entry['telugu'], $query); ?></span>
                </div>
              <?php endif; ?>

              <!-- Hindi translation -->
              <?php if (!empty($entry['hindi'])): ?>
                <div class="result-translation-row">
                  <span class="translation-lang-badge hindi-badge">Hindi</span>
                  <span class="result-definition"><?php echo highlight($entry['hindi'], $query); ?></span>
                </div>
              <?php endif; ?>

              <?php if (!empty($entry['example_source'])): ?>
                <div class="result-examples">
                  <p class="result-example">
                    <span class="example-lang">HI</span>
                    <?php echo htmlspecialchars($entry['example_source']); ?>
                  </p>
                  <?php if (!empty($entry['example_target'])): ?>
                    <p class="result-example">
                      <span class="example-lang">EN</span>
                      <?php echo htmlspecialchars($entry['example_target']); ?>
                    </p>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- ── PAGINATION ── -->
        <?php if ($total_pages > 1): ?>
          <div class="pagination-wrap">

            <!-- Previous -->
            <?php if ($page > 1): ?>
              <a href="<?php echo page_url($page - 1, $query, $mode, $dict_id); ?>" class="page-btn">← Prev</a>
            <?php else: ?>
              <span class="page-btn page-btn-disabled">← Prev</span>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            if ($start > 1): ?>
              <a href="<?php echo page_url(1, $query, $mode, $dict_id); ?>" class="page-btn">1</a>
              <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
              <?php if ($i === $page): ?>
                <span class="page-btn page-btn-active"><?php echo $i; ?></span>
              <?php else: ?>
                <a href="<?php echo page_url($i, $query, $mode, $dict_id); ?>" class="page-btn"><?php echo $i; ?></a>
              <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
              <?php if ($end < $total_pages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
              <a href="<?php echo page_url($total_pages, $query, $mode, $dict_id); ?>" class="page-btn"><?php echo $total_pages; ?></a>
            <?php endif; ?>

            <!-- Next -->
            <?php if ($page < $total_pages): ?>
              <a href="<?php echo page_url($page + 1, $query, $mode, $dict_id); ?>" class="page-btn">Next →</a>
            <?php else: ?>
              <span class="page-btn page-btn-disabled">Next →</span>
            <?php endif; ?>

          </div>
        <?php endif; ?>

      <?php endif; ?>

    <?php else: ?>
      <!-- Suggested searches before any query -->
      <div class="suggestions">
        <p class="section-label" style="text-align:center; margin-bottom: 1.5rem;">Try one of these</p>
        <div class="suggestion-tags">
          <?php
          $suggestions = ['నమస్తే', 'నీళ్ళు', 'అన్నం', 'Water', 'Happy', 'Food'];
          foreach ($suggestions as $s):
          ?>
            <a href="index.php?page=search&q=<?php echo urlencode($s); ?>&mode=substring" class="suggestion-tag">
              <?php echo htmlspecialchars($s); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>