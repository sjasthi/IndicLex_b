/* ============================================================
   DictionaryHub — main.js
   ============================================================ */

// ── FLOATING BACKGROUND WORDS (home page only) ──
const container = document.getElementById('floatingWords');

if (container) {
  const words = [
    'define', 'lexicon', 'etymology', 'syntax', 'verbose',
    'lingua', 'prose', 'idiom', 'vernacular', 'dialect',
    'metaphor', 'nuance', 'cadence', 'diction', 'syllable',
    'phoneme', 'morpheme', 'semantics', 'pragmatics', 'rhetoric'
  ];

  words.forEach((word) => {
    const el = document.createElement('div');
    el.className            = 'float-word';
    el.textContent          = word;
    el.style.left           = `${Math.random() * 80}%`;
    el.style.fontSize       = `${1.5 + Math.random() * 3}rem`;
    el.style.animationDuration = `${12 + Math.random() * 18}s`;
    el.style.animationDelay = `-${Math.random() * 20}s`;
    container.appendChild(el);
  });
}

// ── HERO SEARCH (home page only) ──
function goSearch() {
  const input = document.getElementById('heroSearch');
  if (!input) return;
  const q = input.value.trim();
  if (q) window.location.href = `index.php?page=search&q=${encodeURIComponent(q)}`;
}

function handleSearch(e) {
  if (e.key === 'Enter') goSearch();
}