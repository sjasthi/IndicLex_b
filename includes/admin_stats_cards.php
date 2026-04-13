<?php
/** Expects $total_words, $total_dicts, $with_telugu, $total_users from admin_stats_data.php */
?>
<div class="row g-3 mb-4 admin-stats-cards" id="admin-stats-cards">
  <div class="col-6 col-md-3">
    <div class="admin-stat-card">
      <div class="admin-stat-icon">📚</div>
      <div class="admin-stat-num" id="stat-total-words"><?php echo number_format($total_words); ?></div>
      <div class="admin-stat-label">Total Words</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-stat-card">
      <div class="admin-stat-icon">📖</div>
      <div class="admin-stat-num" id="stat-total-dicts"><?php echo number_format($total_dicts); ?></div>
      <div class="admin-stat-label">Dictionaries</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-stat-card">
      <div class="admin-stat-icon">🇮🇳</div>
      <div class="admin-stat-num" id="stat-with-telugu"><?php echo number_format($with_telugu); ?></div>
      <div class="admin-stat-label">Telugu Entries</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-stat-card">
      <div class="admin-stat-icon">👥</div>
      <div class="admin-stat-num" id="stat-total-users"><?php echo number_format($total_users); ?></div>
      <div class="admin-stat-label">Users</div>
    </div>
  </div>
</div>
