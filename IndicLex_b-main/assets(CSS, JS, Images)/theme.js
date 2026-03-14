document.getElementById('themeToggle').addEventListener('click', function() {
    let htmlTag = document.documentElement;
    let currentTheme = htmlTag.getAttribute('data-bs-theme');
    let newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    // Update the visual theme immediately
    htmlTag.setAttribute('data-bs-theme', newTheme);

    // Save the new theme to a cookie that expires in 30 days
    document.cookie = "theme=" + newTheme + "; path=/; max-age=" + (30*24*60*60);
});