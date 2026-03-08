<?php
// includes/header.php
@session_start();
$doc_root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$proj_root = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__))), '/');
$base_url = str_replace($doc_root, '', $proj_root);

// Determine if we are in admin or user area based on URL
$current_uri = $_SERVER['REQUEST_URI'];
$is_admin = strpos($current_uri, '/admin/') !== false;
$is_user = strpos($current_uri, '/user/') !== false;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor & Patient Management System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body class="global-glass-body">

<nav class="navbar">
    <div class="container nav-container">
        <a href="<?php echo $base_url; ?>/index.php" class="brand">
            <i class="fa-solid fa-stethoscope"></i> MedCare
        </a>
        
        <div class="nav-links">
            <?php if ($is_admin): ?>
                <?php if(isset($_SESSION['admin_id'])): ?>
                    <div class="nav-links">
                        <a href="<?php echo $base_url; ?>/admin/dashboard.php">Dashboard</a>
                        <a href="<?php echo $base_url; ?>/admin/manage-categories.php">Categories</a>
                        <a href="<?php echo $base_url; ?>/admin/manage-doctors.php">Doctors</a>
                        <a href="<?php echo $base_url; ?>/admin/manage-users.php">Patients</a>
                        <a href="<?php echo $base_url; ?>/admin/manage-appointments.php">Appointments</a>
                        <a href="<?php echo $base_url; ?>/admin/manage-reviews.php">Reviews</a>
                    </div>
                    <div class="user-menu">
                        <span style="font-weight: 500;">Admin</span>
                        <a href="<?php echo $base_url; ?>/admin/logout.php" class="btn btn-secondary" style="padding: 0.4rem 1rem; border-color: rgba(255,255,255,0.2);">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>/admin/login.php" class="btn btn-primary">Admin Login</a>
                <?php endif; ?>
            <?php else: ?>
                <!-- User Area or Main Area -->
                <a href="<?php echo $base_url; ?>/index.php">Home</a>
                <a href="<?php echo $base_url; ?>/user/doctors-directory.php">Explore Doctors</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $base_url; ?>/user/dashboard.php">My Appointments</a>
                    <a href="<?php echo $base_url; ?>/user/book-appointment.php" class="btn btn-primary">Book Now</a>
                    <a href="<?php echo $base_url; ?>/user/logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>/user/login.php">Login</a>
                    <a href="<?php echo $base_url; ?>/user/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container page-content">
