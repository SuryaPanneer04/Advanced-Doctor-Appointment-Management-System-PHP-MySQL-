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

<div class="card" style="max-width: 600px; margin: 0 auto; margin-top: 2rem;">
    <h2 class="card-title">Leave a Review</h2>
    <p>Doctor: <strong><?php echo htmlspecialchars($appointment['doctor_name']); ?></strong></p>
    <p>Date: <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></p>
    <hr style="margin: 1rem 0;">

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
        <div class="form-group">
            <label class="form-label">Rating (1 to 5 Stars)</label>
            <div style="display: flex; gap: 0.5rem; font-size: 1.5rem; color: #fbbf24; cursor: pointer;" id="star-rating">
                <i class="fa-regular fa-star" data-value="1"></i>
                <i class="fa-regular fa-star" data-value="2"></i>
                <i class="fa-regular fa-star" data-value="3"></i>
                <i class="fa-regular fa-star" data-value="4"></i>
                <i class="fa-regular fa-star" data-value="5"></i>
            </div>
            <input type="hidden" name="rating" id="rating-input" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="comment">Comment</label>
            <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Share your experience... (optional)"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Submit Review</button>
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
