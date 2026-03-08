/* ============================================================
   DictionaryHub — theme.js
   Handles dark/light mode toggle and persistence
   ============================================================ */

function toggleTheme() {
  const html    = document.documentElement;
  const current = html.getAttribute('data-bs-theme');
  const next    = current === 'dark' ? 'light' : 'dark';

  html.setAttribute('data-bs-theme', next);
  localStorage.setItem('theme', next);

  document.getElementById('themeBtn').textContent =
    next === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
}

// Sync button label on every page load
document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('theme') || 'light';
  const btn   = document.getElementById('themeBtn');
  if (btn) {
    btn.textContent = saved === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
  }
});