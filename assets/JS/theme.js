/* ============================================================
   DictionaryHub — theme.js
   Theme is set server-side via PHP cookie on page load.
   This JS handles the toggle button for instant switching,
   and saves the preference via a quick fetch so the cookie
   is updated for the next page load too.
   ============================================================ */

function toggleTheme() {
    const html    = document.documentElement;
    const current = html.getAttribute('data-bs-theme');
    const next    = current === 'dark' ? 'light' : 'dark';

    // Apply instantly on this page
    html.setAttribute('data-bs-theme', next);

    // Update button label
    const btn = document.getElementById('themeBtn');
    if (btn) btn.textContent = next === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';

    // Save to cookie via preferences page (POST)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?page=preferences';
    form.style.display = 'none';

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'theme';
    input.value = next;
    form.appendChild(input);

    // Also keep current page after save
    const redirect = document.createElement('input');
    redirect.type  = 'hidden';
    redirect.name  = 'redirect';
    redirect.value = window.location.href;
    form.appendChild(redirect);

    document.body.appendChild(form);
    form.submit();
}

// Sync button label on page load to match server-rendered theme
document.addEventListener('DOMContentLoaded', () => {
    const theme = document.documentElement.getAttribute('data-bs-theme');
    const btn   = document.getElementById('themeBtn');
    if (btn) btn.textContent = theme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
});