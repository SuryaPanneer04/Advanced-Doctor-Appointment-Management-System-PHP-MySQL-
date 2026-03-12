<?php
// user/book-appointment.php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $date = trim($_POST['appointment_date']);
    $time = trim($_POST['appointment_time']);
    $patient_query = trim($_POST['patient_query']);
    $user_id = $_SESSION['user_id'];

    if ($category_id > 0 && $doctor_id > 0 && !empty($date) && !empty($time)) {
        // Prevent booking in the past
        $datetime = strtotime("$date $time");
        if ($datetime < time()) {
            $error = "You cannot book an appointment in the past.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, patient_query, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            if ($stmt->execute([$user_id, $doctor_id, $date, $time, $patient_query])) {
                $success = "Appointment request sent successfully! Waiting for doctor approval.";
            } else {
                $error = "Failed to book appointment.";
            }
        }
    } else {
        $error = "Please fill in all the details.";
    }
}

include '../includes/header.php';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$initial_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$initial_doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
?>

<div class="global-glass-container fade-in-up" style="max-width: 600px; margin: 0 auto;">
    <h2 style="text-align: center; color: white;">Book an Appointment</h2>

    <?php if ($success): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Booked!',
                    text: '<?php echo addslashes($success); ?>',
                    confirmButtonText: 'View Appointments',
                    confirmButtonColor: '#4F46E5'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'dashboard.php';
                    }
                });
            });
        </script>
    <?php endif; ?>

    <?php if ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => { showError('Booking Failed', '<?php echo addslashes($error); ?>'); });
        </script>
    <?php endif; ?>

    <form method="POST" action="" id="booking-form">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" for="category_id" style="color: rgba(255,255,255,0.9);">Select Specialization</label>
            <select name="category_id" id="category_id" class="form-control glass-input" required>
                <option value="">-- Choose Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $initial_category_id == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Available Doctors</label>
            <input type="hidden" name="doctor_id" id="doctor_id" required>
            
            <div id="loading-doctors" style="display: none; padding: 2rem; border: 1px dashed rgba(255,255,255,0.3); border-radius: 8px; color: white;" class="text-center">
                Loading doctors...
            </div>
            
            <div id="initial-message" style="padding: 2rem; border: 1px dashed rgba(255,255,255,0.3); border-radius: 8px; color: rgba(255,255,255,0.8);" class="text-center">
                Please select a specialization first.
            </div>

            <!-- Available Doctors Container -->
            <div id="available-container-wrapper" style="display:none; margin-top: 1rem;">
                <h4 style="color: #6ee7b7; margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-check"></i> Currently Available</h4>
                <div id="doctor-cards-container" style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                </div>
            </div>

            <!-- Unavailable Doctors Container -->
            <div id="unavailable-container-wrapper" style="display:none; margin-top: 1rem;">
                <h4 style="color: #fca5a5; margin-bottom: 0.5rem;"><i class="fa-solid fa-circle-xmark"></i> Not Available</h4>
                <div id="unavailable-cards-container" style="display: grid; grid-template-columns: 1fr; gap: 1rem; opacity: 0.7;">
                </div>
            </div>
            <p id="doctor-error" class="text-danger mt-2" style="display: none; color: #fca5a5;">Please select a doctor to continue.</p>
        </div>
        
        <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label class="form-label" for="appointment_date" style="color: rgba(255,255,255,0.9);">Date</label>
                <input type="date" name="appointment_date" id="appointment_date" class="form-control glass-input" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
                <label class="form-label" for="appointment_time" style="color: rgba(255,255,255,0.9);">Time</label>
                <input type="time" name="appointment_time" id="appointment_time" class="form-control glass-input" required>
            </div>
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" for="patient_query" style="color: rgba(255,255,255,0.9);">Symptoms / Your Query</label>
            <textarea name="patient_query" id="patient_query" class="form-control glass-input" rows="3" placeholder="Describe your symptoms or reason for appointment..." required></textarea>
        </div>
        
        <button type="submit" class="glass-btn" style="width: 100%; margin-top: 1rem;"><i class="fa-solid fa-calendar-check"></i> Submit Request</button>
    </form>
</div>

<style>
<style>
.doctor-card {
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.05); /* very light glass */
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
}
.doctor-card:hover {
    border-color: rgba(255, 255, 255, 0.4);
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}
.doctor-card.selected {
    border-color: #6ee7b7; /* success green border */
    background: rgba(110, 231, 183, 0.1);
    box-shadow: 0 0 0 2px rgba(110, 231, 183, 0.3);
}
.stars {
    color: #fbbf24;
    font-size: 0.9rem;
}
</style>

<!-- Reviews Modal (hidden by default) -->
<div id="reviews-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index:9999; align-items:center; justify-content:center;">
    <div class="global-glass-container" style="width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto; position:relative; margin-bottom: 0;">
        <button id="close-modal" style="position:absolute; top:1rem; right:1rem; background:none; border:none; font-size:1.5rem; color: white; cursor:pointer;">&times;</button>
        <h3 style="margin-bottom: 1rem; color: white;">Doctor Reviews</h3>
        <div id="reviews-content" style="color: rgba(255,255,255,0.9);">
            Loading reviews...
        </div>
    </div>
</div>

<script>
const INITIAL_DOCTOR_ID = <?php echo $initial_doctor_id; ?>;
const INITIAL_CATEGORY_ID = <?php echo $initial_category_id; ?>;

document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('category_id');
    const doctorCardsContainer = document.getElementById('doctor-cards-container');
    const unavailableCardsContainer = document.getElementById('unavailable-cards-container');
    const availableWrapper = document.getElementById('available-container-wrapper');
    const unavailableWrapper = document.getElementById('unavailable-container-wrapper');
    const loadingMessage = document.getElementById('loading-doctors');
    const initialMessage = document.getElementById('initial-message');
    
    const doctorIdInput = document.getElementById('doctor_id');
    const bookingForm = document.getElementById('booking-form');
    const doctorError = document.getElementById('doctor-error');
    
    const reviewsModal = document.getElementById('reviews-modal');
    const closeModal = document.getElementById('close-modal');
    const reviewsContent = document.getElementById('reviews-content');

    bookingForm.addEventListener('submit', function(e) {
        if (!doctorIdInput.value) {
            e.preventDefault();
            doctorError.style.display = 'block';
        }
    });

    closeModal.addEventListener('click', () => reviewsModal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === reviewsModal) reviewsModal.style.display = 'none';
    });

    categorySelect.addEventListener('change', async function() {
        const catId = this.value;
        
        initialMessage.style.display = 'none';
        availableWrapper.style.display = 'none';
        unavailableWrapper.style.display = 'none';
        doctorCardsContainer.innerHTML = '';
        unavailableCardsContainer.innerHTML = '';
        doctorIdInput.value = '';

        if (catId) {
            loadingMessage.style.display = 'block';
            try {
                const response = await fetch(`get-doctors.php?category_id=${catId}`);
                const doctors = await response.json();
                
                loadingMessage.style.display = 'none';
                
                let hasAvailable = false;
                let hasUnavailable = false;

                if (doctors.length > 0) {
                    doctors.forEach(doc => {
                        let starsHtml = '';
                        const rating = parseFloat(doc.rating);
                        const isHighRated = (rating >= 4.5);
                        const starColor = isHighRated ? '#fbbf24' : 'rgba(255,255,255,0.6)';

                        for(let i=1; i<=5; i++) {
                            if (i <= Math.floor(rating)) {
                                starsHtml += `<i class="fa-solid fa-star" style="color: ${starColor};"></i>`;
                            } else if (i - 0.5 <= rating) {
                                starsHtml += `<i class="fa-solid fa-star-half-stroke" style="color: ${starColor};"></i>`;
                            } else {
                                starsHtml += `<i class="fa-regular fa-star" style="color: ${starColor};"></i>`;
                            }
                        }

                        const isAvail = (doc.is_available == 1 || doc.is_available == '1');
                        const isTopTier = (rating >= 4.8);
                        
                        const cardHTML = `
                            <div class="doctor-card ${!isAvail ? 'unavailable' : ''} ${isHighRated ? 'top-rated-glow' : ''}" data-id="${doc.id}" style="position: relative; ${isHighRated ? 'border: 1px solid rgba(251, 191, 36, 0.4); box-shadow: 0 0 15px rgba(251, 191, 36, 0.1);' : ''}">
                                ${isTopTier ? '<span class="glass-badge" style="position:absolute; top:-10px; right:-10px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #000; font-weight: 700; border: none; font-size: 0.7rem; padding: 0.2rem 0.5rem;"><i class="fa-solid fa-crown"></i> TOP</span>' : ''}
                                <div style="display: flex; gap: 1rem; align-items: start;">
                                    <img src="${doc.photo ? doc.photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doc.name) + '&background=random'}" alt="${doc.name}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid ${isHighRated ? 'rgba(251, 191, 36, 0.5)' : 'rgba(255,255,255,0.3)'};">
                                    <div>
                                        <h4 style="margin: 0; color: white;">${doc.name}</h4>
                                        <div style="margin-top: 0.2rem; display: flex; align-items: center; gap: 0.4rem;">
                                            <span class="stars">${starsHtml}</span>
                                            <span style="font-size: 0.85rem; color: white; font-weight: 600;">
                                                ${rating.toFixed(1)}
                                            </span>
                                            <span style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">( ${doc.review_count} Reviews )</span>
                                        </div>
                                        <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.8);">${doc.details ? doc.details : 'No details provided.'}</p>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: center; gap: 0.5rem;">
                                    ${!isAvail ? '<span class="glass-badge" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; text-align: center;">Booked</span>' : '<span class="glass-badge" style="background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; text-align: center;">Available</span>'}
                                    <button type="button" class="glass-btn view-reviews-btn" data-id="${doc.id}" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; width: auto; background: transparent; border: 1px solid rgba(255,255,255,0.3); box-shadow: none;" ${doc.review_count == 0 ? 'disabled' : ''}>Reviews</button>
                                </div>
                            </div>
                        `;
                        
                        if (isAvail) {
                            doctorCardsContainer.innerHTML += cardHTML;
                            hasAvailable = true;
                        } else {
                            unavailableCardsContainer.innerHTML += cardHTML;
                            hasUnavailable = true;
                        }
                    });

                    if (hasAvailable) availableWrapper.style.display = 'block';
                    if (hasUnavailable) unavailableWrapper.style.display = 'block';

                    // Add click listeners to select ONLY available doctors
                    document.querySelectorAll('.doctor-card').forEach(card => {
                        card.addEventListener('click', function(e) {
                            if(e.target.classList.contains('view-reviews-btn')) return;
                            if(this.classList.contains('unavailable')) return; // Do not select unavailable doctors
                            
                            document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected'));
                            this.classList.add('selected');
                            doctorIdInput.value = this.getAttribute('data-id');
                            doctorError.style.display = 'none';
                        });
                    });

                    // Auto-select initial doctor if matches
                    if (INITIAL_DOCTOR_ID > 0 && String(catId) === String(INITIAL_CATEGORY_ID)) {
                        const initCard = document.querySelector(`.doctor-card[data-id="${INITIAL_DOCTOR_ID}"]:not(.unavailable)`);
                        if (initCard) {
                            initCard.click();
                            // Scroll it into view
                            initCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }

                    // Add click listeners to view reviews
                    document.querySelectorAll('.view-reviews-btn').forEach(btn => {
                        btn.addEventListener('click', async function(e) {
                            e.stopPropagation();
                            const docId = this.getAttribute('data-id');
                            reviewsModal.style.display = 'flex';
                            reviewsContent.innerHTML = '<p class="text-center">Loading...</p>';
                            
                            try {
                                const res = await fetch(`get-doctor-reviews.php?doctor_id=${docId}`);
                                const reviews = await res.json();
                                
                                if (reviews.length > 0) {
                                    let revHtml = '';
                                    reviews.forEach(r => {
                                        let rStars = '';
                                        for(let i=1; i<=5; i++) {
                                            rStars += (i <= r.rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                        }
                                        revHtml += `
                                            <div style="border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 1rem; margin-bottom: 1rem;">
                                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                                    <strong style="color: white;">${r.patient_name}</strong>
                                                    <span style="font-size:0.85rem; color: rgba(255,255,255,0.6);">${r.date_formatted}</span>
                                                </div>
                                                <div class="stars" style="margin: 0.3rem 0;">${rStars}</div>
                                                <p style="margin: 0; font-size: 0.95rem; color: rgba(255,255,255,0.8);">${r.comment ? r.comment : '<em>No comment provided.</em>'}</p>
                                            </div>
                                        `;
                                    });
                                    reviewsContent.innerHTML = revHtml;
                                } else {
                                    reviewsContent.innerHTML = '<p>No reviews found.</p>';
                                }
                            } catch (error) {
                                reviewsContent.innerHTML = '<p class="text-danger">Failed to load reviews.</p>';
                            }
                        });
                    });

                } else {
                    initialMessage.innerHTML = 'No doctors available for this category';
                    initialMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Error fetching doctors:', error);
                loadingMessage.style.display = 'none';
                initialMessage.innerHTML = '<span class="text-danger">Error loading doctors</span>';
                initialMessage.style.display = 'block';
            }
        } else {
            initialMessage.innerHTML = 'Please select a specialization first.';
            initialMessage.style.display = 'block';
        }
    });

    // Auto trigger fetch if category is already selected
    if (categorySelect.value) {
        // Create and dispatch event
        const event = new Event('change');
        categorySelect.dispatchEvent(event);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
