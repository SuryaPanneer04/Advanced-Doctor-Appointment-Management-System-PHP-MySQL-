<?php
// admin/dashboard.php
require_once '../includes/db.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';

// Fetch stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_doctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pending_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$total_reviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

// Fetch 5 recent reviews
$recent_reviews_stmt = $pdo->query("
    SELECT r.id, r.rating, r.comment, r.created_at, 
           u.name as patient_name, 
           d.name as doctor_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN doctors d ON r.doctor_id = d.id 
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recent_reviews = $recent_reviews_stmt->fetchAll();
?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white; margin: 0;">Admin Dashboard</h2>
</div>

<div class="dashboard-stats fade-in-up delay-100">
    <div class="global-glass-card" style="display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon" style="color: #60a5fa; font-size: 2.5rem;"><i class="fa-solid fa-users"></i></div>
        <div class="stat-details">
            <h3 style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin: 0 0 0.5rem 0;">Total Patients</h3>
            <p style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;"><?php echo $total_users; ?></p>
        </div>
    </div>
    
    <div class="global-glass-card" style="display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon" style="color: #34d399; font-size: 2.5rem;"><i class="fa-solid fa-user-doctor"></i></div>
        <div class="stat-details">
            <h3 style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin: 0 0 0.5rem 0;">Registered Doctors</h3>
            <p style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;"><?php echo $total_doctors; ?></p>
        </div>
    </div>
    
    <div class="global-glass-card" style="display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon" style="color: #a78bfa; font-size: 2.5rem;"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-details">
            <h3 style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin: 0 0 0.5rem 0;">Total Appointments</h3>
            <p style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;"><?php echo $total_appointments; ?></p>
        </div>
    </div>
    
    <div class="global-glass-card" style="display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon" style="color: #f87171; font-size: 2.5rem;"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-details">
            <h3 style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin: 0 0 0.5rem 0;">Pending Requests</h3>
            <p style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;"><?php echo $pending_appointments; ?></p>
        </div>
    </div>
    
    <div class="global-glass-card" style="display: flex; align-items: center; gap: 1rem;">
        <div class="stat-icon" style="color: #fbbf24; font-size: 2.5rem;"><i class="fa-solid fa-star"></i></div>
        <div class="stat-details">
            <h3 style="color: rgba(255,255,255,0.8); font-size: 0.9rem; margin: 0 0 0.5rem 0;">Total Reviews</h3>
            <p style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;"><?php echo $total_reviews; ?></p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-top: 2rem;">
    <!-- Recent Reviews Panel -->
    <div class="global-glass-container fade-in-up delay-200" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin-bottom: 0; color: white;">Recent Patient Reviews</h3>
            <a href="manage-reviews.php" class="glass-btn" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 8px;">View All</a>
        </div>
        
        <div class="table-responsive">
            <table style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($recent_reviews) > 0): ?>
                        <?php foreach ($recent_reviews as $rev): ?>
                            <tr>
                                <td><span style="font-size: 0.85rem; color: rgba(255,255,255,0.7);"><?php echo date('M j, Y', strtotime($rev['created_at'])); ?></span></td>
                                <td><?php echo htmlspecialchars($rev['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($rev['doctor_name']); ?></td>
                                <td class="text-warning" style="text-shadow: 0 0 5px rgba(251, 191, 36, 0.5);">
                                    <?php
                                    for($i=1; $i<=5; $i++) {
                                        echo ($i <= $rev['rating']) ? '<i class="fa-solid fa-star" style="font-size:0.85rem;"></i>' : '<i class="fa-regular fa-star" style="font-size:0.85rem;"></i>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center" style="padding: 1rem; color: rgba(255,255,255,0.7);">No recent reviews.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="global-glass-container fade-in-up delay-300" style="margin-top: 2rem;">
    <h3 style="text-align: left; color: white;">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h3>
    <p style="color: rgba(255,255,255,0.8);">Use the navigation menu above to manage system entities.</p>
</div>

<?php include '../includes/footer.php'; ?>
