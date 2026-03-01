<?php
// admin/manage-reviews.php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle Delete Review
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Review deleted successfully.";
    } else {
        $error = "Failed to delete review.";
    }
}

// Fetch all reviews
$stmt = $pdo->query("
    SELECT r.id, r.rating, r.comment, r.created_at, 
           u.name as patient_name, 
           d.name as doctor_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN doctors d ON r.doctor_id = d.id 
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white; margin: 0;">Manage Patient Reviews</h2>
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
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Specialist</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($reviews) > 0): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td><?php echo date('M j, Y g:i A', strtotime($rev['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($rev['patient_name']); ?></td>
                            <td><strong><?php echo htmlspecialchars($rev['doctor_name']); ?></strong></td>
                            <td class="text-warning" style="text-shadow: 0 0 5px rgba(251, 191, 36, 0.5);">
                                <?php
                                for($i=1; $i<=5; $i++) {
                                    echo ($i <= $rev['rating']) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                }
                                ?>
                            </td>
                            <td>
                                <div style="max-width: 300px; white-space: normal; line-height: 1.4; color: rgba(255,255,255,0.9);">
                                    <?php echo htmlspecialchars($rev['comment']); ?>
                                </div>
                            </td>
                            <td>
                                <a href="manage-reviews.php?delete=<?php echo $rev['id']; ?>" 
                                   class="glass-btn glass-btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; height: auto;"
                                   onclick="event.preventDefault(); const url = this.href; confirmAction('Delete Review', 'Are you sure you want to delete this patient review? This action cannot be undone.', 'Yes, Delete', () => window.location.href=url);">
                                   <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center" style="padding: 2rem; color: rgba(255,255,255,0.7);">No reviews have been submitted yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
