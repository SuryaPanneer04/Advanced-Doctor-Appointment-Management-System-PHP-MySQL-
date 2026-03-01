<?php
// admin/manage-categories.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $success = "Category added successfully.";
        } else {
            $error = "Failed to add category.";
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Category deleted successfully.";
    } else {
         $error = "Failed to delete category. It might be linked to doctors.";
    }
}

include '../includes/header.php';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white; margin: 0;">Manage Specializations</h2>
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

<div class="global-glass-container fade-in-up delay-100" style="margin-bottom: 2rem;">
    <h3 style="margin-bottom: 1.5rem; color: white;">Add New Specialization</h3>
    <form method="POST" action="" style="display: flex; gap: 1rem; align-items: flex-end;">
        <input type="hidden" name="action" value="add">
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <label class="form-label" for="name" style="color: rgba(255,255,255,0.9);">Category Name</label>
            <input type="text" name="name" id="name" class="form-control glass-input" placeholder="e.g. Cardiologist" required>
        </div>
        <button type="submit" class="glass-btn">Add Category</button>
    </form>
</div>

<div class="global-glass-container fade-in-up delay-200">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($categories) > 0): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['id']); ?></td>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td>
                                <a href="manage-categories.php?delete=<?php echo $cat['id']; ?>" 
                                   class="glass-btn glass-btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; height: auto;"
                                   onclick="event.preventDefault(); const url = this.href; confirmAction('Delete Category', 'Are you sure you want to delete this category? All doctors inside it will be removed permanently.', 'Yes, Delete', () => window.location.href=url);">
                                   <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center" style="color: rgba(255,255,255,0.7);">No categories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
