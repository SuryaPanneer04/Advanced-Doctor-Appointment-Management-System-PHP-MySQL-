<?php
// user/login.php
require_once '../includes/db.php';
include '../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>



<div class="glass-wrapper">
    <div class="glass-card">
        <h2>Patient Login</h2>

        <?php if ($error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showError('Login Failed', '<?php echo addslashes($error); ?>');
                });
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control glass-input" placeholder="Enter your email" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control glass-input" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="glass-btn">Secure Login</button>
        </form>
        
        <p class="text-center" style="margin-top: 2rem; color: rgba(255,255,255,0.8);">
            Don't have an account? <br>
            <a href="register.php" style="display: inline-block; margin-top: 0.5rem; border-bottom: 2px solid rgba(255,255,255,0.3); padding-bottom: 2px; color: white;">Create an Account</a>
        </p>
        
        <div class="text-center" style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1.5rem;">
            <a href="../admin/login.php" target="_blank" class="glass-btn" style="padding: 0.5rem 1rem; font-size: 0.9rem; background: rgba(0,0,0,0.2);">Admin Login</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
