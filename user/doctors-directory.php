<?php
// user/doctors-directory.php
require_once '../includes/db.php';
session_start();

$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll();

$initial_category = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

include '../includes/header.php';
?>

<div class="fade-in-up" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2 style="color: white;">Doctor Directory</h2>
</div>

<div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">
    <!-- Filters Sidebar -->
    <div class="global-glass-container fade-in-up delay-100" style="align-self: start; position: sticky; top: 90px; padding: 2rem; margin-bottom: 0;">
        <h3 style="margin-bottom: 1.5rem; text-align: left; font-size: 1.2rem; color: white;">Filters & Sorting</h3>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Sort By</label>
            <select id="sort-select" class="form-control glass-input">
                <option value="rating">Highest Rated</option>
                <option value="reviews">Most Reviewed</option>
                <option value="recent">Recently Added</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Specialization</label>
            <select id="category-select" class="form-control glass-input">
                <option value="0">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($initial_category == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label class="form-label" style="color: rgba(255,255,255,0.9);">Availability</label>
            <select id="availability-select" class="form-control glass-input">
                <option value="all">All Doctors</option>
                <option value="available">Only Available</option>
                <option value="unavailable">Not Available</option>
            </select>
        </div>
        
        <button id="apply-filters" class="glass-btn" style="width: 100%;">Apply Filters</button>
    </div>

    <!-- Results Area -->
    <div class="fade-in-up delay-200">
        <div id="results-loader" style="display: none; padding: 3rem; text-align: center; color: white;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; margin-bottom: 1rem;"></i>
            <p>Loading doctors...</p>
        </div>
        
        <div id="no-results" class="global-glass-container" style="display: none; padding: 5rem 3rem; text-align: center; color: white; margin-bottom: 0;">
            <i class="fa-solid fa-user-doctor" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.8;"></i>
            <h3>No Doctors Found</h3>
            <p style="color: rgba(255,255,255,0.8);">Try adjusting your filters to find what you're looking for.</p>
        </div>

        <div id="doctors-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Doctors will be injected here via JS -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const applyBtn = document.getElementById('apply-filters');
    const sortSelect = document.getElementById('sort-select');
    const categorySelect = document.getElementById('category-select');
    const availabilitySelect = document.getElementById('availability-select');
    
    const grid = document.getElementById('doctors-grid');
    const loader = document.getElementById('results-loader');
    const noResults = document.getElementById('no-results');

    async function fetchDoctors() {
        grid.innerHTML = '';
        noResults.style.display = 'none';
        loader.style.display = 'block';

        const params = new URLSearchParams({
            sort: sortSelect.value,
            category_id: categorySelect.value,
            availability: availabilitySelect.value
        });

        try {
            const response = await fetch(`api-search-doctors.php?${params.toString()}`);
            const data = await response.json();
            
            loader.style.display = 'none';

            if (data.success && data.doctors.length > 0) {
                renderDoctors(data.doctors);
            } else {
                noResults.style.display = 'block';
            }
        } catch (e) {
            console.error(e);
            loader.style.display = 'none';
            showError('Error', 'Failed to fetch directory results.');
        }
    }

    function renderDoctors(doctors) {
        doctors.forEach(doc => {
            const isAvail = (doc.is_available == 1 || doc.is_available == '1');
            const rating = parseFloat(doc.rating);
            const isTopTier = (rating >= 4.8);
            const isHighRated = (rating >= 4.5);
            
            let starsHtml = '';
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

            const photoUrl = doc.photo ? doc.photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doc.name) + '&background=random';
            
            const card = document.createElement('div');
            card.className = 'global-glass-card' + (isHighRated ? ' top-rated-glow' : '');
            card.style.display = 'flex';
            card.style.flexDirection = 'column';
            card.style.height = '100%';
            card.style.position = 'relative';
            if (!isAvail) card.style.opacity = '0.7';
            if (isHighRated) {
                card.style.border = '1px solid rgba(251, 191, 36, 0.4)';
                card.style.boxShadow = '0 0 20px rgba(251, 191, 36, 0.15)';
            }

            let badgeHtml = '';
            if (isTopTier) {
                badgeHtml = `
                    <div style="position: absolute; top: -10px; right: -10px; z-index: 10;">
                        <span class="glass-badge" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #000; font-weight: 700; border: none; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                            <i class="fa-solid fa-crown"></i> Top-Tier Expert
                        </span>
                    </div>
                `;
            }

            const statusBadge = isAvail 
                ? `<span class="glass-badge" style="background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; padding: 0.3rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-circle-check"></i> Available Now</span>`
                : `<span class="glass-badge" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 0.3rem 0.8rem; font-size: 0.8rem;"><i class="fa-solid fa-circle-xmark"></i> Fully Booked</span>`;

            card.innerHTML = `
                ${badgeHtml}
                <div style="display: flex; gap: 1rem; align-items: start; margin-bottom: 1rem; flex-grow: 1;">
                    <img src="${photoUrl}" alt="${doc.name}" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid ${isHighRated ? 'rgba(251, 191, 36, 0.5)' : 'rgba(255,255,255,0.3)'};">
                    <div>
                        <h4 style="margin: 0 0 0.4rem 0; color: white; font-size: 1.1rem;">${doc.name}</h4>
                        <span class="glass-badge" style="background: rgba(79, 70, 229, 0.4); border-color: rgba(79, 70, 229, 0.6); margin-bottom: 0.5rem; display: inline-block;">${doc.category_name}</span>
                        <div style="font-size: 0.9rem; margin-top: 0.3rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span class="review-stars" style="margin: 0;">${starsHtml}</span>
                            <span style="font-size: 0.81rem; color: white; font-weight: 600;">
                                ${rating.toFixed(1)}
                            </span>
                            <span style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">( ${doc.review_count} Reviews )</span>
                        </div>
                        <p style="margin: 0.8rem 0 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.8); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            ${doc.details || 'Dedicated professional medical practitioner.'}
                        </p>
                    </div>
                </div>
                <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; gap: 0.8rem;">
                    <button onclick="viewReviews(${doc.id})" class="glass-btn" style="width: 100%; margin: 0; font-size: 0.8rem; padding: 0.4rem; background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-comments"></i> View Reviews
                    </button>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                         <span style="font-size: 0.75rem; color: rgba(255,255,255,0.5);">Status:</span>
                         ${statusBadge}
                    </div>
                    ${isAvail 
                        ? `<a href="book-appointment.php?category_id=${doc.category_id}&doctor_id=${doc.id}" class="glass-btn" style="width: 100%; margin: 0; text-align: center; background: ${isHighRated ? 'rgba(251, 191, 36, 0.15)' : ''}; border-color: ${isHighRated ? 'rgba(251, 191, 36, 0.4)' : ''}; font-weight: 600;">Book Appointment</a>`
                        : `<button class="glass-btn" disabled style="width: 100%; margin: 0; opacity: 0.5; cursor: not-allowed;">Not Available</button>`
                    }
                </div>
            `;
            grid.appendChild(card);
        });
    }

    applyBtn.addEventListener('click', fetchDoctors);
    
    // Auto fetch on load
    fetchDoctors();
});
</script>

    }
</script>

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

// Update renderDoctors to include reviews count and button
const originalRenderDoctors = renderDoctors;
window.renderDoctors = function(doctors) {
    doctors.forEach(doc => {
        // Find the card we just created in the original render (if we were wrapping)
        // But it's easier to just modify the innerHTML string in the original function
    });
};
</script>

<?php include '../includes/footer.php'; ?>
