<?php
// ============================================================
// pages/admin_docs.php — Developer Documentation
// ============================================================
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();
?>

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Developer Documentation</h1>
        <p class="admin-subtitle">Architecture, deployment instructions, and technical reference for contributors.</p>
      </div>
      <a href="index.php?page=admin_dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
    </div>

    <!-- Quick nav -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <a href="#stack"       class="btn btn-outline-secondary btn-sm">🧱 Tech Stack</a>
      <a href="#structure"   class="btn btn-outline-secondary btn-sm">📁 File Structure</a>
      <a href="#setup-local" class="btn btn-outline-secondary btn-sm">💻 Local Setup</a>
      <a href="#deploy"      class="btn btn-outline-secondary btn-sm">🚀 Bluehost Deploy</a>
      <a href="#database"    class="btn btn-outline-secondary btn-sm">🗄️ Database</a>
      <a href="#api"         class="btn btn-outline-secondary btn-sm">🔌 REST API</a>
      <a href="#admin"       class="btn btn-outline-secondary btn-sm">🛡️ Admin System</a>
      <a href="#git"         class="btn btn-outline-secondary btn-sm">🔀 Git Workflow</a>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">

        <!-- Tech Stack -->
        <div class="admin-card mb-4" id="stack">
          <div class="admin-card-header"><h5>🧱 Technology Stack</h5></div>
          <div class="admin-card-body">
            <table class="table table-sm table-hover mb-0">
              <thead><tr><th>Layer</th><th>Technology</th><th>Version</th><th>Purpose</th></tr></thead>
              <tbody>
                <tr><td>Backend</td><td>PHP</td><td>8.2</td><td>Server-side logic and routing</td></tr>
                <tr><td>Database</td><td>MySQL</td><td>8.0</td><td>Data storage</td></tr>
                <tr><td>Frontend</td><td>Bootstrap</td><td>5.3.2</td><td>Responsive layout and components</td></tr>
                <tr><td>Typography</td><td>Playfair Display + DM Sans</td><td>—</td><td>Google Fonts</td></tr>
                <tr><td>Tables</td><td>jQuery DataTables</td><td>1.13.7</td><td>Server-side paginated tables</td></tr>
                <tr><td>Charts</td><td>Chart.js</td><td>4.4.0</td><td>Reports visualizations</td></tr>
                <tr><td>Excel Import</td><td>PhpSpreadsheet</td><td>^2.1</td><td>Excel/CSV parsing via Composer</td></tr>
                <tr><td>Auth</td><td>PHP Sessions + bcrypt</td><td>—</td><td>Login, roles, password hashing</td></tr>
                <tr><td>Local Dev</td><td>XAMPP</td><td>8.2</td><td>Apache + MySQL + PHP on Windows</td></tr>
                <tr><td>Hosting</td><td>Bluehost</td><td>—</td><td>Production shared hosting</td></tr>
                <tr><td>Version Control</td><td>Git + GitHub</td><td>—</td><td>Source control and collaboration</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- File Structure -->
        <div class="admin-card mb-4" id="structure">
          <div class="admin-card-header"><h5>📁 Project File Structure</h5></div>
          <div class="admin-card-body">
            <pre class="dev-pre">IndicLex_b/
├── index.php                  ← Main router — all pages go through here
├── composer.json              ← PHP dependency declarations
├── schema.sql                 ← Run once in phpMyAdmin to create tables
├── .htaccess                  ← Clean URL routing + Bluehost PHP config
├── .gitignore                 ← Excludes vendor/, uploads/
├── setup.bat                  ← Windows: double-click to run composer install
│
├── api/
│   └── search.php             ← GET /api/search REST endpoint
│
├── includes/                  ← Shared PHP files (never accessed directly)
│   ├── db.php                 ← mysqli database connection
│   ├── auth.php               ← Session management, login, role checks
│   ├── header.php             ← Public navbar + &lt;head&gt;
│   ├── footer.php             ← Scripts + &lt;/body&gt;
│   ├── admin_header.php       ← Admin-only dark navbar
│   ├── admin_footer.php       ← Admin footer
│   ├── preferences_helper.php ← Cookie → DB → default resolution chain
│   ├── dictionary_search.php  ← Shared search library (API + public page)
│   ├── admin_stats_data.php   ← Fetches dashboard stat variables
│   └── admin_stats_cards.php  ← Renders stat card HTML
│
├── pages/                     ← One PHP file per page
│   ├── home.php               ← Landing page
│   ├── search.php             ← Public search with autocomplete
│   ├── catalog.php            ← Word catalog (under construction)
│   ├── preferences.php        ← User preferences panel
│   ├── help.php               ← End-user documentation
│   ├── login.php              ← Login form
│   ├── logout.php             ← Clears session
│   ├── register.php           ← User registration
│   ├── admin_dashboard.php    ← Admin home
│   ├── admin_dictionaries.php ← Dictionary CRUD
│   ├── admin_entries.php      ← Entry CRUD
│   ├── admin_import.php       ← Bulk import form
│   ├── admin_compare.php      ← Dictionary comparison
│   ├── admin_integrity.php    ← Data integrity checks
│   ├── admin_reports.php      ← Charts and statistics
│   ├── admin_docs.php         ← This page
│   ├── admin_crud_api.php     ← JSON API for CRUD operations
│   ├── datatables_ajax.php    ← Server-side DataTables endpoint
│   ├── entry_datatables_ajax.php ← Entry-specific DataTables
│   ├── import.php             ← POST handler for bulk import
│   ├── upload.php             ← Export handler (CSV/JSON/HTML)
│   ├── api_search.php         ← Proxies api/search.php through router
│   └── api_autocomplete.php   ← Autocomplete suggestions endpoint
│
├── assets/
│   ├── css/
│   │   ├── style.css          ← Public site styles
│   │   └── admin.css          ← Admin panel styles
│   ├── js/
│   │   ├── theme.js           ← Dark/light mode toggle
│   │   └── main.js            ← Floating words + hero search
│   └── JS/
│       ├── search-autocomplete.js ← Autocomplete dropdown logic
│       └── admin_crud.js      ← Dictionary/entry CRUD via Ajax
│
├── uploads/                   ← Temp files during import (auto-created)
└── vendor/                    ← Composer packages (never commit to Git)</pre>
          </div>
        </div>

        <!-- Local Setup -->
        <div class="admin-card mb-4" id="setup-local">
          <div class="admin-card-header"><h5>💻 Local Development Setup (XAMPP)</h5></div>
          <div class="admin-card-body">

            <h6 class="fw-bold mt-2">Prerequisites</h6>
            <ul>
              <li>XAMPP with PHP 8.2 and MySQL 8.0</li>
              <li>Composer (download from <a href="https://getcomposer.org" target="_blank">getcomposer.org</a>)</li>
              <li>Git</li>
            </ul>

            <h6 class="fw-bold mt-3">Step 1 — Clone the repository</h6>
            <pre class="dev-pre">cd C:\xampp\htdocs
git clone https://github.com/your-team/IndicLex_b.git
cd IndicLex_b</pre>

            <h6 class="fw-bold mt-3">Step 2 — Install PHP dependencies</h6>
            <pre class="dev-pre">composer install --ignore-platform-reqs</pre>
            <p class="text-muted small">Or double-click <code>setup.bat</code> on Windows.</p>

            <h6 class="fw-bold mt-3">Step 3 — Start XAMPP</h6>
            <p>Open XAMPP Control Panel and start both <strong>Apache</strong> and <strong>MySQL</strong>.</p>

            <h6 class="fw-bold mt-3">Step 4 — Create the database</h6>
            <ol>
              <li>Open <a href="http://localhost/phpmyadmin" target="_blank">localhost/phpmyadmin</a></li>
              <li>Click <strong>New</strong> → Name: <code>indiclex_db</code> → Collation: <code>utf8mb4_unicode_ci</code></li>
              <li>Click the <strong>SQL</strong> tab → paste <code>schema.sql</code> → click <strong>Go</strong></li>
            </ol>

            <h6 class="fw-bold mt-3">Step 5 — Configure database connection</h6>
            <pre class="dev-pre">// includes/db.php
DEFINE('DATABASE_HOST',     'localhost');
DEFINE('DATABASE_DATABASE', 'indiclex_db');
DEFINE('DATABASE_USER',     'root');
DEFINE('DATABASE_PASSWORD', '');       // blank for XAMPP default</pre>

            <h6 class="fw-bold mt-3">Step 6 — Create the admin account</h6>
            <pre class="dev-pre">// Visit in browser:
http://localhost/IndicLex_b/fix_admin.php
// Then delete fix_admin.php immediately after</pre>

            <h6 class="fw-bold mt-3">Step 7 — Import dictionary data</h6>
            <ol>
              <li>Log in at <code>index.php?page=login</code></li>
              <li>Go to <strong>Import</strong> → upload <code>dictionaries_two.xlsx</code></li>
              <li>~8,300 entries will be imported</li>
            </ol>

            <h6 class="fw-bold mt-3">Step 8 — Verify</h6>
            <pre class="dev-pre">// Test database connection:
http://localhost/IndicLex_b/test_db.php
// Delete test_db.php after verifying</pre>

           
          </div>
        </div>

        


            
          </div>
        </div>

        <!-- Database -->
        <div class="admin-card mb-4" id="database">
          <div class="admin-card-header"><h5>🗄️ Database Schema</h5></div>
          <div class="admin-card-body">
            <p>The database uses <strong>utf8mb4_unicode_ci</strong> collation throughout to support Telugu and Hindi scripts correctly.</p>
            <table class="table table-sm table-hover">
              <thead><tr><th>Table</th><th>Key Columns</th><th>Purpose</th></tr></thead>
              <tbody>
                <tr>
                  <td><code>users</code></td>
                  <td>id, username, email, password (bcrypt), role</td>
                  <td>Registered accounts — role is 'admin' or 'user'</td>
                </tr>
                <tr>
                  <td><code>dictionaries</code></td>
                  <td>id, name, source_lang, target_lang, created_by</td>
                  <td>Named dictionary collections</td>
                </tr>
                <tr>
                  <td><code>dictionary_entries</code></td>
                  <td>id, dictionary_id, word, telugu, hindi, transliteration, part_of_speech</td>
                  <td>Individual word entries — UNIQUE on (dictionary_id, word)</td>
                </tr>
                <tr>
                  <td><code>preferences</code></td>
                  <td>id, user_id, theme, font_size, ui_language</td>
                  <td>Per-user saved settings</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- REST API -->
        <div class="admin-card mb-4" id="api">
          <div class="admin-card-header"><h5>🔌 REST API Reference</h5></div>
          <div class="admin-card-body">

            <h6 class="fw-bold">GET /api/search</h6>
            <p>Public search endpoint. No authentication required.</p>
            <table class="table table-sm table-hover">
              <thead><tr><th>Parameter</th><th>Required</th><th>Values</th><th>Default</th></tr></thead>
              <tbody>
                <tr><td><code>q</code></td><td>✅ Yes</td><td>Any search string</td><td>—</td></tr>
                <tr><td><code>dict</code></td><td>No</td><td>Dictionary ID or <code>all</code></td><td>all</td></tr>
                <tr><td><code>mode</code></td><td>No</td><td>exact, prefix, suffix, substring</td><td>substring</td></tr>
                <tr><td><code>limit</code></td><td>No</td><td>1–100</td><td>50</td></tr>
                <tr><td><code>offset</code></td><td>No</td><td>0+</td><td>0</td></tr>
              </tbody>
            </table>

            <h6 class="fw-bold mt-3">HTTP Status Codes</h6>
            <table class="table table-sm table-hover">
              <thead><tr><th>Code</th><th>Meaning</th></tr></thead>
              <tbody>
                <tr><td><span class="badge bg-success">200</span></td><td>Results found and returned</td></tr>
                <tr><td><span class="badge bg-warning text-dark">400</span></td><td>Bad request — missing or invalid parameter</td></tr>
                <tr><td><span class="badge bg-warning text-dark">404</span></td><td>No results found or dictionary not found</td></tr>
                <tr><td><span class="badge bg-danger">405</span></td><td>Method not allowed — use GET</td></tr>
                <tr><td><span class="badge bg-danger">500</span></td><td>Internal server error</td></tr>
              </tbody>
            </table>

            <h6 class="fw-bold mt-3">Example request</h6>
            <pre class="dev-pre">GET /api/search?q=water&mode=substring&dict=all&limit=10</pre>

            
          </div>
        </div>

        <!-- Admin System -->
        <div class="admin-card mb-4" id="admin">
          <div class="admin-card-header"><h5>🛡️ Admin System</h5></div>
          <div class="admin-card-body">
            <p>Admin pages are protected by role-based access control. The route protection works as follows:</p>
            <pre class="dev-pre">// index.php
$admin_pages = ['admin_dashboard', 'admin_import', ...];
if (in_array($page, $admin_pages)) {
    require_admin();   // redirects to login if not admin
    require 'includes/admin_header.php';
    require "pages/{$page}.php";
    require 'includes/admin_footer.php';
    exit;
}</pre>

            <h6 class="fw-bold mt-3">To add a new admin page:</h6>
            <ol>
              <li>Create <code>pages/admin_yourpage.php</code></li>
              <li>Add <code>'admin_yourpage'</code> to <code>$admin_pages</code> in <code>index.php</code></li>
              <li>Add a link in <code>includes/admin_header.php</code></li>
              <li>Do <strong>not</strong> call <code>require_admin()</code> inside the page file — <code>index.php</code> handles it</li>
            </ol>

            <h6 class="fw-bold mt-3">To create an admin account:</h6>
            <pre class="dev-pre">// Upload fix_admin.php to project root, visit it, then delete it
// Or run this SQL in phpMyAdmin:
UPDATE users SET role = 'admin' WHERE username = 'yourusername';</pre>
          </div>
        </div>

        <!-- Git Workflow -->
        <div class="admin-card mb-4" id="git">
          <div class="admin-card-header"><h5>🔀 Git Workflow</h5></div>
          <div class="admin-card-body">
            <h6 class="fw-bold">Never commit these files:</h6>
            <pre class="dev-pre">vendor/          # Composer packages — run composer install locally
uploads/         # Uploaded files
test_db.php      # Exposes database info
fix_admin.php    # Modifies database directly
*.log            # Server logs</pre>

            <h6 class="fw-bold mt-3">Daily workflow:</h6>
            <pre class="dev-pre">git pull origin main          # get latest changes
# make your changes
git add .
git commit -m "describe what you changed"
git push origin main</pre>

            <h6 class="fw-bold mt-3">After a teammate pushes changes:</h6>
            <pre class="dev-pre">git pull origin main
# If composer.json changed:
composer install --ignore-platform-reqs</pre>

            <div class="alert alert-warning mt-3">
              ⚠️ If you get a merge conflict in <code>db.php</code>, keep your own credentials — do not overwrite them with a teammate's local database settings.
            </div>
          </div>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <div style="position:sticky; top:80px;">

          <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>📋 Quick Reference</h5></div>
            <div class="admin-card-body p-0">
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Local URL</strong><br><code>localhost/IndicLex_b</code></li>
                <li class="list-group-item"><strong>Database</strong><br><code>indiclex_db</code></li>
                <li class="list-group-item"><strong>PHP version</strong><br>8.2+</li>
                <li class="list-group-item"><strong>Charset</strong><br>utf8mb4_unicode_ci</li>
                <li class="list-group-item"><strong>Admin login</strong><br><code>index.php?page=login</code></li>
                <li class="list-group-item"><strong>API endpoint</strong><br><code>/api/search?q=...</code></li>
              </ul>
            </div>
          </div>

          <div class="admin-card mb-3">
            <div class="admin-card-header"><h5>🔗 Admin Pages</h5></div>
            <div class="admin-card-body p-0">
              <ul class="list-group list-group-flush" style="font-size:0.85rem;">
                <li class="list-group-item"><a href="index.php?page=admin_dashboard">📊 Dashboard</a></li>
                <li class="list-group-item"><a href="index.php?page=admin_dictionaries">📚 Dictionaries</a></li>
                <li class="list-group-item"><a href="index.php?page=admin_entries">📝 Entries</a></li>
                <li class="list-group-item"><a href="index.php?page=admin_import">📥 Import</a></li>
                <li class="list-group-item"><a href="index.php?page=admin_compare">⇄ Compare</a></li>
                <li class="list-group-item"><a href="index.php?page=admin_integrity">🔍 Integrity</a></li>
                <li class="list-group-item"><a href="index.php?page=admin_reports">📈 Reports</a></li>
              </ul>
            </div>
          </div>

          <div class="admin-card">
            <div class="admin-card-header"><h5>⚡ Common Fixes</h5></div>
            <div class="admin-card-body" style="font-size:0.85rem;">
              <p><strong>MySQL won't start</strong><br>Check port 3306 isn't in use. In XAMPP → Config → my.ini, try changing port to 3307.</p>
              <p><strong>vendor/ missing</strong><br>Run <code>composer install --ignore-platform-reqs</code></p>
              <p><strong>Telugu not displaying</strong><br>Ensure DB charset is utf8mb4 and <code>$db->set_charset('utf8mb4')</code> is called in db.php.</p>
              <p><strong>Session keeps expiring</strong><br>Check <code>session_start()</code> is only called once — at the top of index.php.</p>
              <p class="mb-0"><strong>Import fails on Bluehost</strong><br>Check upload_max_filesize in .htaccess is at least 20M.</p>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>