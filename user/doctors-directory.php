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
            const rating = parseFloat(doc.avg_rating);
            let starsHtml = '';
            for(let i=1; i<=5; i++) {
                if (i <= Math.round(rating)) {
                    starsHtml += '<i class="fa-solid fa-star"></i>';
                } else {
                    starsHtml += '<i class="fa-regular fa-star"></i>';
                }
            }

            const photoUrl = doc.photo ? doc.photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doc.name) + '&background=random';
            
            const card = document.createElement('div');
            card.className = 'global-glass-card';
            card.style.display = 'flex';
            card.style.flexDirection = 'column';
            card.style.height = '100%';
            if (!isAvail) card.style.opacity = '0.7';

            card.innerHTML = `
                <div style="display: flex; gap: 1rem; align-items: start; margin-bottom: 1rem; flex-grow: 1;">
                    <img src="${photoUrl}" alt="${doc.name}" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.3);">
                    <div>
                        <h4 style="margin: 0 0 0.4rem 0; color: white; font-size: 1.1rem;">${doc.name}</h4>
                        <span class="glass-badge" style="background: rgba(79, 70, 229, 0.4); border-color: rgba(79, 70, 229, 0.6); margin-bottom: 0.5rem; display: inline-block;">${doc.category_name}</span>
                        <div style="font-size: 0.9rem; margin-top: 0.3rem;">
                            <span class="review-stars" style="margin: 0;">${starsHtml}</span>
                            <span style="font-size: 0.8rem; margin-left: 0.3rem; color: rgba(255,255,255,0.8);">
                                ${rating.toFixed(1)} (${doc.review_count})
                            </span>
                        </div>
                        <p style="margin: 0.8rem 0 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.8); line-height: 1.5;">
                            ${doc.details || 'No details provided.'}
                        </p>
                    </div>
                </div>
                <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.2); text-align: center;">
                    ${isAvail 
                        ? `<a href="book-appointment.php?category_id=${doc.category_id}&doctor_id=${doc.id}" class="glass-btn" style="width: 100%;">Book Appointment</a>`
                        : `<span class="glass-badge" style="background: rgba(220, 38, 38, 0.4); border-color: rgba(220, 38, 38, 0.5); padding: 0.6rem; display: block; font-size: 0.9rem;">Currently Not Available</span>`
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

<?php include '../includes/footer.php'; ?>
