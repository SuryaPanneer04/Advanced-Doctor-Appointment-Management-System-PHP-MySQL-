<?php
// user/view-category.php
// Redirect to the new advanced Doctor Directory, passing the category_id
session_start();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$category_id = (int)$_GET['id'];
header("Location: doctors-directory.php?category_id=" . $category_id);
exit;
?>
