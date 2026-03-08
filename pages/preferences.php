<section class="preferences-section">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <div class="section-label">Settings</div>
      <h1 class="page-title">Preferences</h1>
      <p class="page-subtitle">Customise your DictionaryHub experience.</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-7">

        <!-- Appearance -->
        <div class="pref-card">
          <div class="pref-card-header">
            <span class="pref-icon">🎨</span>
            <div>
              <h5 class="pref-title">Appearance</h5>
              <p class="pref-desc">Choose how DictionaryHub looks for you.</p>
            </div>
          </div>
          <div class="pref-option">
            <div class="pref-option-label">
              <span>Dark Mode</span>
              <small>Easier on the eyes in low light</small>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" id="darkModeToggle" onchange="handleDarkModeToggle(this)">
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>

        <!-- Text Size -->
        <div class="pref-card">
          <div class="pref-card-header">
            <span class="pref-icon">🔤</span>
            <div>
              <h5 class="pref-title">Text Size</h5>
              <p class="pref-desc">Adjust the size of definition text.</p>
            </div>
          </div>
          <div class="pref-option">
            <div class="pref-option-label">
              <span>Font Size</span>
              <small id="fontSizeLabel">Medium</small>
            </div>
            <div class="size-options">
              <button class="size-btn" onclick="setFontSize('small',  this)">A</button>
              <button class="size-btn active" onclick="setFontSize('medium', this)">A</button>
              <button class="size-btn large" onclick="setFontSize('large',  this)">A</button>
            </div>
          </div>
        </div>

        <!-- Language -->
        <div class="pref-card">
          <div class="pref-card-header">
            <span class="pref-icon">🌍</span>
            <div>
              <h5 class="pref-title">Language</h5>
              <p class="pref-desc">Set your preferred dictionary language.</p>
            </div>
          </div>
          <div class="pref-option">
            <div class="pref-option-label">
              <span>Dictionary Language</span>
              <small>Affects definitions and examples</small>
            </div>
            <select class="pref-select" id="languageSelect" onchange="savePref('language', this.value)">
              <option value="en-us">English (US)</option>
              <option value="en-gb">English (UK)</option>
              <option value="es">Spanish</option>
              <option value="fr">French</option>
              <option value="de">German</option>
              <option value="ja">Japanese</option>
            </select>
          </div>
        </div>

        <!-- Word of the Day -->
        <div class="pref-card">
          <div class="pref-card-header">
            <span class="pref-icon">✦</span>
            <div>
              <h5 class="pref-title">Word of the Day</h5>
              <p class="pref-desc">Show the word of the day on the home page.</p>
            </div>
          </div>
          <div class="pref-option">
            <div class="pref-option-label">
              <span>Show Word of the Day</span>
              <small>Displayed in the hero section</small>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" id="wodToggle" checked onchange="savePref('showWod', this.checked)">
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>

        <!-- Save Notice -->
        <div class="save-notice" id="saveNotice">✓ Preferences saved</div>

        <!-- Reset -->
        <div class="text-center mt-4">
          <button class="btn-secondary-custom" onclick="resetPrefs()">Reset to Defaults</button>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  // ── Load saved preferences on page load ──
  document.addEventListener('DOMContentLoaded', () => {
    // Dark mode toggle state
    const theme = localStorage.getItem('theme') || 'light';
    document.getElementById('darkModeToggle').checked = theme === 'dark';

    // Font size
    const fontSize = localStorage.getItem('fontSize') || 'medium';
    applyFontSize(fontSize);
    syncFontButtons(fontSize);

    // Language
    const lang = localStorage.getItem('language') || 'en-us';
    document.getElementById('languageSelect').value = lang;

    // Word of the Day
    const showWod = localStorage.getItem('showWod');
    document.getElementById('wodToggle').checked = showWod !== 'false';
  });

  // ── Dark Mode ──
  function handleDarkModeToggle(checkbox) {
    const next = checkbox.checked ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('theme', next);
    const btn = document.getElementById('themeBtn');
    if (btn) btn.textContent = next === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
    showSaveNotice();
  }

  // ── Font Size ──
  function setFontSize(size, btn) {
    applyFontSize(size);
    syncFontButtons(size);
    localStorage.setItem('fontSize', size);
    showSaveNotice();
  }

  function applyFontSize(size) {
    const map = { small: '14px', medium: '16px', large: '19px' };
    const labels = { small: 'Small', medium: 'Medium', large: 'Large' };
    document.body.style.fontSize = map[size] || '16px';
    const label = document.getElementById('fontSizeLabel');
    if (label) label.textContent = labels[size] || 'Medium';
  }

  function syncFontButtons(size) {
    document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
    const map = { small: 0, medium: 1, large: 2 };
    const btns = document.querySelectorAll('.size-btn');
    if (btns[map[size]]) btns[map[size]].classList.add('active');
  }

  // ── Generic preference saver ──
  function savePref(key, value) {
    localStorage.setItem(key, value);
    showSaveNotice();
  }

  // ── Reset ──
  function resetPrefs() {
    localStorage.removeItem('theme');
    localStorage.removeItem('fontSize');
    localStorage.removeItem('language');
    localStorage.removeItem('showWod');
    document.documentElement.setAttribute('data-bs-theme', 'light');
    document.getElementById('darkModeToggle').checked = false;
    document.getElementById('languageSelect').value   = 'en-us';
    document.getElementById('wodToggle').checked      = true;
    applyFontSize('medium');
    syncFontButtons('medium');
    const btn = document.getElementById('themeBtn');
    if (btn) btn.textContent = '🌙 Dark Mode';
    showSaveNotice();
  }

  // ── Save notice ──
  function showSaveNotice() {
    const notice = document.getElementById('saveNotice');
    notice.classList.add('visible');
    setTimeout(() => notice.classList.remove('visible'), 2000);
  }
</script>