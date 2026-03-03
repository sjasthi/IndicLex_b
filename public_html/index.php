<?php
$page = $_GET['page'] ?? 'home';
?>

<?php include "includes/header.php"; ?>

<div class="container py-5">
<?php
switch($page){
    case "catalog":
        include "pages/catalog.php";
        break;
    case "search":
        include "pages/search.php";
        break;
    case "preferences":
        include "pages/preferences.php";
        break;
    default:
        include "pages/home.php";
}
?>
</div>

<?php include "includes/footer.php"; ?>