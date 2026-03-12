<?php
// admin/manage-appointments.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $appt_id = (int)$_POST['appointment_id'];
    $status = $_POST['status'];
    
    if (in_array($status, ['Approved', 'Cancelled'])) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $appt_id])) {
            $success = "Appointment status updated to " . htmlspecialchars($status) . ".";
        } else {
            $error = "Failed to update status.";
        }
    }
}

include '../includes/header.php';

// Fetch all appointments
$appointments = $pdo->query("
    SELECT a.*, u.name as patient_name, d.name as doctor_name, c.name as category_name
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN categories c ON d.category_id = c.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetchAll();

?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white; margin: 0;">Manage Appointments</h2>
</div>

<?php if ($success): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => { showSuccess('Success', '<?php echo addslashes($success); ?>'); });
    </script>
<?php endif; ?>

<?php if ($error): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => { showError('Error', '<?php echo addslashes($error); ?>'); });
    </script>
<?php endif; ?>

<div class="global-glass-container fade-in-up delay-100">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Query</th>
                    <th>Report</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($appointments) > 0): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></strong><br>
                                <small class="text-gray"><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                            <td>
                                <div style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: help;" title="<?php echo htmlspecialchars($appt['patient_query']); ?>">
                                    <?php echo htmlspecialchars($appt['patient_query'] ?: '-'); ?>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: help;" title="<?php echo htmlspecialchars($appt['doctor_report']); ?>">
                                    <?php echo htmlspecialchars($appt['doctor_report'] ?: '-'); ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = 'badge-pending';
                                    if ($appt['status'] === 'Approved') $statusClass = 'badge-approved';
                                    if ($appt['status'] === 'Cancelled') $statusClass = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($appt['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($appt['status'] === 'Pending'): ?>
                                    <form method="POST" action="" style="display: inline-block;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                        <input type="hidden" name="status" value="Approved">
                                        <button type="button" class="glass-btn" style="background: rgba(16, 185, 129, 0.4); border-color: rgba(16, 185, 129, 0.6); padding: 0.3rem 0.6rem; font-size: 0.8rem; height: auto;" onclick="confirmAction('Approve Appointment', 'Are you sure you want to approve this?', 'Yes, Approve', () => { this.closest('form').submit(); })"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    
                                    <form method="POST" action="" style="display: inline-block;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="button" class="glass-btn glass-btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; height: auto;" onclick="confirmAction('Cancel Appointment', 'Are you sure you want to cancel this?', 'Yes, Cancel', () => { this.closest('form').submit(); })"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: rgba(255,255,255,0.7); font-size: 0.85rem;">Action Taken</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center" style="color: rgba(255,255,255,0.7);">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
