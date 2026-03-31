<?php
// ============================================================
// includes/preferences_helper.php
// Preference resolution chain:
//   1. Check cookie  (set by user explicitly)
//   2. Check DB      (saved preferences table)
//   3. System default (hardcoded fallback)
// ============================================================

// ── System defaults ──────────────────────────────────────────
define('DEFAULT_THEME',       'light');
define('DEFAULT_RESULTS',     10);
define('DEFAULT_DICT_ID',     1);
define('COOKIE_EXPIRY',       60 * 60 * 24 * 365); // 1 year

// ── Cookie names ─────────────────────────────────────────────
define('COOKIE_THEME',        'pref_theme');
define('COOKIE_RESULTS',      'pref_results_per_page');
define('COOKIE_DICT',         'pref_default_dict');

// ── Valid values ─────────────────────────────────────────────
define('VALID_RESULTS',       [5, 10, 25, 50]);
define('VALID_THEMES',        ['light', 'dark']);

// ============================================================
// get_preference()
// Returns the resolved value for a given preference key.
// Resolution order: cookie → DB row → system default
// ============================================================
function get_preference($key, $db, $user_id = null) {
    // ── 1. Check cookie first ──
    $cookie_val = get_cookie_pref($key);
    if ($cookie_val !== null) return $cookie_val;

    // ── 2. Check database if user_id provided ──
    if ($user_id && $db) {
        $db_val = get_db_pref($key, $db, $user_id);
        if ($db_val !== null) return $db_val;
    }

    // ── 3. Fall back to system default ──
    return get_default_pref($key);
}

// ── Read from cookie ─────────────────────────────────────────
function get_cookie_pref($key) {
    switch ($key) {
        case 'theme':
            $val = $_COOKIE[COOKIE_THEME] ?? null;
            return ($val && in_array($val, VALID_THEMES)) ? $val : null;

        case 'results_per_page':
            $val = isset($_COOKIE[COOKIE_RESULTS]) ? intval($_COOKIE[COOKIE_RESULTS]) : null;
            return ($val && in_array($val, VALID_RESULTS)) ? $val : null;

        case 'default_dict':
            $val = isset($_COOKIE[COOKIE_DICT]) ? intval($_COOKIE[COOKIE_DICT]) : null;
            return ($val && $val > 0) ? $val : null;
    }
    return null;
}

// ── Read from database ────────────────────────────────────────
function get_db_pref($key, $db, $user_id) {
    $stmt = $db->prepare("SELECT theme, font_size, ui_language FROM preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) return null;

    switch ($key) {
        case 'theme':
            return in_array($row['theme'], VALID_THEMES) ? $row['theme'] : null;
    }
    return null;
}

// ── System defaults ───────────────────────────────────────────
function get_default_pref($key) {
    return match($key) {
        'theme'            => DEFAULT_THEME,
        'results_per_page' => DEFAULT_RESULTS,
        'default_dict'     => DEFAULT_DICT_ID,
        default            => null,
    };
}

// ============================================================
// save_preference()
// Saves a preference to both cookie and DB (if user logged in)
// ============================================================
function save_preference($key, $value, $db, $user_id = null) {
    $expiry = time() + COOKIE_EXPIRY;

    switch ($key) {
        case 'theme':
            if (!in_array($value, VALID_THEMES)) return false;
            setcookie(COOKIE_THEME, $value, $expiry, '/');
            $_COOKIE[COOKIE_THEME] = $value; // apply immediately this request
            break;

        case 'results_per_page':
            $value = intval($value);
            if (!in_array($value, VALID_RESULTS)) return false;
            setcookie(COOKIE_RESULTS, $value, $expiry, '/');
            $_COOKIE[COOKIE_RESULTS] = $value;
            break;

        case 'default_dict':
            $value = intval($value);
            if ($value <= 0) return false;
            setcookie(COOKIE_DICT, $value, $expiry, '/');
            $_COOKIE[COOKIE_DICT] = $value;
            break;

        default:
            return false;
    }

    // Also save to DB if user is logged in
    if ($user_id && $db && $key === 'theme') {
        $stmt = $db->prepare("
            INSERT INTO preferences (user_id, theme)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE theme = VALUES(theme)
        ");
        $stmt->bind_param("is", $user_id, $value);
        $stmt->execute();
        $stmt->close();
    }

    return true;
}

// ============================================================
// load_all_preferences()
// Returns all preferences resolved for the current request
// ============================================================
function load_all_preferences($db, $user_id = null) {
    return [
        'theme'            => get_preference('theme',            $db, $user_id),
        'results_per_page' => get_preference('results_per_page', $db, $user_id),
        'default_dict'     => get_preference('default_dict',     $db, $user_id),
    ];
}