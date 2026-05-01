/* ============================================================
   assets/JS/admin_crud.js
   Shared admin JavaScript utilities.
   Required by: admin_dashboard.php, admin_dictionaries.php,
                admin_entries.php
   ============================================================ */

// ── API base URL ──────────────────────────────────────────────
var ADMIN_API = 'index.php?page=admin_crud_api';

// ── Show alert banner ─────────────────────────────────────────
function showAdminAlert(type, message) {
    var $alert = jQuery('#admin-crud-alert');
    if ($alert.length === 0) return;

    // Map type to Bootstrap class
    var cls = {
        'success' : 'alert-success',
        'danger'  : 'alert-danger',
        'warning' : 'alert-warning',
        'info'    : 'alert-info',
    }[type] || 'alert-info';

    $alert
        .removeClass('d-none alert-success alert-danger alert-warning alert-info')
        .addClass(cls)
        .html(message);

    // Auto-hide success messages after 4 seconds
    if (type === 'success') {
        setTimeout(function () {
            $alert.addClass('d-none');
        }, 4000);
    }
}

// ── Refresh stat cards on the dashboard ──────────────────────
function refreshAdminStats() {
    jQuery.getJSON(ADMIN_API, { action: 'stats' })
        .done(function (res) {
            if (!res || !res.ok) return;

            // Update each stat card number if it exists on the page
            var map = {
                '.stat-total-words'   : res.total_words,
                '.stat-total-dicts'   : res.total_dicts,
                '.stat-total-users'   : res.total_users,
                '.stat-with-telugu'   : res.with_telugu,
                '.stat-with-hindi'    : res.with_hindi,
            };

            jQuery.each(map, function (selector, value) {
                jQuery(selector).text(
                    typeof value === 'number'
                        ? value.toLocaleString()
                        : value
                );
            });
        })
        .fail(function () {
            // Silently fail — stats refresh is non-critical
        });
}

// ── Generic POST helper ───────────────────────────────────────
function adminPost(data, onSuccess, onError) {
    jQuery.ajax({
        url      : ADMIN_API,
        method   : 'POST',
        data     : data,
        dataType : 'json',
    })
    .done(function (res) {
        if (!res.ok) {
            if (typeof onError === 'function') {
                onError(res.error || 'Request failed.');
            }
            return;
        }
        if (typeof onSuccess === 'function') {
            onSuccess(res);
        }
    })
    .fail(function (xhr) {
        var r   = xhr.responseJSON;
        var msg = (r && r.error) ? r.error : 'Request failed (HTTP ' + xhr.status + ').';
        if (typeof onError === 'function') {
            onError(msg);
        }
    });
}