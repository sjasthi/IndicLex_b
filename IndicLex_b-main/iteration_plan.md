# ICS 325: Web Application Development

## IndicLex — Multilingual Dictionary Management & Search Platform

### Iteration / Sprint Plan (10 Iterations)

---

## Iteration Requirements

To earn credit for each iteration, **both** of the following conditions must be met:

1. **Weekly Project Signoff Sheet:** You must submit a completed weekly project signoff sheet documenting the work accomplished during that iteration.
2. **Team Breakout Room Demonstration:** You must give a live demonstration of your work in the team breakout room.

> **Important:** Only when both conditions are met will you earn credit for that iteration. A missing signoff sheet or a skipped demonstration means no credit, regardless of the work completed.

---

## Iteration 1 — Project Setup & Database Foundation

Set up the development environment, GitHub repository, and Bluehost hosting. Design and implement the normalized MySQL schema (dictionaries, dictionary_entries, users, preferences tables). Establish the database connection layer in PHP. Deploy the bare-bones project structure to Bluehost and confirm connectivity. By the end of this iteration, the team should have a working database, a live Bluehost URL, and a clean project skeleton.

---

## Iteration 2 — Public UI Shell & Navigation

Build the Bootstrap-based responsive layout including the navbar, footer, and page routing. Create the Home Page with a hero section, the Dictionary Catalog page (pulling dictionary metadata from the database), and placeholder pages for Search and Preferences. Implement the light/dark theme toggle using cookies. Deploy to Bluehost and demo the navigable front-end.

---

## Iteration 3 — Bulk Import/Export & File Handling

Implement bulk dictionary upload from Excel files (using a PHP library like PhpSpreadsheet) with validation and error reporting. Build export functionality for CSV, JSON, and HTML formats. Handle edge cases: duplicate detection during import, malformed files, and large file uploads. Verify file upload permissions and size limits on Bluehost. By the end of this iteration, teams should have real multilingual dictionary data loaded into their databases, which makes all subsequent search and reporting work far more meaningful and testable.

---

## Iteration 4 — Search Engine (Core)

Implement the Search Interface page with a form that lets users select a dictionary (or all), choose a search mode (exact, prefix, suffix, substring), and enter a query. Build the PHP backend search logic with parameterized MySQL queries for each mode. Display results on the Search Results page with pagination. With real dictionary data already loaded from Iteration 3, students can immediately validate their search logic against actual entries rather than hand-entered test data.

---

## Iteration 5 — Preferences & Cookie Management

Build the Preferences Panel (default dictionary, results per page, theme). Implement the full preference resolution chain: check cookies first, then fall back to system defaults from the preferences table. Ensure cookies are read on page load and applied before rendering. Integrate results-per-page into the search pagination built in Iteration 4.

---

## Iteration 6 — Admin Authentication & Dashboard

Create the admin login system with secure password hashing and session management. Build the Admin Dashboard showing summary statistics: total dictionaries, word counts per dictionary, total words, and language-wise breakdowns. Add role-based route protection so admin pages are inaccessible to public users. Integrate jQuery DataTables for tabular admin views. The dashboard statistics will be immediately impressive since real data has been in the system since Iteration 3.

---

## Iteration 7 — Dictionary & Entry CRUD

Build the Dictionary Manager with full CRUD: create, read, update, and delete dictionaries. Build the Entry Manager with CRUD for individual word entries within a dictionary. Implement inline editing or modal-based forms. All operations should refresh the dashboard statistics. Test thoroughly on Bluehost to catch any file-path or permission issues early.

---

## Iteration 8 — REST API & Advanced Search

Build the REST API endpoint (GET /api/search) with support for the q, dict, and mode parameters. Implement proper HTTP status codes (200, 400, 404, 500) and JSON responses. Add autocomplete search on the public search page using jQuery AJAX calls. Integrate the Word Length Matching feature linking to the external telugupuzzles resource. Configure .htaccess for clean API routing on Bluehost.

---

## Iteration 9 — Comparison, Data Integrity & Reporting

Build the Dictionary Comparison page: select two dictionaries and display shared entries, unique entries, and overlapping translations side by side. Build the Data Integrity & Validation page: select a dictionary and run duplicate detection and missing-entry analysis against other dictionaries. Create the Reports page with visualizations (bar charts and pie charts using a library like Chart.js) for dictionary statistics. This iteration rounds out all remaining functional requirements.

---

## Iteration 10 — Stabilization, Polish & Demonstration

Freeze new feature development. Conduct end-to-end testing of every feature on the live Bluehost deployment. Fix bugs, improve error handling, and optimize performance (query indexing, caching where appropriate). Polish the UI for consistency, responsiveness, and accessibility. Finalize technical documentation and ensure the GitHub repo is clean with a proper README. Each team member completes their self-evaluation spreadsheet. Prepare and rehearse the final demonstration from the live Bluehost URL.

---

## Summary

| Iteration | Title | Key Deliverables |
|:---------:|-------|------------------|
| 1 | Project Setup & Database Foundation | Working DB, live Bluehost URL, project skeleton |
| 2 | Public UI Shell & Navigation | Responsive layout, home page, catalog, theme toggle |
| 3 | Bulk Import/Export & File Handling | Excel upload, CSV/JSON/HTML export, real data loaded |
| 4 | Search Engine (Core) | Search interface, 4 search modes, paginated results |
| 5 | Preferences & Cookie Management | Preferences panel, cookie chain, system defaults |
| 6 | Admin Authentication & Dashboard | Login system, admin dashboard, role protection, DataTables |
| 7 | Dictionary & Entry CRUD | Dictionary CRUD, entry CRUD, modal forms |
| 8 | REST API & Advanced Search | REST API endpoint, autocomplete, Word Length Matching |
| 9 | Comparison, Data Integrity & Reporting | Comparison tool, integrity checks, Chart.js reports |
| 10 | Stabilization, Polish & Demonstration | Bug fixes, documentation, final demo from Bluehost |
