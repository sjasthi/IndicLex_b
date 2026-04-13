<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

require_once __DIR__ . '/../includes/admin_stats_data.php';

$dictionary_id = isset($_GET['dictionary_id']) ? (int) $_GET['dictionary_id'] : 0;

$dict_list = [];
$r = $db->query('SELECT id, name FROM dictionaries ORDER BY name ASC');
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $dict_list[] = $row;
    }
}

if ($dictionary_id < 1 && count($dict_list) > 0) {
    $dictionary_id = (int) $dict_list[0]['id'];
}

$current_dict_name = '';
foreach ($dict_list as $d) {
    if ((int) $d['id'] === $dictionary_id) {
        $current_dict_name = $d['name'];
        break;
    }
}
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<div class="admin-wrap">
  <div class="container-fluid px-4 py-4">

    <div class="admin-page-header">
      <div>
        <h1 class="admin-title">Entry Manager</h1>
        <p class="admin-subtitle">Add, edit, or delete words inside a dictionary. Headwords must be unique per dictionary.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="index.php?page=admin_dashboard" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
        <a href="index.php?page=admin_dictionaries" class="btn btn-outline-primary btn-sm">📚 Dictionaries</a>
      </div>
    </div>

    <div id="admin-crud-alert" class="alert d-none mb-3" role="alert"></div>

    <?php require __DIR__ . '/../includes/admin_stats_cards.php'; ?>

    <?php if (count($dict_list) === 0): ?>
      <div class="alert alert-warning">No dictionaries yet. <a href="index.php?page=admin_dictionaries">Create a dictionary</a> first.</div>
    <?php else: ?>

    <div class="admin-card mb-3">
      <div class="admin-card-body py-3">
        <form method="get" action="index.php" class="row g-2 align-items-end">
          <input type="hidden" name="page" value="admin_entries">
          <div class="col-md-6 col-lg-4">
            <label class="form-label mb-0" for="dictionary_id_select">Dictionary</label>
            <select class="form-select" id="dictionary_id_select" name="dictionary_id" onchange="this.form.submit()">
              <?php foreach ($dict_list as $d): ?>
                <option value="<?php echo (int) $d['id']; ?>" <?php echo (int) $d['id'] === $dictionary_id ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($d['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#entryModal" id="btnNewEntry">+ New entry</button>
          </div>
        </form>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <h5 class="mb-0">Words in <strong><?php echo htmlspecialchars($current_dict_name ?: '—'); ?></strong></h5>
      </div>
      <div class="admin-card-body">
        <table id="entryTable" class="table table-hover table-sm" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Word</th>
              <th>Telugu</th>
              <th>Hindi</th>
              <th>Transliteration</th>
              <th>Part of speech</th>
              <th></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <?php endif; ?>

  </div>
</div>

<!-- Entry modal -->
<div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="entryModalLabel">Dictionary entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="entryForm">
          <input type="hidden" id="entry_id" name="id" value="">
          <input type="hidden" id="entry_dictionary_id" name="dictionary_id" value="<?php echo (int) $dictionary_id; ?>">

          <div class="mb-3">
            <label class="form-label" for="entry_word">Word <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="entry_word" name="word" required maxlength="255">
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label" for="entry_telugu">Telugu</label>
              <input type="text" class="form-control" id="entry_telugu" name="telugu" maxlength="255">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="entry_hindi">Hindi</label>
              <input type="text" class="form-control" id="entry_hindi" name="hindi" maxlength="255">
            </div>
          </div>
          <div class="mb-3 mt-2">
            <label class="form-label" for="entry_transliteration">Transliteration</label>
            <input type="text" class="form-control" id="entry_transliteration" name="transliteration" maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label" for="entry_pos">Part of speech</label>
            <input type="text" class="form-control" id="entry_pos" name="part_of_speech" maxlength="100">
          </div>
          <div class="mb-3">
            <label class="form-label" for="entry_ex_src">Example (source)</label>
            <textarea class="form-control" id="entry_ex_src" name="example_source" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label" for="entry_ex_tgt">Example (target)</label>
            <textarea class="form-control" id="entry_ex_tgt" name="example_target" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="entrySaveBtn">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="entryDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete entry</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Delete the entry <strong id="entryDelWord"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="entryDeleteConfirm">Delete</button>
      </div>
    </div>
  </div>
</div>

<?php if (count($dict_list) > 0): ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/JS/admin_crud.js"></script>
<script>
(function () {
  var api = 'index.php?page=admin_crud_api';
  var dictId = <?php echo (int) $dictionary_id; ?>;
  var delEntryId = null;
  var entryTable;
  var delModal = new bootstrap.Modal(document.getElementById('entryDeleteModal'));

  function flash(msg, type) {
    showAdminAlert(type || 'success', msg);
    var $a = jQuery('#admin-crud-alert');
    $a.removeClass('d-none');
    if (type === 'success') {
      setTimeout(function () { $a.addClass('d-none'); }, 4000);
    }
  }

  jQuery('#entryModal').on('show.bs.modal', function (e) {
    var t = e.relatedTarget;
    if (t && t.id === 'btnNewEntry') {
      jQuery('#entryModalLabel').text('New entry');
      jQuery('#entry_id').val('');
      jQuery('#entry_word').val('');
      jQuery('#entry_telugu').val('');
      jQuery('#entry_hindi').val('');
      jQuery('#entry_transliteration').val('');
      jQuery('#entry_pos').val('');
      jQuery('#entry_ex_src').val('');
      jQuery('#entry_ex_tgt').val('');
      jQuery('#entry_dictionary_id').val(String(dictId));
    }
  });

  entryTable = jQuery('#entryTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: 'index.php?page=entry_datatables_ajax&dictionary_id=' + dictId,
    columns: [
      { data: 'id', width: '56px' },
      { data: 'word' },
      { data: 'telugu' },
      { data: 'hindi' },
      { data: 'transliteration' },
      { data: 'part_of_speech' },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (_, __, row) {
          return '<button type="button" class="btn btn-sm btn-outline-secondary btn-edit-entry" data-id="' + row.id + '">Edit</button> ' +
            '<button type="button" class="btn btn-sm btn-outline-danger btn-del-entry" data-id="' + row.id + '">Delete</button>';
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 25,
    language: {
      processing: '<div class="spinner-border spinner-border-sm text-secondary" role="status"></div> Loading...'
    }
  });

  jQuery(document).on('click', '.btn-edit-entry', function () {
    var id = jQuery(this).data('id');
    jQuery.getJSON(api, { action: 'entry_get', id: id })
      .done(function (res) {
        if (!res.ok || !res.entry) {
          flash(res.error || 'Load failed.', 'danger');
          return;
        }
        var e = res.entry;
        jQuery('#entryModalLabel').text('Edit entry');
        jQuery('#entry_id').val(e.id);
        jQuery('#entry_dictionary_id').val(e.dictionary_id);
        jQuery('#entry_word').val(e.word);
        jQuery('#entry_telugu').val(e.telugu);
        jQuery('#entry_hindi').val(e.hindi);
        jQuery('#entry_transliteration').val(e.transliteration);
        jQuery('#entry_pos').val(e.part_of_speech);
        jQuery('#entry_ex_src').val(e.example_source);
        jQuery('#entry_ex_tgt').val(e.example_target);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('entryModal')).show();
      })
      .fail(function () {
        flash('Could not load entry.', 'danger');
      });
  });

  jQuery(document).on('click', '.btn-del-entry', function () {
    delEntryId = jQuery(this).data('id');
    var tr = jQuery(this).closest('tr');
    var word = tr.find('td').eq(1).text();
    jQuery('#entryDelWord').text(word);
    delModal.show();
  });

  jQuery('#entrySaveBtn').on('click', function () {
    var id = jQuery('#entry_id').val();
    var payload = {
      dictionary_id: parseInt(jQuery('#entry_dictionary_id').val(), 10),
      word: jQuery('#entry_word').val().trim(),
      telugu: jQuery('#entry_telugu').val(),
      hindi: jQuery('#entry_hindi').val(),
      transliteration: jQuery('#entry_transliteration').val(),
      part_of_speech: jQuery('#entry_pos').val(),
      example_source: jQuery('#entry_ex_src').val(),
      example_target: jQuery('#entry_ex_tgt').val()
    };
    if (!payload.word) {
      flash('Word is required.', 'danger');
      return;
    }
    var action = id ? 'entry_update' : 'entry_create';
    var data = jQuery.extend({ action: action }, payload);
    if (id) data.id = parseInt(id, 10);
    jQuery.post(api, data)
      .done(function (res) {
        if (!res.ok) {
          flash(res.error || 'Save failed.', 'danger');
          return;
        }
        flash(id ? 'Entry updated.' : 'Entry added.', 'success');
        refreshAdminStats();
        var em = document.getElementById('entryModal');
        var mi = bootstrap.Modal.getInstance(em);
        if (mi) { mi.hide(); }
        entryTable.ajax.reload(null, false);
      })
      .fail(function (xhr) {
        var r = xhr.responseJSON;
        flash((r && r.error) ? r.error : 'Request failed.', 'danger');
      });
  });

  jQuery('#entryDeleteConfirm').on('click', function () {
    if (!delEntryId) return;
    jQuery.post(api, { action: 'entry_delete', id: delEntryId })
      .done(function (res) {
        if (!res.ok) {
          flash(res.error || 'Delete failed.', 'danger');
          return;
        }
        flash('Entry deleted.', 'success');
        refreshAdminStats();
        delModal.hide();
        entryTable.ajax.reload(null, false);
      })
      .fail(function (xhr) {
        var r = xhr.responseJSON;
        flash((r && r.error) ? r.error : 'Request failed.', 'danger');
      });
  });
})();
</script>
<?php endif; ?>
