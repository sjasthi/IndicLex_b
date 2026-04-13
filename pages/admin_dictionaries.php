<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

require_once __DIR__ . '/../includes/admin_stats_data.php';

$dict_rows = [];
$r = $db->query("
    SELECT d.id, d.name, d.description, d.source_lang, d.target_lang, d.created_at,
           COUNT(de.id) AS word_count
    FROM dictionaries d
    LEFT JOIN dictionary_entries de ON de.dictionary_id = d.id
    GROUP BY d.id
    ORDER BY d.name ASC
");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $dict_rows[] = $row;
    }
}
?>

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Dictionary Manager</h1>
        <p class="admin-subtitle">Create, edit, or remove dictionaries. Deleting a dictionary removes all of its entries.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="index.php?page=admin_dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
        <a href="index.php?page=admin_entries" class="btn btn-outline-primary btn-sm">📝 Entries</a>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dictModal" id="btnNewDict">+ New dictionary</button>
      </div>
    </div>

    <div id="admin-crud-alert" class="alert d-none mb-3" role="alert"></div>

    <?php require __DIR__ . '/../includes/admin_stats_cards.php'; ?>

    <div class="admin-card">
      <div class="admin-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📚 Dictionaries</h5>
      </div>
      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Source → Target</th>
                <th>Words</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dict_rows as $d): ?>
                <tr data-id="<?php echo (int) $d['id']; ?>"
                    data-name="<?php echo htmlspecialchars($d['name'], ENT_QUOTES); ?>"
                    data-description="<?php echo htmlspecialchars((string) $d['description'], ENT_QUOTES); ?>"
                    data-source-lang="<?php echo htmlspecialchars($d['source_lang'], ENT_QUOTES); ?>"
                    data-target-lang="<?php echo htmlspecialchars($d['target_lang'], ENT_QUOTES); ?>">
                  <td><?php echo (int) $d['id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($d['name']); ?></strong></td>
                  <td><?php echo htmlspecialchars($d['source_lang']); ?> → <?php echo htmlspecialchars($d['target_lang']); ?></td>
                  <td><span class="admin-word-count"><?php echo number_format((int) $d['word_count']); ?></span></td>
                  <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-primary" href="index.php?page=admin_entries&amp;dictionary_id=<?php echo (int) $d['id']; ?>">Entries</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-dict">Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-del-dict">Delete</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal: create / edit dictionary -->
<div class="modal fade" id="dictModal" tabindex="-1" aria-labelledby="dictModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dictModalLabel">Dictionary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="dictForm">
          <input type="hidden" name="id" id="dict_id" value="">
          <div class="mb-3">
            <label class="form-label" for="dict_name">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="dict_name" name="name" required maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label" for="dict_description">Description</label>
            <textarea class="form-control" id="dict_description" name="description" rows="3"></textarea>
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label" for="dict_source_lang">Source language</label>
              <input type="text" class="form-control" id="dict_source_lang" name="source_lang" value="Telugu" maxlength="50">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="dict_target_lang">Target language</label>
              <input type="text" class="form-control" id="dict_target_lang" name="target_lang" value="English" maxlength="50">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="dictSaveBtn">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete confirm -->
<div class="modal fade" id="dictDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete dictionary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Delete <strong id="dictDelName"></strong> and <strong>all entries</strong> in it? This cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="dictDeleteConfirm">Delete</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/JS/admin_crud.js"></script>
<script>
(function () {
  var api = 'index.php?page=admin_crud_api';
  var delId = null;
  var modal = new bootstrap.Modal(document.getElementById('dictModal'));
  var delModal = new bootstrap.Modal(document.getElementById('dictDeleteModal'));

  function flash(msg, type) {
    showAdminAlert(type || 'success', msg);
    var $a = jQuery('#admin-crud-alert');
    $a.removeClass('d-none');
    if (type === 'success') {
      setTimeout(function () { $a.addClass('d-none'); }, 4000);
    }
  }

  jQuery('#btnNewDict').on('click', function () {
    jQuery('#dictModalLabel').text('New dictionary');
    jQuery('#dict_id').val('');
    jQuery('#dict_name').val('');
    jQuery('#dict_description').val('');
    jQuery('#dict_source_lang').val('Telugu');
    jQuery('#dict_target_lang').val('English');
  });

  jQuery(document).on('click', '.btn-edit-dict', function () {
    var tr = jQuery(this).closest('tr');
    jQuery('#dictModalLabel').text('Edit dictionary');
    jQuery('#dict_id').val(tr.data('id'));
    jQuery('#dict_name').val(tr.data('name'));
    jQuery('#dict_description').val(tr.data('description'));
    jQuery('#dict_source_lang').val(tr.data('source-lang'));
    jQuery('#dict_target_lang').val(tr.data('target-lang'));
    modal.show();
  });

  jQuery(document).on('click', '.btn-del-dict', function () {
    var tr = jQuery(this).closest('tr');
    delId = tr.data('id');
    jQuery('#dictDelName').text(tr.data('name'));
    delModal.show();
  });

  jQuery('#dictSaveBtn').on('click', function () {
    var id = jQuery('#dict_id').val();
    var payload = {
      name: jQuery('#dict_name').val().trim(),
      description: jQuery('#dict_description').val(),
      source_lang: jQuery('#dict_source_lang').val().trim() || 'Telugu',
      target_lang: jQuery('#dict_target_lang').val().trim() || 'English'
    };
    if (!payload.name) {
      flash('Name is required.', 'danger');
      return;
    }
    var action = id ? 'dictionary_update' : 'dictionary_create';
    var data = jQuery.extend({ action: action }, payload);
    if (id) data.id = parseInt(id, 10);
    jQuery.post(api, data)
      .done(function (res) {
        if (!res.ok) {
          flash(res.error || 'Save failed.', 'danger');
          return;
        }
        flash(id ? 'Dictionary updated.' : 'Dictionary created.', 'success');
        refreshAdminStats();
        modal.hide();
        window.location.reload();
      })
      .fail(function (xhr) {
        var r = xhr.responseJSON;
        flash((r && r.error) ? r.error : 'Request failed.', 'danger');
      });
  });

  jQuery('#dictDeleteConfirm').on('click', function () {
    if (!delId) return;
    jQuery.post(api, { action: 'dictionary_delete', id: delId })
      .done(function (res) {
        if (!res.ok) {
          flash(res.error || 'Delete failed.', 'danger');
          return;
        }
        flash('Dictionary deleted.', 'success');
        refreshAdminStats();
        delModal.hide();
        window.location.reload();
      })
      .fail(function (xhr) {
        var r = xhr.responseJSON;
        flash((r && r.error) ? r.error : 'Request failed.', 'danger');
      });
  });
})();
</script>
