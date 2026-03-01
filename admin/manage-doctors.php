<?php
// admin/manage-doctors.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle Add Doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $photo = trim($_POST['photo']);
    $details = trim($_POST['details']);
    $is_available = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 1;
    $category_id = (int)$_POST['category_id'];

    if (!empty($name) && !empty($email) && !empty($phone) && $category_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO doctors (category_id, name, email, phone, photo, details, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$category_id, $name, $email, $phone, $photo, $details, $is_available])) {
            $success = "Doctor added successfully.";
        } else {
            $error = "Failed to add doctor.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle Delete Doctor
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Doctor deleted successfully.";
    } else {
         $error = "Failed to delete doctor.";
    }
}

include '../includes/header.php';

// Fetch required data
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$doctors = $pdo->query("
    SELECT d.*, c.name as category_name 
    FROM doctors d 
    JOIN categories c ON d.category_id = c.id 
    ORDER BY d.id DESC
")->fetchAll();
?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white; margin: 0;">Manage Doctors</h2>
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
    <h3 style="margin-bottom: 1.5rem; color: white;">Add New Doctor</h3>
    <?php if (count($categories) === 0): ?>
        <p style="color: #fca5a5;">Please add at least one category before adding a doctor.</p>
    <?php else: ?>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="name" style="color: rgba(255,255,255,0.9);">Doctor's Name</label>
                <input type="text" name="name" id="name" class="form-control glass-input" placeholder="Dr. John Doe" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="category_id" style="color: rgba(255,255,255,0.9);">Specialization</label>
                <select name="category_id" id="category_id" class="form-control glass-input" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="email" style="color: rgba(255,255,255,0.9);">Email Address</label>
                <input type="email" name="email" id="email" class="form-control glass-input" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="phone" style="color: rgba(255,255,255,0.9);">Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control glass-input" required>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="photo" style="color: rgba(255,255,255,0.9);">Photo URL (Optional)</label>
                <input type="url" name="photo" id="photo" class="form-control glass-input" placeholder="https://example.com/photo.jpg">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="details" style="color: rgba(255,255,255,0.9);">Bio / Details (Optional)</label>
                <textarea name="details" id="details" class="form-control glass-input" rows="3" placeholder="Short biography..."></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label" for="is_available" style="color: rgba(255,255,255,0.9);">Status</label>
                <select name="is_available" id="is_available" class="form-control glass-input">
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>
            </div>
            
            <button type="submit" class="glass-btn">Add Doctor</button>
        </form>
    <?php endif; ?>
</div>

<div class="global-glass-container fade-in-up delay-200">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($doctors) > 0): ?>
                    <?php foreach ($doctors as $doc): ?>
                        <tr>
                            <td>
                                <?php if($doc['photo']): ?>
                                    <img src="<?php echo htmlspecialchars($doc['photo']); ?>" alt="Photo" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border: 2px solid rgba(255,255,255,0.3);">
                                <?php else: ?>
                                    <i class="fa-solid fa-user-doctor" style="font-size: 24px; color: rgba(255,255,255,0.5);"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: white;"><?php echo htmlspecialchars($doc['name']); ?></strong>
                                <?php if($doc['details']): ?>
                                    <br><small style="color: rgba(255,255,255,0.7);" title="<?php echo htmlspecialchars($doc['details']); ?>"><?php echo substr(htmlspecialchars($doc['details']), 0, 30); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="glass-badge" style="background: rgba(79, 70, 229, 0.4); border-color: rgba(79, 70, 229, 0.6);"><?php echo htmlspecialchars($doc['category_name']); ?></span></td>
                            <td><?php echo htmlspecialchars($doc['email']); ?></td>
                            <td><?php echo htmlspecialchars($doc['phone']); ?></td>
                            <td>
                                <?php if($doc['is_available'] == 1): ?>
                                    <span class="glass-badge" style="background: rgba(5, 150, 105, 0.4); border-color: rgba(5, 150, 105, 0.5); cursor: pointer;" onclick="toggleAvailability(<?php echo $doc['id']; ?>, 0)" title="Click to mark as Not Available">Available</span>
                                <?php else: ?>
                                    <span class="glass-badge" style="background: rgba(220, 38, 38, 0.4); border-color: rgba(220, 38, 38, 0.5); cursor: pointer;" onclick="toggleAvailability(<?php echo $doc['id']; ?>, 1)" title="Click to mark as Available">Not Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="manage-doctors.php?delete=<?php echo $doc['id']; ?>"  
                                   class="glass-btn glass-btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; height: auto;"
                                   onclick="event.preventDefault(); const url = this.href; confirmAction('Delete Doctor', 'Are you sure you want to delete this doctor? All their appointments will be removed!', 'Yes, Delete', () => window.location.href=url);">
                                   <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center" style="color: rgba(255,255,255,0.7);">No doctors found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function toggleAvailability(doctorId, newStatus) {
    try {
        const formData = new FormData();
        formData.append('doctor_id', doctorId);
        formData.append('is_available', newStatus);
        
        const response = await fetch('toggle-availability.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if(result.success) {
            window.location.reload();
        } else {
            showError('Error', result.error || 'Failed to update availability.');
        }
    } catch (e) {
        showError('Error', 'An unexpected error occurred.');
    }
}
</script>

<?php include '../includes/footer.php'; ?>
