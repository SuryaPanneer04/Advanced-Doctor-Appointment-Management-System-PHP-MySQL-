<?php
// user/register.php
require_once '../includes/db.php';
include '../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $address, $hashed_password])) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<div class="glass-wrapper fade-in-up">
    <div class="glass-card">
        <h2>Patient Registration</h2>

        <?php if ($error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showError('Registration Failed', '<?php echo addslashes($error); ?>');
                });
            </script>
        <?php endif; ?>

        <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: "<?php echo addslashes($success); ?>",
                        confirmButtonText: 'Go to Login',
                        confirmButtonColor: '#4F46E5',
                        background: 'rgba(30,30,40,0.95)',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="name" style="color: rgba(255,255,255,0.9);">Full Name</label>
                <input type="text" name="name" id="name" class="form-control glass-input" placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="email" style="color: rgba(255,255,255,0.9);">Email Address</label>
                <input type="email" name="email" id="email" class="form-control glass-input" placeholder="Enter email" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="phone" style="color: rgba(255,255,255,0.9);">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="form-control glass-input" placeholder="Enter phone number" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="address" style="color: rgba(255,255,255,0.9);">Address</label>
                <textarea name="address" id="address" class="form-control glass-input" rows="3" placeholder="Enter your full address" required></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label" for="password" style="color: rgba(255,255,255,0.9);">Password</label>
                <input type="password" name="password" id="password" class="form-control glass-input" required minlength="6" placeholder="Create a strong password">
            </div>
            
            <button type="submit" class="glass-btn">Register Account</button>
        </form>
        
        <p class="text-center" style="margin-top: 2rem; color: rgba(255,255,255,0.8);">
            Already have an account? <br>
            <a href="login.php" style="display: inline-block; margin-top: 0.5rem; border-bottom: 2px solid rgba(255,255,255,0.3); padding-bottom: 2px;">Login here</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
