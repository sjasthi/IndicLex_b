/* ============================================================
   DictionaryHub — theme.js
   ============================================================ */

function toggleTheme() {
  const html    = document.documentElement;
  const current = html.getAttribute('data-bs-theme');
  const next    = current === 'dark' ? 'light' : 'dark';

  html.setAttribute('data-bs-theme', next);
  localStorage.setItem('theme', next);

  const btn = document.getElementById('themeBtn');
  if (btn) btn.textContent = next === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
}

document.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('theme') || 'light';
  const btn   = document.getElementById('themeBtn');
  if (btn) btn.textContent = saved === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
});