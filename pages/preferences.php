<?php
// ============================================================
// pages/preferences.php
// Handles saving preferences via POST, then displays the panel
// ============================================================
require_once __DIR__ . '/../includes/preferences_helper.php';
require_once __DIR__ . '/../includes/db.php';

$saved   = false;
$message = '';

// ── Handle form POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = ['theme', 'results_per_page', 'default_dict'];
    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            save_preference($key, $_POST[$key], $db, null);
        }
    }
    $saved   = true;
    $message = 'Preferences saved!';
}

// ── Load current preferences (after possible save) ───────────
$prefs = load_all_preferences($db, null);

// ── Fetch dictionaries for dropdown ──────────────────────────
$dictionaries = [];
$result = $db->query("SELECT id, name FROM dictionaries ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dictionaries[] = $row;
    }
}
?>

<section class="preferences-section">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <div class="section-label">Settings</div>
      <h1 class="page-title">Preferences</h1>
      <p class="page-subtitle">Customise your DictionaryHub experience. Settings are saved as cookies and applied on every page load.</p>
    </div>

    <?php if ($saved): ?>
      <div class="alert alert-success text-center mb-4" role="alert">
        ✓ <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <!-- Preference resolution info -->
    <div class="pref-resolution-banner">
      <span class="pref-resolution-icon">🔗</span>
      <div>
        <strong>How preferences are resolved:</strong>
        Cookie value → Database default → System default.
        Your saved preferences are loaded before the page renders so there is no flash of wrong theme or settings.
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-7">

        <form method="POST" action="index.php?page=preferences">

          <!-- ── THEME ── -->
          <div class="pref-card">
            <div class="pref-card-header">
              <span class="pref-icon">🎨</span>
              <div>
                <h5 class="pref-title">Theme</h5>
                <p class="pref-desc">Choose how DictionaryHub looks. Applied server-side before render — no flash.</p>
              </div>
            </div>
            <div class="pref-option">
              <div class="pref-option-label">
                <span>Colour Mode</span>
                <small>Current: <strong><?php echo ucfirst($prefs['theme']); ?></strong>
                  <?php
                    $theme_source = get_cookie_pref('theme') !== null ? 'cookie' : 'system default';
                    echo "<span class='pref-source-badge'>{$theme_source}</span>";
                  ?>
                </small>
              </div>
              <div class="theme-toggle-group">
                <label class="theme-radio-btn <?php echo $prefs['theme'] === 'light' ? 'active' : ''; ?>">
                  <input type="radio" name="theme" value="light" <?php echo $prefs['theme'] === 'light' ? 'checked' : ''; ?>>
                  ☀️ Light
                </label>
                <label class="theme-radio-btn <?php echo $prefs['theme'] === 'dark' ? 'active' : ''; ?>">
                  <input type="radio" name="theme" value="dark" <?php echo $prefs['theme'] === 'dark' ? 'checked' : ''; ?>>
                  🌙 Dark
                </label>
              </div>
            </div>
          </div>

          <!-- ── RESULTS PER PAGE ── -->
          <div class="pref-card">
            <div class="pref-card-header">
              <span class="pref-icon">📄</span>
              <div>
                <h5 class="pref-title">Results Per Page</h5>
                <p class="pref-desc">How many search results to show per page. Applied automatically in search pagination.</p>
              </div>
            </div>
            <div class="pref-option">
              <div class="pref-option-label">
                <span>Results Per Page</span>
                <small>Current: <strong><?php echo $prefs['results_per_page']; ?></strong>
                  <?php
                    $rpp_source = get_cookie_pref('results_per_page') !== null ? 'cookie' : 'system default';
                    echo "<span class='pref-source-badge'>{$rpp_source}</span>";
                  ?>
                </small>
              </div>
              <div class="size-options">
                <?php foreach (VALID_RESULTS as $n): ?>
                  <label class="size-btn <?php echo $prefs['results_per_page'] == $n ? 'active' : ''; ?>">
                    <input type="radio" name="results_per_page" value="<?php echo $n; ?>" <?php echo $prefs['results_per_page'] == $n ? 'checked' : ''; ?> style="display:none;">
                    <?php echo $n; ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- ── DEFAULT DICTIONARY ── -->
          <div class="pref-card">
            <div class="pref-card-header">
              <span class="pref-icon">📖</span>
              <div>
                <h5 class="pref-title">Default Dictionary</h5>
                <p class="pref-desc">The dictionary pre-selected when you open the Search page.</p>
              </div>
            </div>
            <div class="pref-option">
              <div class="pref-option-label">
                <span>Dictionary</span>
                <small>Current:
                  <?php
                    $dict_source = get_cookie_pref('default_dict') !== null ? 'cookie' : 'system default';
                    echo "<span class='pref-source-badge'>{$dict_source}</span>";
                  ?>
                </small>
              </div>
              <select name="default_dict" class="pref-select">
                <option value="0">All Dictionaries</option>
                <?php foreach ($dictionaries as $d): ?>
                  <option value="<?php echo $d['id']; ?>" <?php echo $prefs['default_dict'] == $d['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($d['name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- ── SAVE BUTTON ── -->
          <div class="d-grid mt-3">
            <button type="submit" class="btn-primary-custom" style="justify-content:center; border-radius:14px; padding:1rem;">
              💾 Save Preferences
            </button>
          </div>

        </form>

        <!-- ── RESET ── -->
        <div class="text-center mt-3">
          <a href="index.php?page=preferences&reset=1" class="btn-secondary-custom" style="font-size:0.85rem; padding:0.5rem 1.5rem;">
            Reset to Defaults
          </a>
        </div>

        <!-- ── CURRENT VALUES (debug info) ── -->
        <div class="pref-debug-card mt-4">
          <div class="pref-debug-title">🔍 Current Resolved Values</div>
          <div class="pref-debug-rows">
            <div class="pref-debug-row">
              <span>Theme</span>
              <span><?php echo htmlspecialchars($prefs['theme']); ?></span>
              <span class="pref-source-badge"><?php echo get_cookie_pref('theme') !== null ? 'cookie' : 'default'; ?></span>
            </div>
            <div class="pref-debug-row">
              <span>Results per page</span>
              <span><?php echo htmlspecialchars($prefs['results_per_page']); ?></span>
              <span class="pref-source-badge"><?php echo get_cookie_pref('results_per_page') !== null ? 'cookie' : 'default'; ?></span>
            </div>
            <div class="pref-debug-row">
              <span>Default dictionary</span>
              <span><?php echo htmlspecialchars($prefs['default_dict']); ?></span>
              <span class="pref-source-badge"><?php echo get_cookie_pref('default_dict') !== null ? 'cookie' : 'default'; ?></span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php
// ── Handle reset ─────────────────────────────────────────────
if (isset($_GET['reset'])) {
    setcookie(COOKIE_THEME,   '', time() - 3600, '/');
    setcookie(COOKIE_RESULTS, '', time() - 3600, '/');
    setcookie(COOKIE_DICT,    '', time() - 3600, '/');
    header('Location: index.php?page=preferences');
    exit;
}
?>

<script>
// Make theme radio buttons and size buttons update visually on click
document.querySelectorAll('.theme-radio-btn input').forEach(input => {
    input.addEventListener('change', () => {
        document.querySelectorAll('.theme-radio-btn').forEach(l => l.classList.remove('active'));
        input.closest('.theme-radio-btn').classList.add('active');
    });
});

document.querySelectorAll('.size-btn input').forEach(input => {
    input.addEventListener('change', () => {
        document.querySelectorAll('.size-btn').forEach(l => l.classList.remove('active'));
        input.closest('.size-btn').classList.add('active');
    });
});
</script>