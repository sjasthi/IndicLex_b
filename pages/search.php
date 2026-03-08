<?php
// ── PLACEHOLDER DATA ──
// Replace this array with a real database query when ready, e.g.:
// $stmt = $pdo->prepare("SELECT * FROM words WHERE word LIKE ?");
// $stmt->execute(["%{$query}%"]);
// $results = $stmt->fetchAll();

$all_words = [
  ['word' => 'Ameliorate', 'pos' => 'verb',      'phonetic' => '/əˈmiː.li.ə.reɪt/', 'definition' => 'To make something bad or unsatisfactory better.',                                           'example' => 'The new policy was designed to ameliorate the living conditions of the residents.',  'synonyms' => ['improve', 'better', 'enhance']],
  ['word' => 'Benevolent', 'pos' => 'adjective',  'phonetic' => '/bɪˈnev.ə.lənt/',   'definition' => 'Well meaning and kindly; generous or charitable.',                                         'example' => 'The benevolent donor funded the entire school library.',                              'synonyms' => ['kind', 'generous', 'charitable']],
  ['word' => 'Candor',     'pos' => 'noun',       'phonetic' => '/ˈkæn.dər/',         'definition' => 'The quality of being open and honest in expression; frankness.',                          'example' => 'She appreciated his candor when he told her the truth.',                             'synonyms' => ['honesty', 'frankness', 'openness']],
  ['word' => 'Deft',       'pos' => 'adjective',  'phonetic' => '/deft/',             'definition' => 'Neatly skillful and quick in one\'s movements or actions.',                               'example' => 'With a deft flick of the wrist, she caught the falling glass.',                     'synonyms' => ['skillful', 'adroit', 'nimble']],
  ['word' => 'Ephemeral',  'pos' => 'adjective',  'phonetic' => '/ɪˈfem.ər.əl/',     'definition' => 'Lasting for a very short time; transitory.',                                              'example' => 'The beauty of cherry blossoms is ephemeral, lasting only a few days.',              'synonyms' => ['fleeting', 'transient', 'momentary']],
  ['word' => 'Forlorn',    'pos' => 'adjective',  'phonetic' => '/fəˈlɔːrn/',         'definition' => 'Pitifully sad and abandoned or lonely.',                                                  'example' => 'The forlorn puppy sat waiting by the door.',                                         'synonyms' => ['lonely', 'abandoned', 'desolate']],
  ['word' => 'Gregarious', 'pos' => 'adjective',  'phonetic' => '/ɡrɪˈɡeə.ri.əs/',   'definition' => 'Fond of company; sociable.',                                                              'example' => 'His gregarious nature made him the life of every party.',                           'synonyms' => ['sociable', 'outgoing', 'friendly']],
  ['word' => 'Halcyon',    'pos' => 'adjective',  'phonetic' => '/ˈhæl.si.ən/',       'definition' => 'Denoting a period of time in the past that was idyllically happy and peaceful.',         'example' => 'She often thought back to those halcyon days of childhood.',                        'synonyms' => ['peaceful', 'serene', 'idyllic']],
  ['word' => 'Insouciant', 'pos' => 'adjective',  'phonetic' => '/ɪnˈsuː.si.ənt/',   'definition' => 'Showing a casual lack of concern; indifferent.',                                          'example' => 'He gave an insouciant shrug and walked away.',                                       'synonyms' => ['carefree', 'nonchalant', 'indifferent']],
  ['word' => 'Jovial',     'pos' => 'adjective',  'phonetic' => '/ˈdʒəʊ.vi.əl/',     'definition' => 'Cheerful and friendly.',                                                                  'example' => 'The jovial host made everyone feel welcome.',                                        'synonyms' => ['cheerful', 'merry', 'jolly']],
  ['word' => 'Sonder',     'pos' => 'noun',       'phonetic' => '/ˈsɒn.dər/',         'definition' => 'The profound realization that each passerby has a life as vivid and complex as one\'s own.', 'example' => 'Walking through the crowded station, she was struck by a sense of sonder.',    'synonyms' => ['empathy', 'awareness', 'perspective']],
  ['word' => 'Serendipity','pos' => 'noun',       'phonetic' => '/ˌser.ənˈdɪp.ɪ.ti/','definition' => 'The occurrence and development of events by chance in a happy or beneficial way.',        'example' => 'Finding that rare book at the garage sale was pure serendipity.',                   'synonyms' => ['chance', 'fortune', 'luck']],
];

// Handle search query
$query   = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query !== '') {
  foreach ($all_words as $entry) {
    if (stripos($entry['word'], $query) !== false || stripos($entry['definition'], $query) !== false) {
      $results[] = $entry;
    }
  }
}
?>

<section class="search-page-section">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <div class="section-label">Discover</div>
      <h1 class="page-title">Search Words</h1>
      <p class="page-subtitle">Type a word to explore its definition, pronunciation, and more.</p>
    </div>

    <!-- Search Bar -->
    <div class="search-page-wrap">
      <div class="search-wrap" style="max-width: 680px; margin: 0 auto 3rem;">
        <form method="GET" action="index.php" style="display:contents;">
          <input type="hidden" name="page" value="search">
          <input
            type="text"
            name="q"
            id="searchInput"
            placeholder='Try "ephemeral" or "sonder"…'
            value="<?php echo htmlspecialchars($query); ?>"
            autocomplete="off"
          >
          <button type="submit">Search →</button>
        </form>
      </div>
    </div>

    <!-- Results -->
    <?php if ($query !== ''): ?>

      <?php if (count($results) === 0): ?>
        <div class="no-results">
          <div class="no-results-icon">🔍</div>
          <h3>No results for "<?php echo htmlspecialchars($query); ?>"</h3>
          <p>Try a different spelling or browse the <a href="index.php?page=catalog">catalog</a>.</p>
        </div>

      <?php else: ?>
        <div class="results-count">
          <?php echo count($results); ?> result<?php echo count($results) !== 1 ? 's' : ''; ?> for "<strong><?php echo htmlspecialchars($query); ?></strong>"
        </div>

        <div class="row g-4">
          <?php foreach ($results as $entry): ?>
            <div class="col-12">
              <div class="result-card">
                <div class="result-card-header">
                  <div>
                    <span class="result-word"><?php echo htmlspecialchars($entry['word']); ?></span>
                    <span class="result-phonetic"><?php echo htmlspecialchars($entry['phonetic']); ?></span>
                  </div>
                  <span class="word-card-pos"><?php echo htmlspecialchars($entry['pos']); ?></span>
                </div>
                <p class="result-definition"><?php echo htmlspecialchars($entry['definition']); ?></p>
                <?php if (!empty($entry['example'])): ?>
                  <p class="result-example">"<?php echo htmlspecialchars($entry['example']); ?>"</p>
                <?php endif; ?>
                <?php if (!empty($entry['synonyms'])): ?>
                  <div class="result-synonyms">
                    <span class="synonyms-label">Synonyms:</span>
                    <?php foreach ($entry['synonyms'] as $syn): ?>
                      <a href="index.php?page=search&q=<?php echo urlencode($syn); ?>" class="synonym-tag"><?php echo htmlspecialchars($syn); ?></a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- Suggested searches when no query yet -->
      <div class="suggestions">
        <p class="section-label" style="text-align:center; margin-bottom: 1.5rem;">Try one of these</p>
        <div class="suggestion-tags">
          <?php
          $suggestions = ['Ephemeral', 'Sonder', 'Serendipity', 'Halcyon', 'Gregarious', 'Candor'];
          foreach ($suggestions as $s):
          ?>
            <a href="index.php?page=search&q=<?php echo urlencode($s); ?>" class="suggestion-tag"><?php echo $s; ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>