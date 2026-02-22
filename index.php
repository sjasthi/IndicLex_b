<?php 
require_once 'config/db.php'; 
include 'includes/header.php'; 
?>

<div class="container mt-5">
    <h1>Welcome to IndicLex</h1>
    <p>Database Status: 
        <?php echo $pdo ? '<span class="text-success">Connected</span>' : '<span class="text-danger">Failed</span>'; ?>
    </p>
</div>

<?php include 'includes/footer.php'; ?>