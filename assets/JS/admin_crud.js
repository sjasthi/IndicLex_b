/**
 * Updates stat card numbers from admin_crud_api (matches dashboard queries).
 */
function refreshAdminStats() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    jQuery.getJSON('index.php?page=admin_crud_api', { action: 'stats' })
        .done(function (data) {
            if (!data || !data.ok) {
                return;
            }
            jQuery('#stat-total-words').text(Number(data.total_words).toLocaleString());
            jQuery('#stat-total-dicts').text(Number(data.total_dicts).toLocaleString());
            jQuery('#stat-with-telugu').text(Number(data.with_telugu).toLocaleString());
            jQuery('#stat-total-users').text(Number(data.total_users).toLocaleString());
        });
}

function showAdminAlert(type, message) {
    var cls = type === 'success' ? 'alert-success' : type === 'danger' ? 'alert-danger' : 'alert-info';
    var $box = jQuery('#admin-crud-alert');
    if (!$box.length) {
        return;
    }
    $box.removeClass('d-none alert-success alert-danger alert-info').addClass(cls).text(message);
}
