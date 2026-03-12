<?php
// user/leave-review.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

if ($appointment_id <= 0) {
    echo "Invalid Details.";
    exit;
}

// Verify appointment belongs to user and is approved
$stmt = $pdo->prepare("SELECT a.*, d.name as doctor_name FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.id = ? AND a.user_id = ? AND a.status = 'Approved'");
$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    echo "Appointment not found or not eligible for review.";
    exit;
}

// Check if review already exists
$stmt = $pdo->prepare("SELECT id FROM reviews WHERE appointment_id = ? AND user_id = ?");
$stmt->execute([$appointment_id, $user_id]);
if ($stmt->rowCount() > 0) {
    echo "You have already left a review for this appointment.";
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("INSERT INTO reviews (appointment_id, user_id, doctor_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$appointment_id, $user_id, $appointment['doctor_id'], $rating, $comment])) {
            // Update doctor's overall rating (dynamic average logic)
            $calcStmt = $pdo->prepare("SELECT AVG(rating) as new_avg FROM reviews WHERE doctor_id = ?");
            $calcStmt->execute([$appointment['doctor_id']]);
            $newAvg = (float)$calcStmt->fetchColumn();
            
            // Round to 1 decimal place or keep high precision if preferred
            $updateStmt = $pdo->prepare("UPDATE doctors SET rating = ? WHERE id = ?");
            $updateStmt->execute([number_format($newAvg, 1, '.', ''), $appointment['doctor_id']]);

            $success = "Review submitted successfully!";
        } else {
            $error = "Failed to submit review.";
        }
    } else {
        $error = "Please provide a valid rating between 1 and 5.";
    }
}

include '../includes/header.php';
?>

<div class="global-glass-container fade-in-up" style="max-width: 600px; margin: 0 auto; margin-top: 2rem;">
    <h2 style="color: white; margin-bottom: 1.5rem;">Leave a Review</h2>
    
    <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div>
                <p style="margin-bottom: 0.2rem; color: rgba(255,255,255,0.6); font-size: 0.85rem;">Treating Doctor</p>
                <strong style="color: white; font-size: 1.1rem;"><i class="fa-solid fa-user-md"></i> <?php echo htmlspecialchars($appointment['doctor_name']); ?></strong>
            </div>
            <div style="text-align: right;">
                <p style="margin-bottom: 0.2rem; color: rgba(255,255,255,0.6); font-size: 0.85rem;">Date</p>
                <span style="color: white; font-weight: 500;"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></span>
            </div>
        </div>
        
        <?php if (!empty($appointment['doctor_report'])): ?>
            <div style="margin-top: 1.5rem; padding: 1.2rem; background: rgba(110, 231, 183, 0.1); border-left: 4px solid #6ee7b7; border-radius: 4px;">
                <h4 style="color: #6ee7b7; margin-bottom: 0.5rem; font-size: 0.95rem;"><i class="fa-solid fa-file-medical"></i> DOCTOR'S CHECKUP SCENARIO:</h4>
                <p style="color: white; margin: 0; font-style: italic; line-height: 1.6;">
                    "<?php echo nl2br(htmlspecialchars($appointment['doctor_report'])); ?>"
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($success): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Thank You!',
                    text: '<?php echo addslashes($success); ?>',
                    confirmButtonText: 'Back to Dashboard',
                    confirmButtonColor: '#4F46E5'
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
            });
        </script>
    <?php elseif ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => { showError('Error', '<?php echo addslashes($error); ?>'); });
        </script>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9); margin-bottom: 1rem; display: block;">Rating (1 to 5 Stars)</label>
            <div style="display: flex; gap: 0.8rem; font-size: 2rem; color: #fbbf24; cursor: pointer;" id="star-rating">
                <i class="fa-regular fa-star" data-value="1"></i>
                <i class="fa-regular fa-star" data-value="2"></i>
                <i class="fa-regular fa-star" data-value="3"></i>
                <i class="fa-regular fa-star" data-value="4"></i>
                <i class="fa-regular fa-star" data-value="5"></i>
            </div>
            <input type="hidden" name="rating" id="rating-input" required>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" for="comment" style="color: rgba(255,255,255,0.9);">Your Comments</label>
            <textarea name="comment" id="comment" class="form-control glass-input" rows="4" placeholder="Share your experience with HealthyHub specialists..."></textarea>
        </div>

        <button type="submit" class="glass-btn" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Submit Review</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('#star-rating i');
    const ratingInput = document.getElementById('rating-input');
    let currentRating = 0;

    function updateStars(value) {
        stars.forEach(s => {
            if (s.getAttribute('data-value') <= value) {
                s.classList.remove('fa-regular');
                s.classList.add('fa-solid');
                s.style.transform = 'scale(1.1)';
            } else {
                s.classList.remove('fa-solid');
                s.classList.add('fa-regular');
                s.style.transform = 'scale(1)';
            }
        });
    }

    stars.forEach(star => {
        // Hover effect
        star.addEventListener('mouseover', function() {
            updateStars(this.getAttribute('data-value'));
        });
        
        // Remove hover effect
        star.addEventListener('mouseout', function() {
            updateStars(currentRating);
        });

        // Click to set rating
        star.addEventListener('click', function() {
            currentRating = this.getAttribute('data-value');
            ratingInput.value = currentRating;
            updateStars(currentRating);
            
            // Add a little pop animation
            this.style.transform = 'scale(1.3)';
            setTimeout(() => { this.style.transform = 'scale(1.1)'; }, 150);
        });
        
        // Add CSS transition safely via JS
        star.style.transition = 'all 0.2s ease';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
