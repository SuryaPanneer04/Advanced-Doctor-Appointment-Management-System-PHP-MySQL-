<?php
// admin/login.php
require_once '../includes/db.php';
include '../includes/header.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>

<div class="glass-wrapper fade-in-up">
    <div class="global-glass-container" style="max-width: 450px; padding: 3rem;">
        <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Admin Portal</h2>

        <?php if ($error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showError('Access Denied', '<?php echo addslashes($error); ?>');
                });
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="username" style="color: rgba(255,255,255,0.9);">Admin Username</label>
                <input type="text" name="username" id="username" class="form-control glass-input" placeholder="Enter username" required autofocus>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label" for="password" style="color: rgba(255,255,255,0.9);">Password</label>
                <input type="password" name="password" id="password" class="form-control glass-input" placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="glass-btn glass-btn-danger" style="width: 100%;">Secure Login</button>
        </form>
        
        <p class="text-center mt-4">
            <a href="../index.php" style="color: rgba(255,255,255,0.8); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 2px;">Return to Homepage</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
