<?php
// doctor/login.php
require_once '../includes/db.php';
session_start();

if (isset($_SESSION['doctor_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        $doctor = $stmt->fetch();

        if ($doctor && password_verify($password, $doctor['password'])) {
            $_SESSION['doctor_id'] = $doctor['id'];
            $_SESSION['doctor_name'] = $doctor['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

include '../includes/header.php';
?>

<div class="global-glass-container fade-in-up" style="max-width: 400px; margin: 4rem auto;">
    <h2 style="text-align: center; color: white; margin-bottom: 2rem;"><i class="fa-solid fa-user-md"></i> Doctor Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" for="email" style="color: rgba(255,255,255,0.9);">Email Address</label>
            <input type="email" name="email" id="email" class="form-control glass-input" required placeholder="doctor@example.com">
        </div>
        
        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" for="password" style="color: rgba(255,255,255,0.9);">Password</label>
            <input type="password" name="password" id="password" class="form-control glass-input" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="glass-btn btn-primary" style="width: 100%;">Login to Portal</button>
    </form>
    
    <div style="margin-top: 1.5rem; text-align: center; color: rgba(255,255,255,0.6); font-size: 0.9rem;">
        Default password is <code>doctor123</code>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
