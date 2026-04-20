<?php
// ============================================================
// pages/search.php — Search Interface + Results + Pagination
// ============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/preferences_helper.php';
require_once __DIR__ . '/../includes/dictionary_search.php';

// ── Load preferences ─────────────────────────────────────────
$prefs = load_all_preferences($db, null);

// ── CONFIG ──────────────────────────────────────────────────
// Results per page comes from preferences (cookie → DB → default)
$results_per_page = $prefs['results_per_page'];

// ── GET PARAMETERS ──────────────────────────────────────────
$query       = trim($_GET['q']           ?? '');
$mode        = $_GET['mode']             ?? 'substring';
// Use saved default dictionary if none selected in URL
$dict_id     = $_GET['dictionary_id']   ?? $prefs['default_dict'];
$page        = max(1, intval($_GET['p'] ?? 1));
$offset      = ($page - 1) * $results_per_page;

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

    $run = indiclex_dictionary_search($db, $query, $mode, $dict_id, $results_per_page, $offset);
    if (!empty($run['ok'])) {
        $total   = (int) $run['total'];
        $results = $run['rows'];
    } else {
        $error = $run['error'] ?? 'Search failed';
    }

    $total_pages = $results_per_page > 0 ? (int) ceil($total / $results_per_page) : 0;
}

$script_dir      = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$api_search_path = ($script_dir === '/' || $script_dir === '\\') ? '/api/search.php' : rtrim($script_dir, '/') . '/api/search.php';

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
            <div class="search-wrap search-wrap-autocomplete" style="max-width:100%;">
              <input
                type="text"
                name="q"
                id="search-q-input"
                value="<?php echo htmlspecialchars($query); ?>"
                placeholder='e.g. पानी or "water"'
                autocomplete="off"
                data-api-search="<?php echo htmlspecialchars($api_search_path, ENT_QUOTES, 'UTF-8'); ?>"
                aria-autocomplete="list"
                aria-controls="search-autocomplete-list"
                aria-expanded="false"
              >
              <button type="submit">Search →</button>
              <div id="search-autocomplete-list" class="search-autocomplete-dropdown" role="listbox" hidden></div>
            </div>
          </div>

          <!-- Dictionary select -->
          <div class="col-12 col-md-3">
            <label class="search-form-label">Dictionary</label>
            <select name="dictionary_id" id="search-dictionary-select" class="pref-select w-100">
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
            <select name="mode" id="search-mode-select" class="pref-select w-100">
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
          &nbsp;·&nbsp; Showing <strong><?php echo $results_per_page; ?></strong> results per page
          — <a href="index.php?page=preferences" style="color:var(--gold);">change in Preferences</a>
        </div>

        <div class="word-length-match-callout">
          <strong>Word length matching</strong>
          <span class="word-length-match-text">
            Need words of the same length for a Telugu puzzle or crossword?
            Use the same-length word tools at
            <a href="https://telugupuzzles.com/apps.php" target="_blank" rel="noopener noreferrer">Telugu Puzzles</a>
            (external).
          </span>
        </div>
      </form>
    </div>

    <!-- ── RESULTS ── -->
    <?php if ($searched): ?>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger search-error-banner" role="alert">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php elseif (count($results) === 0): ?>
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

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="assets/JS/search-autocomplete.js"></script>