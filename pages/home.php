<!-- HERO -->
<section class="hero">
  <div class="hero-bg-text">Words</div>
  <div class="floating-words" id="floatingWords"></div>
  <div class="container hero-content">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1>Every word has<br>a <em>story</em> to tell.</h1>
        <p class="lead">Explore Telugu–English definitions, transliterations, and usage examples. Your guide to the language, one word at a time.</p>
        <div class="hero-actions">
          <a href="index.php?page=search" class="btn-primary-custom">🔍 Search a Word</a>
          <a href="index.php?page=catalog" class="btn-secondary-custom">Browse Catalog</a>
          <a href="index.php?page=admin_import" class="btn-secondary-custom">📥 Import Dictionary</a>
        </div>
      </div>
      <div class="col-lg-5 mt-5 mt-lg-0">
        <div class="search-section">
          <div class="search-wrap">
            <input type="text" id="heroSearch" placeholder='Try "नमस्ते" or "Namaste"…' onkeydown="handleSearch(event)">
            <button onclick="goSearch()">Search →</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>