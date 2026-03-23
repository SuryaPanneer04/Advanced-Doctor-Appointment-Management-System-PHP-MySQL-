<?php
// index.php
require_once 'includes/db.php';
include 'includes/header.php';

// Fetch top 4 reviews
$reviewsStmt = $pdo->query("
    SELECT r.*, u.name as patient_name, d.name as doctor_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN doctors d ON r.doctor_id = d.id 
    ORDER BY r.rating DESC, r.created_at DESC 
    LIMIT 4
");
$topReviews = $reviewsStmt->fetchAll();
?>

<!-- Hero Section with Background Slider -->
<div class="hero">
    <div class="hero-slider" id="heroSlider">
        <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1538108149393-cebb47ac5274?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
        <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
    </div>
    <div class="hero-overlay"></div>
    
    <div class="hero-content fade-in-up">
        <h1>Welcome to HealthyHub</h1>
        <p>Your trusted partner for world-class healthcare, connecting you with top-rated specialists anytime, anywhere.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="user/login.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px;">Patient Login</a>
                <a href="user/register.php" class="btn btn-glass" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px; margin-top: 0;">Register Now</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px;">Go to Dashboard</a>
                <a href="user/doctors-directory.php" class="btn btn-glass" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px; margin-top: 0;">Find a Doctor</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="padding-top: 2rem;">

    <!-- Why We Are The Best -->
    <div class="text-center fade-in-up" style="margin-bottom: 4rem;">
        <h2 class="section-title text-center">Why We Are The Best</h2>
        <p class="text-gray" style="max-width: 600px; margin: 0 auto;">Pioneering the future of digital healthcare with unmatched accessibility, security, and world-class medical expertise.</p>
    </div>

    <div class="pro-grid fade-in-up delay-200">
        <div class="pro-card text-center">
            <i class="fa-solid fa-user-doctor pro-card-bg-icon"></i>
            <div class="icon-box animate-float" style="width: 4rem; height: 4rem; font-size: 2rem;">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <h3 style="font-size: 2rem; color: var(--primary); font-weight: 800; margin-bottom: 0.5rem;">50+</h3>
            <p style="font-weight: 600; color: var(--dark);">Specialist Doctors</p>
            <p class="text-gray" style="font-size: 0.9rem; margin-top: 0.5rem;">Access verified, top-rated professionals across dozens of disciplines.</p>
        </div>
        <div class="pro-card text-center gradient-bg">
            <i class="fa-solid fa-face-smile pro-card-bg-icon"></i>
            <div class="icon-box" style="width: 4rem; height: 4rem; font-size: 2rem;">
                <i class="fa-solid fa-face-smile"></i>
            </div>
            <h3 style="font-size: 2rem; color: white; font-weight: 800; margin-bottom: 0.5rem;">10,000+</h3>
            <p style="font-weight: 600; color: white;">Happy Patients</p>
            <p style="font-size: 0.9rem; margin-top: 0.5rem; opacity: 0.9;">Delivering care that changes lives with seamless booking.</p>
        </div>
        <div class="pro-card text-center">
            <i class="fa-solid fa-shield-halved pro-card-bg-icon"></i>
            <div class="icon-box animate-float" style="width: 4rem; height: 4rem; font-size: 2rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 style="font-size: 2rem; color: var(--primary); font-weight: 800; margin-bottom: 0.5rem;">100%</h3>
            <p style="font-weight: 600; color: var(--dark);">Secure Records</p>
            <p class="text-gray" style="font-size: 0.9rem; margin-top: 0.5rem;">Your medical privacy is guaranteed with our encrypted databases.</p>
        </div>
    </div>

    <!-- About Us Section -->
    <div class="card fade-in-up" style="margin-bottom: 5rem; padding: 0; overflow: hidden;">
        <div style="display: flex; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <img src="https://images.unsplash.com/photo-1551076805-e1869033e561?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="About HealthyHub" style="width: 100%; height: 100%; object-fit: cover; min-height: 400px;">
            </div>
            <div style="flex: 1; min-width: 300px; padding: 4rem;">
                <h2 class="section-title" style="color: var(--dark) !important;">About Us</h2>
                <h4 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 1.2rem;">Empowering Your Health Journey</h4>
                <p style="color: var(--gray) !important; line-height: 1.8; margin-bottom: 1.5rem;">
                    HealthyHub is a leading digital healthcare platform designed to bridge the gap between world-class medical specialists and patients in need. We leverage technology to provide seamless appointment scheduling, transparent reviews, and secure management.
                </p>
                <p style="color: var(--gray) !important; line-height: 1.8; margin-bottom: 2rem;">
                    Whether you need a routine checkup or specialized care, our curated network of doctors is available at your fingertips.
                </p>
                <a href="<?php echo isset($_SESSION['user_id']) ? 'user/doctors-directory.php' : 'user/register.php'; ?>" class="btn btn-primary" style="padding: 0.8rem 2rem; border-radius: 50px;">Get Started Now <i class="fa-solid fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
            </div>
        </div>
    </div>

    <!-- HealthyHub Reviews -->
    <?php if(count($topReviews) > 0): ?>
    <div style="margin-bottom: 5rem;">
        <div class="text-center fade-in-up" style="margin-bottom: 3rem;">
            <h2 class="section-title text-center">Patient Stories</h2>
            <p class="text-gray" style="max-width: 600px; margin: 0 auto;">Don't just take our word for it. Hear from the community of patients whose lives have been positively impacted.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;" class="fade-in-up delay-200">
            <?php foreach($topReviews as $rev): ?>
                <div class="pro-card" style="padding: 2rem;">
                    <i class="fa-solid fa-quote-right" style="position: absolute; right: 2rem; top: 2rem; font-size: 3rem; color: #f1f5f9; z-index: 0;"></i>
                    <div style="position: relative; z-index: 1;">
                        <div class="review-stars" style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.2rem;">
                                <?php 
                                for($i=1; $i<=5; $i++) {
                                    echo ($i <= $rev['rating']) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                }
                                ?>
                            </div>
                            <span style="font-weight: 700; color: var(--dark); font-size: 0.9rem;"><?php echo number_format($rev['rating'], 1); ?></span>
                        </div>
                        <p style="color: var(--dark); font-size: 1.05rem; font-style: italic; margin-bottom: 1.5rem; line-height: 1.6;">
                            "<?php echo htmlspecialchars($rev['comment'] ?: 'Excellent experience. Highly recommended!'); ?>"
                        </p>
                        <div style="display: flex; gap: 1rem; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($rev['patient_name']); ?>&background=random" style="width: 45px; height: 45px; border-radius: 50%;">
                            <div>
                                <h4 style="margin: 0; font-size: 1rem; color: var(--dark);"><?php echo htmlspecialchars($rev['patient_name']); ?></h4>
                                <span style="font-size: 0.85rem; color: var(--gray);">Visited <?php echo htmlspecialchars($rev['doctor_name']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contact Us -->
    <div class="fade-in-up" style="margin-bottom: 5rem;">
        <h2 class="section-title text-center" style="display: block; margin-bottom: 3rem;">Contact Us</h2>
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-wrap: wrap;">
            <!-- Maps Integration Placeholder -->
            <div style="flex: 1; min-width: 300px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 400px; position: relative;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.25280010078!2d-74.14448732731885!3d40.69763123330368!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s" width="100%" height="100%" style="border:0; position: absolute; top:0; left:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <!-- Contact Details -->
            <div style="flex: 1; min-width: 300px; padding: 4rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%); color: white;">
                <h3 style="font-size: 1.8rem; margin-bottom: 2rem;">Get In Touch</h3>
                <ul class="contact-list" style="margin-bottom: 2rem;">
                    <li style="margin-bottom: 1.5rem;">
                        <div class="icon-box" style="margin-bottom: 0; flex-shrink: 0; background: rgba(255,255,255,0.2); color: white;"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <strong>Main Headquarters</strong><br>
                            <span style="opacity: 0.9;">123 Health Avenue, Innovation District, NY 10001</span>
                        </div>
                    </li>
                    <li style="margin-bottom: 1.5rem;">
                        <div class="icon-box" style="margin-bottom: 0; flex-shrink: 0; background: rgba(255,255,255,0.2); color: white;"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <strong>24/7 Support Line</strong><br>
                            <a href="tel:+18001234567" style="opacity: 0.9; text-decoration: none; color: white;">+1 (800) 123-4567</a>
                        </div>
                    </li>
                    <li>
                        <div class="icon-box" style="margin-bottom: 0; flex-shrink: 0; background: rgba(255,255,255,0.2); color: white;"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <strong>Email Inquiries</strong><br>
                            <a href="mailto:support@healthyhub-health.com" style="opacity: 0.9; text-decoration: none; color: white;">support@healthyhub-health.com</a>
                        </div>
                    </li>
                </ul>
                <!-- <a href="#" class="btn btn-glass" onclick="event.preventDefault(); Swal.fire('Live Chat', 'Connecting you to our support team...', 'info');">
                    <i class="fa-solid fa-comments"></i> Start Live Chat
                </a> -->
            </div>
        </div>
    </div>

</div>

<!-- Vanilla JS for Slider and Scroll Animations -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Hero Slider Logic
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 0) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000); // 5 seconds per slide
    }
});
</script>

<?php include 'includes/footer.php'; ?>
