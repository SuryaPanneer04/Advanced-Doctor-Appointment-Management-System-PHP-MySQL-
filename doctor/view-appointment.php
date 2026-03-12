<?php
// doctor/view-appointment.php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch appointment details
$stmt = $pdo->prepare("
    SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as patient_phone, u.address as patient_address 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = ? AND a.doctor_id = ?
");
$stmt->execute([$appointment_id, $doctor_id]);
$app = $stmt->fetch();

if (!$app) {
    die("Appointment not found or unauthorized access.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $doctor_report = trim($_POST['doctor_report']);

    $stmt = $pdo->prepare("UPDATE appointments SET status = ?, doctor_report = ? WHERE id = ? AND doctor_id = ?");
    if ($stmt->execute([$status, $doctor_report, $appointment_id, $doctor_id])) {
        $success = "Appointment updated successfully.";
        // Refresh appointment data
        $app['status'] = $status;
        $app['doctor_report'] = $doctor_report;
    } else {
        $error = "Failed to update appointment.";
    }
}

include '../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="dashboard.php" style="color: #6ee7b7; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="appointment-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Patient Info -->
    <div class="global-glass-container fade-in-up">
        <h3 style="color: white; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-user"></i> Patient Information
        </h3>
        
        <div style="margin-bottom: 1rem;">
            <label style="color: rgba(255,255,255,0.6); font-size: 0.85rem; display: block;">Full Name</label>
            <div style="color: white; font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($app['patient_name']); ?></div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="color: rgba(255,255,255,0.6); font-size: 0.85rem; display: block;">Email & Phone</label>
            <div style="color: white;"><?php echo htmlspecialchars($app['patient_email']); ?></div>
            <div style="color: white;"><?php echo htmlspecialchars($app['patient_phone']); ?></div>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="color: rgba(255,255,255,0.6); font-size: 0.85rem; display: block;">Address</label>
            <div style="color: white;"><?php echo htmlspecialchars($app['patient_address'] ?: 'Not provided'); ?></div>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 8px; border-left: 4px solid #6ee7b7;">
            <label style="color: #6ee7b7; font-weight: 600; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">PATIENT QUERY / SYMPTOMS</label>
            <div style="color: white; font-style: italic;">
                "<?php echo htmlspecialchars($app['patient_query'] ?: 'No symptoms described.'); ?>"
            </div>
        </div>
    </div>

    <!-- Action Form -->
    <div class="global-glass-container fade-in-up" style="animation-delay: 0.1s;">
        <h3 style="color: white; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-notes-medical"></i> Doctor's Analysis & Patient Report
        </h3>

        <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Scenario Submitted',
                        text: '<?php echo addslashes($success); ?>',
                        confirmButtonText: 'Back to Dashboard',
                        confirmButtonColor: '#10b981'
                    }).then((result) => {
                        window.location.href = 'dashboard.php';
                    });
                });
            </script>
        <?php endif; ?>

        <div style="margin-bottom: 1.5rem; padding: 0.8rem; background: rgba(110, 231, 183, 0.1); border: 1px solid rgba(110, 231, 183, 0.3); border-radius: 8px; color: #6ee7b7; font-size: 0.9rem;">
            <i class="fa-solid fa-info-circle"></i> This appointment was <strong>Approved by Admin</strong>. Please provide your medical analysis for the patient.
        </div>

        <form method="POST" action="">
            <input type="hidden" name="status" value="Approved"> <!-- Keep it approved -->
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="doctor_report" style="color: rgba(255,255,255,0.9);">Patient Diagnosis & Scenario Analysis</label>
                <textarea name="doctor_report" id="doctor_report" class="form-control glass-input" rows="10" placeholder="Type your patient scenario checkup analysis here..." required><?php echo htmlspecialchars($app['doctor_report']); ?></textarea>
                <small style="color: rgba(255,255,255,0.5); display: block; margin-top: 0.5rem;">This "scenario" will be visible to the patient on their dashboard above the review section.</small>
            </div>

            <button type="submit" class="glass-btn" style="width: 100%; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                <i class="fa-solid fa-save"></i> <?php echo !empty($app['doctor_report']) ? 'Update Checkup Scenario' : 'Submit Checkup Scenario'; ?>
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
