<?php
// user/doctors-by-category.php
require_once '../includes/db.php';
session_start();

include '../includes/header.php';

// Fetch all categories and their doctors (sorted by rating DESC)
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll();

$doctorsByCategory = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("
        SELECT d.*, c.name as category_name,
               COALESCE(AVG(r.rating), 0) as avg_rating, 
               COUNT(r.id) as review_count 
        FROM doctors d 
        JOIN categories c ON d.category_id = c.id
        LEFT JOIN reviews r ON d.id = r.doctor_id 
        WHERE d.category_id = ?
        GROUP BY d.id, d.name, d.photo, d.details, d.is_available, d.category_id, d.rating, c.name
        ORDER BY d.rating DESC, d.name ASC
    ");
    $stmt->execute([$cat['id']]);
    $doctors = $stmt->fetchAll();
    
    if (count($doctors) > 0) {
        $doctorsByCategory[] = [
            'category' => $cat,
            'doctors' => $doctors
        ];
    }
}
?>

<div class="fade-in-up" style="margin-bottom: 3rem; text-align: center;">
    <h1 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Specialist Directory</h1>
    <p style="color: rgba(255,255,255,0.8); max-width: 700px; margin: 0 auto;">Browse our world-class medical professionals grouped by their specializations. Doctors are ranked by their excellence and patient satisfaction.</p>
</div>

<?php if (empty($doctorsByCategory)): ?>
    <div class="global-glass-container text-center" style="padding: 5rem 2rem;">
        <i class="fa-solid fa-user-doctor" style="font-size: 4rem; color: rgba(255,255,255,0.3); margin-bottom: 2rem;"></i>
        <h2 style="color: white;">No Doctors Available</h2>
        <p style="color: rgba(255,255,255,0.7);">We are currently updating our directory. Please check back later.</p>
    </div>
<?php else: ?>
    <?php foreach ($doctorsByCategory as $group): ?>
        <div class="category-section fade-in-up" style="margin-bottom: 4rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
                <div class="icon-box" style="margin-bottom: 0; background: rgba(79, 70, 229, 0.2); color: #818cf8; width: 3.5rem; height: 3.5rem; font-size: 1.5rem;">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>
                <div>
                    <h2 style="color: white; margin: 0; font-size: 1.8rem;"><?php echo htmlspecialchars($group['category']['name']); ?></h2>
                    <p style="margin: 0; color: rgba(255,255,255,0.6); font-size: 0.9rem;"><?php echo count($group['doctors']); ?> Certified Specialists</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                <?php foreach ($group['doctors'] as $doc): ?>
                    <?php 
                        $isAvail = ($doc['is_available'] == 1);
                        $rating = (float)$doc['rating'];
                        $isTopTier = ($rating >= 4.8);
                        $isHighRated = ($rating >= 4.5);
                        $photoUrl = $doc['photo'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($doc['name']) . '&background=random&size=200';
                        
                        $cardStyle = "display: flex; flex-direction: column; height: 100%; transition: transform 0.3s ease;";
                        if (!$isAvail) $cardStyle .= "opacity: 0.7;";
                        if ($isHighRated) $cardStyle .= "border: 1px solid rgba(251, 191, 36, 0.4); box-shadow: 0 0 20px rgba(251, 191, 36, 0.15);";
                    ?>
                    <div class="global-glass-card <?php echo $isHighRated ? 'top-rated-glow' : ''; ?>" style="<?php echo $cardStyle; ?>">
                        <?php if ($isTopTier): ?>
                            <div style="position: absolute; top: -10px; right: -10px; z-index: 10;">
                                <span class="glass-badge" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #000; font-weight: 700; border: none; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                                    <i class="fa-solid fa-crown"></i> Top-Tier Expert
                                </span>
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; gap: 1.2rem; align-items: start; margin-bottom: 1.5rem; flex-grow: 1;">
                            <img src="<?php echo $photoUrl; ?>" alt="<?php echo htmlspecialchars($doc['name']); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid <?php echo $isHighRated ? 'rgba(251, 191, 36, 0.5)' : 'rgba(255,255,255,0.2)'; ?>;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.4rem 0; color: white; font-size: 1.2rem;"><?php echo htmlspecialchars($doc['name']); ?></h4>
                                <div class="review-stars" style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: <?php echo $isHighRated ? '#fbbf24' : '#e2e8f0'; ?>;">
                                    <?php 
                                    for($i=1; $i<=5; $i++) {
                                        if ($i <= floor($rating)) {
                                            echo '<i class="fa-solid fa-star"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fa-solid fa-star-half-stroke"></i>';
                                        } else {
                                            echo '<i class="fa-regular fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span style="color: white; font-weight: 600; margin-left: 0.5rem;"><?php echo number_format($rating, 1); ?></span>
                                    <span style="font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-left: 0.5rem;">( <?php echo $doc['review_count']; ?> Reviews )</span>
                                </div>
                                <p style="margin: 0; font-size: 0.85rem; color: rgba(255,255,255,0.7); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($doc['details'] ?: 'Dedicated healthcare professional providing expert medical care and personalized treatment plans.'); ?>
                                </p>
                            </div>
                        </div>
                        <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; gap: 0.8rem;">
                            <button onclick="viewReviews(<?php echo $doc['id']; ?>)" class="glass-btn" style="width: 100%; margin: 0; font-size: 0.8rem; padding: 0.4rem; background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                                <i class="fa-solid fa-comments"></i> View Reviews
                            </button>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">Status:</span>
                                <?php if ($isAvail): ?>
                                    <span class="glass-badge" style="background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; padding: 0.3rem 0.8rem; font-size: 0.8rem;">
                                        <i class="fa-solid fa-circle-check"></i> Available Now
                                    </span>
                                <?php else: ?>
                                    <span class="glass-badge" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 0.3rem 0.8rem; font-size: 0.8rem;">
                                        <i class="fa-solid fa-circle-xmark"></i> Fully Booked
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($isAvail): ?>
                                <a href="book-appointment.php?category_id=<?php echo $doc['category_id']; ?>&doctor_id=<?php echo $doc['id']; ?>" class="glass-btn" style="padding: 0.6rem 1.2rem; font-size: 0.9rem; margin: 0; width: 100%; text-align: center; background: <?php echo $isHighRated ? 'rgba(251, 191, 36, 0.2)' : ''; ?>; border-color: <?php echo $isHighRated ? 'rgba(251, 191, 36, 0.4)' : ''; ?>; font-weight: 600;">
                                    Book Appointment
                                </a>
                            <?php else: ?>
                                <button class="glass-btn" disabled style="padding: 0.6rem 1.2rem; font-size: 0.9rem; margin: 0; width: 100%; text-align: center; opacity: 0.5; cursor: not-allowed;">
                                    Not Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Reviews Modal -->
<div id="reviewsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; padding: 1rem; backdrop-filter: blur(10px);">
    <div class="global-glass-container fade-in-up" style="max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; position: relative; padding: 2.5rem;">
        <button id="closeReviews" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; opacity: 0.6; transition: 0.3s;"><i class="fa-solid fa-xmark"></i></button>
        <h3 style="color: white; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.8rem;">
            <i class="fa-solid fa-comments" style="color: var(--primary);"></i> Doctor Reviews
        </h3>
        <div id="reviewsContent" style="color: white;">
            <!-- Reviews will be injected here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const reviewsModal = document.getElementById('reviewsModal');
    const reviewsContent = document.getElementById('reviewsContent');
    const closeReviews = document.getElementById('closeReviews');

    closeReviews.onclick = () => reviewsModal.style.display = 'none';
    window.onclick = (event) => { if (event.target == reviewsModal) reviewsModal.style.display = 'none'; };

    // Function to show reviews
    window.viewReviews = async function(docId) {
        reviewsModal.style.display = 'flex';
        reviewsContent.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; opacity: 0.5;"></i><p style="margin-top: 1rem;">Loading patient feedback...</p></div>';
        
        try {
            const res = await fetch(`get-doctor-reviews.php?doctor_id=${docId}`);
            const reviews = await res.json();
            
            if (reviews.length > 0) {
                let revHtml = '';
                reviews.forEach(r => {
                    let rStars = '';
                    for(let i=1; i<=5; i++) {
                        rStars += (i <= r.rating) ? '<i class="fa-solid fa-star" style="color: #fbbf24;"></i>' : '<i class="fa-regular fa-star" style="color: rgba(255,255,255,0.3);"></i>';
                    }
                    revHtml += `
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.8rem;">
                                <div>
                                    <strong style="color: white; font-size: 1.1rem; display: block;">${r.patient_name}</strong>
                                    <div style="font-size: 0.85rem; margin-top: 0.2rem;">${rStars}</div>
                                </div>
                                <span style="font-size: 0.8rem; color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); padding: 0.2rem 0.6rem; border-radius: 50px;">${r.date_formatted}</span>
                            </div>
                            <p style="margin: 0; font-size: 0.95rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-style: italic;">"${r.comment ? r.comment : 'No comment provided.'}"</p>
                        </div>
                    `;
                });
                reviewsContent.innerHTML = revHtml;
            } else {
                reviewsContent.innerHTML = '<div style="text-align: center; padding: 3rem; opacity: 0.6;"><i class="fa-solid fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem;"></i><p>No reviews found for this specialist yet.</p></div>';
            }
        } catch (error) {
            reviewsContent.innerHTML = '<p class="text-danger text-center">Failed to load reviews. Please try again.</p>';
        }
    }
});
</script>

<style>
.category-section {
    position: relative;
    z-index: 1;
}
.global-glass-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
}
</style>

<?php include '../includes/footer.php'; ?>
