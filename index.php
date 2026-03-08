<!DOCTYPE html>
<html lang="en">
<script>
  document.documentElement.setAttribute(
    'data-bs-theme',
    localStorage.getItem('theme') || 'light'
  );
</script>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DictionaryHub — Words Come Alive</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">📖 DictionaryHub</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php?page=catalog">Catalog</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php?page=search">Search</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php?page=preferences">Preferences</a></li>
        </ul>
        <button onclick="toggleTheme()" class="theme-btn" id="themeBtn">🌙 Dark Mode</button>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-bg-text">Words</div>
    <div class="floating-words" id="floatingWords"></div>
    <div class="container hero-content">
      <div class="row align-items-center">
        <div class="col-lg-7">
          <div class="word-of-day-tag">✦ Word of the Day</div>
          <h1>Every word has<br>a <em>story</em> to tell.</h1>
          <p class="lead">Explore definitions, origins, synonyms, and usage examples. Your personal guide to the English language — and every language beyond.</p>
          <div class="hero-actions">
            <a href="index.php?page=search" class="btn-primary-custom">🔍 Search a Word</a>
            <a href="index.php?page=catalog" class="btn-secondary-custom">Browse Catalog</a>
          </div>
        </div>
        <div class="col-lg-5 mt-5 mt-lg-0">
          <div class="search-section">
            <div class="search-wrap">
              <input type="text" id="heroSearch" placeholder='Try "ephemeral" or "serendipity"…' onkeydown="handleSearch(event)">
              <button onclick="goSearch()">Search →</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- STATS -->
  <section class="stats-section">
    <div class="container">
      <div class="row text-center g-4">
        <div class="col-6 col-md-3">
          <div class="stat-number">170<span>K+</span></div>
          <div class="stat-label">Words Defined</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-number">40<span>+</span></div>
          <div class="stat-label">Languages</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-number">1M<span>+</span></div>
          <div class="stat-label">Daily Lookups</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-number">100<span>%</span></div>
          <div class="stat-label">Free to Use</div>
        </div>
      </div>
    </div>
  </section>

  <!-- WORD OF THE DAY -->
  <section class="py-5">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-5">
          <div class="section-label">Featured</div>
          <h2 class="section-title">Word of<br>the Day</h2>
          <p class="text-muted-custom">Expand your vocabulary one word at a time. Each day we highlight a fascinating word — its meaning, pronunciation, and a real-world example.</p>
          <a href="index.php?page=catalog" class="btn-secondary-custom">See All Words →</a>
        </div>
        <div class="col-lg-7">
          <div class="wod-card">
            <div class="wod-label">✦ Today's Word</div>
            <div class="wod-word">Sonder</div>
            <div class="wod-phonetic">/ˈsɒn.dər/</div>
            <span class="wod-pos">noun</span>
            <p class="wod-def">The profound realization that each passerby has a life as vivid and complex as one's own — full of ambitions, routines, worries, and joy that continue invisibly around you.</p>
            <p class="wod-example">"Walking through the crowded train station, she was struck by a sudden sense of sonder."</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section class="features">
    <div class="container">
      <div class="text-center mb-5">
        <div class="section-label">What We Offer</div>
        <h2 class="section-title no-margin">Everything you need<br>to master language.</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <span class="feature-icon">📖</span>
            <h5>Rich Definitions</h5>
            <p>Detailed meanings with context, usage notes, and part-of-speech breakdowns for every word.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <span class="feature-icon">🔊</span>
            <h5>Pronunciation</h5>
            <p>IPA transcriptions and audio pronunciation so you always know exactly how to say a word.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <span class="feature-icon">🌿</span>
            <h5>Etymology</h5>
            <p>Trace words back to their roots. Discover Latin, Greek, and other origins that illuminate meaning.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <span class="feature-icon">🔗</span>
            <h5>Synonyms & Antonyms</h5>
            <p>Find the perfect word with curated lists of synonyms, antonyms, and related terms.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <div class="container">
      <h2>Ready to find your<br><em class="cta-em">next favourite word?</em></h2>
      <p>Start exploring thousands of definitions, stories, and linguistic wonders — all in one place.</p>
      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="index.php?page=search" class="btn-primary-custom">Start Searching →</a>
        <a href="index.php?page=catalog" class="btn-secondary-custom">Browse Catalog</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container">
      <p>© <?php echo date("Y"); ?> DictionaryHub | ICS 499 Project</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/theme.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>