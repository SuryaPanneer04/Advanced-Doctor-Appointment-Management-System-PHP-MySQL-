<?php
// doctor/dashboard.php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Get filter parameters
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$query = "
    SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as patient_phone
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.doctor_id = ? AND a.status = 'Approved'
";
$params = [$doctor_id];

if ($search_name !== '') {
    $query .= " AND u.name LIKE ?";
    $params[] = "%$search_name%";
}
if ($date_from !== '') {
    $query .= " AND a.appointment_date >= ?";
    $params[] = $date_from;
}
if ($date_to !== '') {
    $query .= " AND a.appointment_date <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Get Statistics
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_appointments,
        SUM(CASE WHEN appointment_date = CURDATE() THEN 1 ELSE 0 END) as appointments_today,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_appointments,
        SUM(d.fees) as total_revenue
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.doctor_id = ?
");
$statsStmt->execute([$doctor_id]);
$stats = $statsStmt->fetch();

include '../includes/header.php';
?>

<!-- Analytics Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;" class="fade-in-up">
    <div class="pro-card" style="padding: 1.5rem; border-left: 4px solid var(--primary);">
        <h4 style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.5rem;">Total Appointments</h4>
        <div style="font-size: 1.8rem; font-weight: 700; color: white;"><?php echo $stats['total_appointments']; ?></div>
        <div style="font-size: 0.8rem; color: #6ee7b7; margin-top: 0.4rem;"><i class="fa-solid fa-check-circle"></i> <?php echo $stats['approved_appointments']; ?> Approved</div>
    </div>
    <div class="pro-card" style="padding: 1.5rem; border-left: 4px solid #6ee7b7;">
        <h4 style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.5rem;">Appointments Today</h4>
        <div style="font-size: 1.8rem; font-weight: 700; color: white;"><?php echo $stats['appointments_today']; ?></div>
        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 0.4rem;">Live scheduled slots</div>
    </div>
    <div class="pro-card" style="padding: 1.5rem; border-left: 4px solid #fbbf24;">
        <h4 style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.5rem;">Total Revenue</h4>
        <div style="font-size: 1.8rem; font-weight: 700; color: white;">₹<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 0.4rem;">Based on consultation fees</div>
    </div>
    <div class="pro-card" style="padding: 1.5rem; border-left: 4px solid #fca5a5;">
        <h4 style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.5rem;">Completion Rate</h4>
        <div style="font-size: 1.8rem; font-weight: 700; color: white;">
            <?php 
                $rate = $stats['total_appointments'] > 0 ? ($stats['approved_appointments'] / $stats['total_appointments']) * 100 : 0;
                echo round($rate) . '%';
            ?>
        </div>
        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 0.4rem;">Approved vs Total</div>
    </div>
</div>

<div class="dashboard-header" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <h2 style="color: white; margin: 0;"><i class="fa-solid fa-calendar-check"></i> Patient Management</h2>
        
        <div class="stats-badges" style="display: flex; gap: 1rem;">
            <?php
            $pending_report = count(array_filter($appointments, fn($a) => empty($a['doctor_report'])));
            $done_report = count(array_filter($appointments, fn($a) => !empty($a['doctor_report'])));
            ?>
            <span class="glass-badge" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5;"><?php echo $pending_report; ?> Report Pending</span>
            <span class="glass-badge" style="background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7;"><?php echo $done_report; ?> Analysis Done</span>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="global-glass-container" style="margin-top: 1.5rem; padding: 1.2rem; margin-bottom: 0;">
        <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 0.4rem;">Patient Name</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4);"></i>
                    <input type="text" name="search_name" class="form-control glass-input" placeholder="Search by name..." value="<?php echo htmlspecialchars($search_name); ?>" style="padding-left: 2.5rem;">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 0.4rem;">From Date</label>
                <input type="date" name="date_from" class="form-control glass-input" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 0.4rem;">To Date</label>
                <input type="date" name="date_to" class="form-control glass-input" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="glass-btn btn-primary" style="flex: 1; height: 42px;"><i class="fa-solid fa-filter"></i> Apply Filters</button>
                <a href="dashboard.php" class="glass-btn" style="height: 42px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05);"><i class="fa-solid fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="global-glass-container" style="padding: 0; overflow: hidden;">
    <table class="glass-table">
        <thead>
                <tr>
                <tr>
                    <th>Token #</th>
                    <th>Patient Name</th>
                    <th>Date & Time</th>
                    <th>Patient Query</th>
                    <th>Medical Report</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: rgba(255,255,255,0.5);">No appointments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $app): ?>
                        <tr>
                            <td><div class="glass-badge" style="background: rgba(255,255,255,0.1); font-weight: 700; width: 40px; text-align: center; color: #fbbf24;">#<?php echo $app['token_number']; ?></div></td>
                            <td>
                                <strong style="color: white;"><?php echo htmlspecialchars($app['patient_name']); ?></strong><br>
                                <small style="color: rgba(255,255,255,0.6);"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($app['patient_phone']); ?></small>
                            </td>
                            <td>
                                <div style="color: white; font-weight: 500;"><?php echo date('M d, Y', strtotime($app['appointment_date'])); ?></div>
                                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.7);"><?php echo date('h:i A', strtotime($app['appointment_time'])); ?></div>
                            </td>
                            <td>
                                <div style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: rgba(255,255,255,0.8);" title="<?php echo htmlspecialchars($app['patient_query']); ?>">
                                    <?php echo htmlspecialchars($app['patient_query'] ?: 'None'); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($app['medical_report']): ?>
                                    <a href="../<?php echo $app['medical_report']; ?>" target="_blank" class="glass-btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: rgba(79, 70, 229, 0.2); color: #818cf8; border-color: rgba(79, 70, 229, 0.4);">
                                        <i class="fa-solid fa-file-medical"></i> View Report
                                    </a>
                                <?php else: ?>
                                    <span style="color: rgba(255,255,255,0.3); font-size: 0.85rem;">No file</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($app['doctor_report'])): ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4);">Done</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3);">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view-appointment.php?id=<?php echo $app['id']; ?>" class="glass-btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-notes-medical"></i> <?php echo !empty($app['doctor_report']) ? 'Edit' : 'Treat'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
