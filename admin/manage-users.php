<?php
// admin/manage-users.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white; margin: 0;">Registered Patients</h2>
</div>

<div class="global-glass-container fade-in-up delay-100">
    <p style="margin-bottom: 1.5rem; color: rgba(255,255,255,0.8);">View only access to all the registered patients in the system.</p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Registered On</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($users) > 0): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['id']); ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($u['address'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center" style="color: rgba(255,255,255,0.7);">No users registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
