<?php
// user/dashboard.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch user's appointments along with review status
$stmt = $pdo->prepare("
    SELECT a.*, d.name as doctor_name, c.name as category_name,
           (SELECT COUNT(*) FROM reviews r WHERE r.appointment_id = a.id) as has_review
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN categories c ON d.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll();

// Fetch Categories for "Explore Specializations" section
$categoriesStmt = $pdo->query("
    SELECT c.id, c.name, COUNT(d.id) as doctor_count 
    FROM categories c 
    LEFT JOIN doctors d ON c.id = d.category_id 
    GROUP BY c.id, c.name 
    ORDER BY c.name ASC
");
$categories = $categoriesStmt->fetchAll();
?>

<div class="global-glass-container fade-in-up">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>My Appointments</h2>
        <a href="book-appointment.php" class="glass-btn"><i class="fa-solid fa-plus"></i> Book New</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Doctor</th>
                    <th>Token #</th>
                    <th>Queue Status</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($appointments) > 0): ?>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?></strong><br>
                                <small class="text-gray"><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                            <td><span class="glass-badge" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; font-weight: 700;">#<?php echo $appt['token_number']; ?></span></td>
                            <td>
                                <?php 
                                    if ($appt['status'] === 'Approved') {
                                        // Calculate people ahead
                                        $aheadStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND token_number < ? AND status = 'Approved' AND doctor_report IS NULL");
                                        $aheadStmt->execute([$appt['doctor_id'], $appt['appointment_date'], $appt['token_number']]);
                                        $ahead = $aheadStmt->fetchColumn();
                                        $waitTime = $ahead * 15; // 15 mins per patient
                                        
                                        if ($ahead == 0) {
                                            echo '<span style="color: #6ee7b7;"><i class="fa-solid fa-person-walking"></i> You are next!</span>';
                                        } else {
                                            echo '<span style="color: #fbbf24;"><i class="fa-solid fa-clock"></i> '.$ahead.' ahead (~'.$waitTime.'m)</span>';
                                        }
                                    } else {
                                        echo '<span class="text-gray">Pending Approval</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = 'badge-pending';
                                    if ($appt['status'] === 'Approved') $statusClass = 'badge-approved';
                                    if ($appt['status'] === 'Cancelled') $statusClass = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($appt['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                                    <?php if (!empty($appt['doctor_report'])): ?>
                                        <button class="glass-btn" style="background: rgba(79, 70, 229, 0.2); border-color: rgba(79, 70, 229, 0.4); color: white; padding: 0.4rem 0.8rem; font-size: 0.9rem; height: auto;" 
                                                onclick="showReport('<?php echo addslashes(htmlspecialchars($appt['doctor_name'])); ?>', '<?php echo addslashes(htmlspecialchars($appt['doctor_report'])); ?>')">
                                            <i class="fa-solid fa-file-medical"></i> View Report
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($appt['status'] === 'Approved'): ?>
                                        <?php if ($appt['has_review'] > 0): ?>
                                            <span class="text-gray" style="font-size: 0.9rem;"><i class="fa-solid fa-check"></i> Reviewed</span>
                                        <?php else: ?>
                                            <a href="leave-review.php?appointment_id=<?php echo $appt['id']; ?>" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">Leave Review</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($appt['doctor_report']) && $appt['status'] !== 'Approved'): ?>
                                        <span class="text-gray">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 3rem 1rem;">
                            <p style="color: var(--gray); margin-bottom: 1rem;">You have not booked any appointments yet.</p>
                            <a href="book-appointment.php" class="btn btn-primary">Book Your First Appointment</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </table>
    </div>
</div>

<!-- Explore Specializations Section -->
<div class="global-glass-container fade-in-up delay-100" style="margin-top: 2rem;">
    <h3 style="margin-bottom: 1.5rem; color: white;">Explore Specializations</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1rem;">
        <?php foreach($categories as $cat): ?>
            <a href="doctors-directory.php?category_id=<?php echo $cat['id']; ?>" style="text-decoration: none; color: inherit;">
                <div class="global-glass-card" style="text-align: center; padding: 2rem 1rem; cursor: pointer;">
                    <i class="fa-solid fa-stethoscope" style="font-size: 2.5rem; color: white; margin-bottom: 1rem;"></i>
                    <h4 style="margin: 0 0 0.5rem 0; color: white;"><?php echo htmlspecialchars($cat['name']); ?></h4>
                    <p style="margin: 0; color: rgba(255,255,255,0.8); font-size: 0.9rem;">
                        <?php echo $cat['doctor_count']; ?> Specialist<?php echo $cat['doctor_count'] != 1 ? 's' : ''; ?>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Pro UI: About & Contact Sections -->
<div class="fade-in-up delay-200" style="margin-top: 2rem;">
    <h2 class="section-title" style="color: white;">Discover HealthyHub</h2>
    
    <div class="pro-grid">
        <!-- About Section -->
        <div class="global-glass-container" style="position: relative; overflow: hidden; height: 100%; margin-bottom: 0;">
            <i class="fa-solid fa-hospital-user pro-card-bg-icon" style="color: rgba(255,255,255,0.05);"></i>
            <div class="icon-box animate-float" style="background: rgba(255,255,255,0.1); color: white;">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 1rem; color: white;">About Our Platform</h3>
            <p style="color: rgba(255,255,255,0.9); line-height: 1.8; margin-bottom: 1.5rem; font-size: 1rem;">
                HealthyHub is an advanced, globally recognized digital healthcare ecosystem designed to bridge the gap between world-class medical specialists and patients in need. 
                We leverage state-of-the-art technology to provide seamless appointment scheduling, transparent verified reviews, and secure patient data management.
            </p>
            <p style="color: rgba(255,255,255,0.9); line-height: 1.8; font-size: 1rem;">
                Our mission is to empower individuals to take control of their health by providing immediate access to the finest healthcare professionals across multiple disciplinary fields. 
                <strong>Your health, revolutionized.</strong>
            </p>
        </div>

        <!-- Contact Section -->
        <div class="global-glass-container" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.4) 0%, rgba(236, 72, 153, 0.3) 100%); margin-bottom: 0;">
            <i class="fa-solid fa-headset pro-card-bg-icon" style="color: rgba(255,255,255,0.05);"></i>
            <div class="icon-box" style="background: rgba(255,255,255,0.1); color: white;">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: white;">Get In Touch</h3>
            <p style="opacity: 0.9; margin-bottom: 1.5rem; color: rgba(255,255,255,0.9);">Have questions or need assistance? Our support team is available 24/7 to help you.</p>
            
            <ul class="contact-list" style="color: white;">
                <li>
                    <i class="fa-solid fa-globe"></i>
                    <a href="https://www.healthyhub-health.example.com" target="_blank" style="color: white;">www.healthyhub-health.com</a>
                </li>
                <li>
                    <i class="fa-solid fa-phone"></i>
                    <a href="tel:+18001234567" style="color: white;">+1 (800) 123-4567</a>
                </li>
                <li>
                    <i class="fa-solid fa-envelope"></i>
                    <a href="mailto:support@healthyhub-health.com" style="color: white;">support@healthyhub-health.com</a>
                </li>
                <li>
                    <i class="fa-solid fa-location-dot"></i>
                    <span>123 Health Avenue, Innovation District, NY 10001</span>
                </li>
            </ul>

            <!-- <a href="#" class="btn-glass" style="border-color: rgba(255,255,255,0.3);" onclick="event.preventDefault(); Swal.fire('Live Chat', 'Our live chat system is currently connecting you to an agent...', 'info');">
                <i class="fa-solid fa-comments"></i> Start Live Chat
            </a> -->
        </div>
    </div>
</div>

<style>
/* Remove old card hover since global-glass-container handles it */
</style>

<script>
function showReport(doctorName, reportText) {
    if (!reportText || reportText.trim() === "") {
        Swal.fire({
            title: 'Pending',
            text: 'Doctor has not submitted the report yet.',
            icon: 'info',
            confirmButtonColor: '#4F46E5'
        });
        return;
    }
    
    Swal.fire({
        title: '<div style="color: #4338ca !important; font-weight: 800; font-size: 1.5rem; margin-top: 0.5rem;">HealthyHub Medical Report</div>',
        html: `
            <div style="text-align: left; padding: 1.5rem; background: #ffffff !important; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 1rem;">
                <div style="margin-bottom: 1.2rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.6rem;">
                    <span style="color: #475569 !important; font-size: 0.85rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; display: block;">Treating Physician</span>
                    <div style="color: #0f172a !important; font-size: 1.2rem; font-weight: 800; margin-top: 0.2rem;">${doctorName}</div>
                </div>
                <div>
                    <span style="color: #475569 !important; font-size: 0.85rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; display: block;">Report Details / Diagnosis</span>
                    <div style="color: #1e293b !important; font-size: 1.05rem; line-height: 1.7; margin-top: 0.8rem; white-space: pre-wrap; font-weight: 600; min-height: 100px;">
                        "${reportText}"
                    </div>
                </div>
            </div>
            <div style="margin-top: 1.5rem; text-align: center; color: #64748b !important; font-size: 0.85rem; font-weight: 600;">
                &copy; 2026 HealthyHub Healthcare System
            </div>
        `,
        showCloseButton: true,
        confirmButtonText: 'Great, Thanks!',
        confirmButtonColor: '#4F46E5',
        width: '600px',
        background: '#ffffff',
        padding: '2rem'
    });
}
</script>

<?php include '../includes/footer.php'; ?>
