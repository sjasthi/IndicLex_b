<!-- ============================================================
     pages/help.php — End User Documentation
     ============================================================ -->

<section class="help-section">
  <div class="container py-5">

    <!-- Header -->
    <div class="page-header">
      <div class="section-label">Documentation</div>
      <h1 class="page-title">Help & User Guide</h1>
      <p class="page-subtitle">Everything you need to know about using DictionaryHub — searching words, adjusting preferences, and creating an account.</p>
    </div>

    <!-- Quick nav -->
    <div class="help-quicknav">
      <a href="#getting-started" class="help-nav-pill">🚀 Getting Started</a>
      <a href="#searching"       class="help-nav-pill">🔍 Searching</a>
      <a href="#search-modes"    class="help-nav-pill">⚙️ Search Modes</a>
      <a href="#autocomplete"    class="help-nav-pill">💡 Autocomplete</a>
      <a href="#catalog"         class="help-nav-pill">📖 Catalog</a>
      <a href="#preferences"     class="help-nav-pill">🎨 Preferences</a>
      <a href="#account"         class="help-nav-pill">👤 Account</a>
      <a href="#word-length"     class="help-nav-pill">🔠 Word Length</a>
      <a href="#faq"             class="help-nav-pill">❓ FAQ</a>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">

        <!-- Getting Started -->
        <div class="help-card" id="getting-started">
          <div class="help-card-icon">🚀</div>
          <h2 class="help-card-title">Getting Started</h2>
          <p>DictionaryHub is a multilingual dictionary covering <strong>English</strong>, <strong>Telugu</strong>, and <strong>Hindi</strong>. It contains over 8,300 word entries that you can search, browse, and explore.</p>
          <p>You do not need an account to use the dictionary. Simply visit the <strong>Search</strong> page and type any word in English, Telugu, or Hindi to get started.</p>
          <div class="help-tip">
            💡 <strong>Tip:</strong> You can type in English to find Telugu and Hindi translations, or type in Telugu/Hindi script to find the English equivalent.
          </div>
        </div>

        <!-- Searching -->
        <div class="help-card" id="searching">
          <div class="help-card-icon">🔍</div>
          <h2 class="help-card-title">Searching for Words</h2>
          <p>To search, go to the <strong>Search</strong> page from the navigation bar. Type your word into the search box and press Enter or click <strong>Search →</strong>.</p>

          <h5 class="mt-4">What you can search for:</h5>
          <ul class="help-list">
            <li><strong>English words</strong> — e.g. <code>water</code>, <code>happy</code>, <code>book</code></li>
            <li><strong>Telugu script</strong> — e.g. <code>నీళ్ళు</code>, <code>సంతోషం</code></li>
            <li><strong>Hindi script</strong> — e.g. <code>पानी</code>, <code>खुश</code></li>
            <li><strong>Transliteration</strong> — e.g. <code>Namaste</code>, <code>Paani</code></li>
          </ul>

          <h5 class="mt-4">Reading search results:</h5>
          <p>Each result card shows:</p>
          <ul class="help-list">
            <li>The <strong>English word</strong> at the top in large text</li>
            <li>The <span class="translation-lang-badge telugu-badge" style="display:inline;">Telugu</span> translation</li>
            <li>The <span class="translation-lang-badge hindi-badge" style="display:inline;">Hindi</span> translation</li>
            <li>Example sentences in the source and target language where available</li>
            <li>The dictionary it belongs to (shown as a small badge)</li>
          </ul>
        </div>

        <!-- Search Modes -->
        <div class="help-card" id="search-modes">
          <div class="help-card-icon">⚙️</div>
          <h2 class="help-card-title">Search Modes Explained</h2>
          <p>The <strong>Search Mode</strong> dropdown controls how your query is matched against dictionary entries. There are four modes:</p>

          <div class="help-mode-grid">
            <div class="help-mode-card">
              <div class="help-mode-icon">🔍</div>
              <div class="help-mode-name">Substring</div>
              <div class="help-mode-desc">Finds any word <strong>containing</strong> your query anywhere inside it.</div>
              <div class="help-mode-example">Search <code>at</code> → matches <em>water</em>, <em>cat</em>, <em>late</em></div>
            </div>
            <div class="help-mode-card">
              <div class="help-mode-icon">🎯</div>
              <div class="help-mode-name">Exact</div>
              <div class="help-mode-desc">Only finds words that <strong>exactly match</strong> your query.</div>
              <div class="help-mode-example">Search <code>water</code> → matches only <em>water</em></div>
            </div>
            <div class="help-mode-card">
              <div class="help-mode-icon">▶️</div>
              <div class="help-mode-name">Prefix</div>
              <div class="help-mode-desc">Finds words that <strong>start with</strong> your query.</div>
              <div class="help-mode-example">Search <code>wa</code> → matches <em>water</em>, <em>walk</em>, <em>wall</em></div>
            </div>
            <div class="help-mode-card">
              <div class="help-mode-icon">◀️</div>
              <div class="help-mode-name">Suffix</div>
              <div class="help-mode-desc">Finds words that <strong>end with</strong> your query.</div>
              <div class="help-mode-example">Search <code>ing</code> → matches <em>running</em>, <em>walking</em></div>
            </div>
          </div>

          <div class="help-tip mt-3">
            💡 <strong>Tip:</strong> If you're not sure which mode to use, keep it on <strong>Substring</strong> — it gives the most results.
          </div>
        </div>

        <!-- Autocomplete -->
        <div class="help-card" id="autocomplete">
          <div class="help-card-icon">💡</div>
          <h2 class="help-card-title">Autocomplete Suggestions</h2>
          <p>As you type in the search box, a dropdown of suggested words will appear automatically. This uses your first few characters to find the most likely matches.</p>
          <ul class="help-list">
            <li>Click any suggestion to search for it immediately</li>
            <li>Use the <strong>↑ ↓</strong> arrow keys to navigate suggestions</li>
            <li>Press <strong>Enter</strong> to select the highlighted suggestion</li>
            <li>Press <strong>Escape</strong> to close the dropdown</li>
          </ul>
          <div class="help-tip">
            💡 <strong>Tip:</strong> Changing the <strong>Dictionary</strong> dropdown will update the suggestions to only show words from that dictionary.
          </div>
        </div>

        <!-- Catalog -->
        <div class="help-card" id="catalog">
          <div class="help-card-icon">📖</div>
          <h2 class="help-card-title">Browsing the Catalog</h2>
          <p>The <strong>Catalog</strong> page lets you browse all words without searching. It is currently under development and will be available soon.</p>
          <p>In the meantime, use the <strong>Search</strong> page with <strong>Prefix</strong> mode to browse words starting with a specific letter. For example, search <code>a</code> in Prefix mode to see all words beginning with A.</p>
        </div>

        <!-- Preferences -->
        <div class="help-card" id="preferences">
          <div class="help-card-icon">🎨</div>
          <h2 class="help-card-title">Preferences & Settings</h2>
          <p>Visit the <strong>Preferences</strong> page from the navigation bar to customise your experience. Your settings are saved automatically and applied on every page.</p>

          <table class="help-table">
            <thead><tr><th>Setting</th><th>Options</th><th>What it does</th></tr></thead>
            <tbody>
              <tr>
                <td><strong>Theme</strong></td>
                <td>Light / Dark</td>
                <td>Changes the colour scheme of the entire site</td>
              </tr>
              <tr>
                <td><strong>Results per page</strong></td>
                <td>5 / 10 / 25 / 50</td>
                <td>Controls how many search results appear before pagination</td>
              </tr>
              <tr>
                <td><strong>Default dictionary</strong></td>
                <td>Any dictionary or All</td>
                <td>Pre-selects a dictionary when you open the Search page</td>
              </tr>
            </tbody>
          </table>

          <div class="help-tip mt-3">
            💡 <strong>Tip:</strong> Preferences are stored as cookies — they will persist even after closing the browser, but will reset if you clear your cookies.
          </div>
        </div>

        <!-- Account -->
        <div class="help-card" id="account">
          <div class="help-card-icon">👤</div>
          <h2 class="help-card-title">Creating an Account</h2>
          <p>You can create a free account by clicking <strong>Register</strong> in the navigation bar.</p>

          <h5 class="mt-3">Password requirements:</h5>
          <ul class="help-list">
            <li>At least <strong>8 characters</strong></li>
            <li>At least one <strong>uppercase letter</strong></li>
            <li>At least one <strong>number</strong></li>
            <li>At least one <strong>special character</strong> (e.g. !, @, #, $)</li>
          </ul>

          <p class="mt-3">Once registered, log in using the <strong>Login</strong> button. Your password is stored securely using bcrypt hashing — it is never stored as plain text.</p>

          <div class="help-tip">
            💡 <strong>Note:</strong> Regular user accounts are for accessing the dictionary. Admin accounts are managed separately by the site administrator.
          </div>
        </div>

        <!-- Word Length -->
        <div class="help-card" id="word-length">
          <div class="help-card-icon">🔠</div>
          <h2 class="help-card-title">Word Length Matching</h2>
          <p>At the bottom of the Search page you will find a <strong>Word Length Matching</strong> callout. This links to <a href="https://telugupuzzles.com/apps.php" target="_blank" rel="noopener">Telugu Puzzles</a> — an external tool for finding Telugu words by letter count.</p>
          <p>This is useful for:</p>
          <ul class="help-list">
            <li>Telugu crossword puzzles</li>
            <li>Word games requiring a specific letter count</li>
            <li>Finding alternative words of the same length</li>
          </ul>
          <p>Clicking the link opens Telugu Puzzles in a new tab so you don't lose your place on DictionaryHub.</p>
        </div>

        <!-- FAQ -->
        <div class="help-card" id="faq">
          <div class="help-card-icon">❓</div>
          <h2 class="help-card-title">Frequently Asked Questions</h2>

          <div class="help-faq-item">
            <div class="help-faq-q">How many words are in the dictionary?</div>
            <div class="help-faq-a">Over 8,300 English–Telugu–Hindi entries, sourced from a curated academic word list.</div>
          </div>
          <div class="help-faq-item">
            <div class="help-faq-q">Can I search in Telugu or Hindi script?</div>
            <div class="help-faq-a">Yes — the search works across English, Telugu, Hindi, and transliteration simultaneously.</div>
          </div>
          <div class="help-faq-item">
            <div class="help-faq-q">Why does my theme reset?</div>
            <div class="help-faq-a">Preferences are stored in cookies. If you cleared your browser cookies, the theme will reset to the default (light mode).</div>
          </div>
          <div class="help-faq-item">
            <div class="help-faq-q">I can't find a word — what should I try?</div>
            <div class="help-faq-a">Try switching to <strong>Substring</strong> mode which searches inside words. Also try searching the English translation if you were searching in Telugu, or vice versa.</div>
          </div>
          <div class="help-faq-item">
            <div class="help-faq-q">Is the dictionary free to use?</div>
            <div class="help-faq-a">Yes — DictionaryHub is completely free. No subscription or payment is required.</div>
          </div>
          <div class="help-faq-item">
            <div class="help-faq-q">How do I report a missing or incorrect word?</div>
            <div class="help-faq-a">Contact the site administrator. If you have an admin account, you can add or edit entries directly through the Admin Dashboard.</div>
          </div>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <div class="help-sidebar">

          <div class="help-sidebar-card">
            <h5>Quick Links</h5>
            <ul class="help-sidebar-links">
              <li><a href="index.php?page=search">🔍 Search Dictionary</a></li>
              <li><a href="index.php?page=catalog">📖 Browse Catalog</a></li>
              <li><a href="index.php?page=preferences">🎨 Preferences</a></li>
              <li><a href="index.php?page=register">👤 Create Account</a></li>
              <li><a href="index.php?page=login">🔐 Login</a></li>
            </ul>
          </div>

          <div class="help-sidebar-card mt-3">
            <h5>About DictionaryHub</h5>
            <p style="font-size:0.88rem; color:var(--muted); line-height:1.7;">
              DictionaryHub is a multilingual English–Telugu–Hindi dictionary built as part of an ICS 499 project. It provides search, browsing, and administrative tools for managing multilingual word entries.
            </p>
            <p style="font-size:0.88rem; color:var(--muted);">Version: 1.0 &nbsp;·&nbsp; <?php echo date('Y'); ?></p>
          </div>

          <div class="help-sidebar-card mt-3">
            <h5>Supported Browsers</h5>
            <ul class="help-sidebar-links">
              <li>✅ Google Chrome</li>
              <li>✅ Mozilla Firefox</li>
              <li>✅ Microsoft Edge</li>
              <li>✅ Safari</li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>