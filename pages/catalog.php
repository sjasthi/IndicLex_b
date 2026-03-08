<?php
// ── PLACEHOLDER DATA ──
// Replace this array with a real database query when ready, e.g.:
// $words = $pdo->query("SELECT * FROM words ORDER BY word ASC")->fetchAll();

$words = [
  ['word' => 'Ameliorate', 'pos' => 'verb',      'phonetic' => '/əˈmiː.li.ə.reɪt/', 'definition' => 'To make something bad or unsatisfactory better.'],
  ['word' => 'Benevolent', 'pos' => 'adjective',  'phonetic' => '/bɪˈnev.ə.lənt/',   'definition' => 'Well meaning and kindly; generous or charitable.'],
  ['word' => 'Candor',     'pos' => 'noun',       'phonetic' => '/ˈkæn.dər/',         'definition' => 'The quality of being open and honest in expression; frankness.'],
  ['word' => 'Deft',       'pos' => 'adjective',  'phonetic' => '/deft/',             'definition' => 'Neatly skillful and quick in one\'s movements or actions.'],
  ['word' => 'Ephemeral',  'pos' => 'adjective',  'phonetic' => '/ɪˈfem.ər.əl/',     'definition' => 'Lasting for a very short time; transitory.'],
  ['word' => 'Forlorn',    'pos' => 'adjective',  'phonetic' => '/fəˈlɔːrn/',         'definition' => 'Pitifully sad and abandoned or lonely.'],
  ['word' => 'Gregarious', 'pos' => 'adjective',  'phonetic' => '/ɡrɪˈɡeə.ri.əs/',   'definition' => 'Fond of company; sociable.'],
  ['word' => 'Halcyon',    'pos' => 'adjective',  'phonetic' => '/ˈhæl.si.ən/',       'definition' => 'Denoting a period of time in the past that was idyllically happy and peaceful.'],
  ['word' => 'Insouciant', 'pos' => 'adjective',  'phonetic' => '/ɪnˈsuː.si.ənt/',   'definition' => 'Showing a casual lack of concern; indifferent.'],
  ['word' => 'Jovial',     'pos' => 'adjective',  'phonetic' => '/ˈdʒəʊ.vi.əl/',     'definition' => 'Cheerful and friendly.'],
  ['word' => 'Kinetic',    'pos' => 'adjective',  'phonetic' => '/kɪˈnet.ɪk/',        'definition' => 'Relating to or resulting from motion; lively and active.'],
  ['word' => 'Languid',    'pos' => 'adjective',  'phonetic' => '/ˈlæŋ.ɡwɪd/',       'definition' => 'Displaying or having a disinclination for physical exertion or effort; slow and relaxed.'],
];

// Group words by first letter for alphabetical browsing
$grouped = [];
foreach ($words as $entry) {
  $letter = strtoupper($entry['word'][0]);
  $grouped[$letter][] = $entry;
}
ksort($grouped);
?>

<section class="catalog-section">
  <div class="container">

    <!-- Page Header -->
    <div class="page-header">
      <div class="section-label">Browse</div>
      <h1 class="page-title">Word Catalog</h1>
      <p class="page-subtitle">A curated collection of words worth knowing. Browse alphabetically or jump to a letter.</p>
    </div>

    <!-- Alphabet Jump Nav -->
    <div class="alpha-nav">
      <?php foreach (array_keys($grouped) as $letter): ?>
        <a href="#letter-<?php echo $letter; ?>" class="alpha-link"><?php echo $letter; ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Word Groups -->
    <?php foreach ($grouped as $letter => $entries): ?>
      <div class="letter-group" id="letter-<?php echo $letter; ?>">
        <div class="letter-heading"><?php echo $letter; ?></div>
        <div class="row g-4">
          <?php foreach ($entries as $entry): ?>
            <div class="col-md-6 col-lg-4">
              <div class="word-card">
                <div class="word-card-top">
                  <span class="word-card-word"><?php echo htmlspecialchars($entry['word']); ?></span>
                  <span class="word-card-pos"><?php echo htmlspecialchars($entry['pos']); ?></span>
                </div>
                <div class="word-card-phonetic"><?php echo htmlspecialchars($entry['phonetic']); ?></div>
                <p class="word-card-def"><?php echo htmlspecialchars($entry['definition']); ?></p>
                <a href="index.php?page=search&q=<?php echo urlencode($entry['word']); ?>" class="word-card-link">View full entry →</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</section>