# ICS325 — Capstone Project
## IndicLex — Multilingual Dictionary Management & Search Platform

### **Course:** ICS325 — Web Application Development

### **Project Name:** IndicLex — Multilingual Dictionary Management & Search Platform

---

## 1. Project Overview

IndicLex is a full-stack web application designed to manage, analyze, and search across multiple multilingual dictionaries. The project simulates a real-world enterprise-level data-driven application, allowing students to practice modern web application development using PHP, MySQL, JavaScript, jQuery, Bootstrap, HTML, and CSS.

Students will design, implement, and deploy a system that supports:

- Multi-language dictionaries
- Advanced search functionality
- Admin dashboards
- Reporting & analytics
- Responsive user interface
- User preferences with cookie-based persistence
- Dictionary comparison and data integrity tools
- REST API for dictionary search

---

## 2. Technology Stack

**Frontend:**
- HTML5
- CSS3
- JavaScript
- jQuery
- Bootstrap

**Backend:**
- PHP
- REST API (PHP-based)

**Database:**
- MySQL

**Hosting & Deployment:**
- Bluehost (required)

---

## 3. Functional Requirements

### 3.1 User Roles

#### A. Visitor (Public User)
- View list of dictionaries and metadata
- Perform searches:
  - Within a selected dictionary
  - Across all dictionaries
- Search patterns:
  - Exact match
  - Prefix
  - Suffix
  - Substring
- View search results with translations
- Set personal preferences (saved via cookies):
  - Default dictionary
  - Results per page
  - Theme (light/dark)

#### B. Admin User
- Secure login system
- Dictionary management:
  - Upload new dictionaries
  - Create dictionaries manually
  - Update dictionary metadata
  - Delete dictionaries
- Word entry management:
  - Add entries
  - Edit entries
  - Delete entries
  - Bulk import & export
- Dictionary comparison:
  - Select two dictionaries for side-by-side comparison
  - View shared entries, unique entries, and overlapping translations
- Data integrity & validation:
  - Select a dictionary and run integrity checks
  - Identify duplicate entries within a dictionary
  - Identify potentially missing entries by comparing against other dictionaries
- Reporting dashboard:
  - Total number of dictionaries
  - Word count per dictionary
  - Total word count across all dictionaries
  - Language-wise statistics
- Export functionality:
  - CSV
  - JSON
  - Excel (optional)

---

## 4. Database Design Requirements

Students must design normalized MySQL tables.

**Core Tables:**
1. dictionaries
2. dictionary_entries
3. users
4. preferences

### 4.1 Preferences Table

The `preferences` table stores system-level default preferences. These defaults apply to all users unless overridden.

**System-level preferences include:**
- Default dictionary (the dictionary pre-selected on the search page)
- Results per page (default number of search results displayed)
- Theme (light or dark mode)

**Preference resolution order (highest to lowest priority):**
1. **User cookie** — If a user has set a preference via the UI, it is stored in a browser cookie and takes precedence.
2. **System default** — If no cookie is present, the system default from the `preferences` table is used.

The application must read cookies on page load and apply them before rendering. If no cookie exists for a given preference, the system default is fetched from the database and applied.

---

## 5. Search Features

- Exact word match
- Prefix matching
- Suffix matching
- Substring matching
- Search within a dictionary
- Search across all dictionaries
- Word Length Matching  (Integration with http://ananya.telugupuzzles.com)

---

## 6. REST API

The application must expose a simple REST API endpoint for dictionary search. This allows external tools or future front-end clients to query the dictionary data programmatically.

### 6.1 Endpoint

**GET** `/api/search`

### 6.2 Query Parameters

| Parameter | Required | Description |
|-----------|----------|-------------|
| `q`       | Yes      | The search term |
| `dict`    | No       | Dictionary identifier (e.g., `telugu-english`). If omitted, searches across all dictionaries. |
| `mode`    | No       | Search mode: `exact`, `prefix`, `suffix`, `substring`. Defaults to `exact`. |

### 6.3 Example Requests

```
GET /api/search?q=namaskar&dict=telugu-english
GET /api/search?q=pra&mode=prefix
GET /api/search?q=tion&dict=sanskrit-english&mode=suffix
```

### 6.4 Response Format

The API must return results in JSON format:

```json
{
  "status": "success",
  "query": "namaskar",
  "dictionary": "telugu-english",
  "mode": "exact",
  "count": 2,
  "results": [
    {
      "word": "namaskar",
      "translation": "greeting",
      "dictionary": "telugu-english"
    }
  ]
}
```

### 6.5 Error Handling

The API must return appropriate HTTP status codes and JSON error messages:
- `200` — Success
- `400` — Missing or invalid parameters
- `404` — No results found
- `500` — Server error

---

## 7. UI / UX Requirements

- Fully responsive design
- Bootstrap-based UI
- Clean layouts
- Support for JQuery datatables (for the admin)
- Intuitive navigation
- Mobile & desktop support
- Light/dark theme toggle (persisted via cookies)

**Required Pages:**

**Public:**
- Home Page with Hero section
- Dictionary Catalog
- Search Interface
- Search Results
- Preferences Panel (accessible from navigation; allows users to set default dictionary, results per page, and theme)

**Admin:**
- Login
- Dashboard
- Dictionary Manager
- CRUD support for dictionary entries
- CRUD support for Dictionaries
- Uploading a dictionary (excel file)
- Exporting of a dictionary (into HTML)
- Entry Manager
- Dictionary Comparison Page (select two dictionaries, view side-by-side analysis)
- Data Integrity & Validation Page (select a dictionary, view duplicate and missing entry reports)
- Reports Page
- Import / Export Page

---

## 8. Advanced Features

- Autocomplete search
- Visualizations (Bar Charts, Pie Charts)
- Role-based authorization
- Performance optimization
- Bulk uploads with validation
- REST API for external search access
- Cookie-based preference management with system default fallback
- Cross-dictionary comparison and gap analysis
- Data integrity checks with duplicate detection

---

## 9. Deployment Requirement (Bluehost)

The web application **must be deployed to Bluehost** and **must be fully functional** in the hosted environment. Local development is expected during the build process, but all demonstrations and evaluations will be conducted from the live, deployed application.

**Requirements:**

- The application must be hosted on Bluehost and accessible via a live URL
- All features — including search, admin dashboard, REST API, database operations, file uploads, and cookie-based preferences — must work correctly on the deployed site
- Students are responsible for configuring Bluehost hosting, including PHP settings, MySQL database setup, file permissions, and any necessary `.htaccess` configurations
- The deployed application must be kept up to date with the latest working version of the project throughout the semester

**Weekly Demonstrations:**

- All weekly progress demonstrations **must be given from the deployed application on Bluehost** — not from a local development environment (localhost)
- If the application is not deployed or not functional on Bluehost at the time of a scheduled demo, the demonstration will be considered incomplete
- This ensures students gain real-world experience with deployment, environment configuration, and production troubleshooting

**Why this matters:**

- Deployment is a critical professional skill — employers expect developers to ship working software, not just code that runs locally
- Production environments expose issues (file paths, permissions, database connectivity, server configurations) that never surface in local development
- Demonstrating from a live URL mirrors how real software is reviewed by stakeholders and end users

> **Bottom line:** If it's not deployed and working on Bluehost, it's not done.

---

## 10. Deliverables

- Fully functional web application deployed on Bluehost
- Live URL accessible for review
- Source code (GitHub)
- Technical documentation
- Self-Evaluation Spreadsheet (from each team member)

---

## 11. Learning Outcomes

- Full-stack development mastery
- Database schema design
- Secure backend development
- Search algorithm implementation
- Professional UI engineering
- Real-world application experience
- REST API design and implementation
- Client-side state management with cookies
- Data quality and integrity analysis
- Production deployment and hosting configuration
