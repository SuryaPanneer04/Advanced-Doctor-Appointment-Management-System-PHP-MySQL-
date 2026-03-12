<?php
// doctor/profile.php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $details = trim($_POST['details']);
    $password = $_POST['password'];

    if(!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE doctors SET name = ?, phone = ?, details = ?, password = ? WHERE id = ?");
        $result = $stmt->execute([$name, $phone, $details, $hashed_password, $doctor_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE doctors SET name = ?, phone = ?, details = ? WHERE id = ?");
        $result = $stmt->execute([$name, $phone, $details, $doctor_id]);
    }

    if ($result) {
        $success = "Profile updated successfully.";
        $_SESSION['doctor_name'] = $name;
    } else {
        $error = "Failed to update profile.";
    }
}

// Fetch current details
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

include '../includes/header.php';
?>

<div class="global-glass-container fade-in-up" style="max-width: 600px; margin: 0 auto;">
    <h2 style="color: white; margin-bottom: 2rem;"><i class="fa-solid fa-user-edit"></i> My Profile</h2>

    <?php if ($success): ?>
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Full Name</label>
            <input type="text" name="name" class="form-control glass-input" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Email (Cannot change)</label>
            <input type="email" class="form-control glass-input" value="<?php echo htmlspecialchars($doctor['email']); ?>" readonly style="opacity: 0.6; cursor: not-allowed;">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Phone Number</label>
            <input type="text" name="phone" class="form-control glass-input" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Professional Details</label>
            <textarea name="details" class="form-control glass-input" rows="4"><?php echo htmlspecialchars($doctor['details']); ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control glass-input" placeholder="••••••••">
        </div>

        <button type="submit" class="glass-btn btn-primary" style="width: 100%;">Update Profile</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
