<?php
// doctor/logout.php
session_start();
unset($_SESSION['doctor_id']);
unset($_SESSION['doctor_name']);
header("Location: login.php");
exit;
?>
