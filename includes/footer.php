</main> <!-- End of page-content -->

<footer class="footer">
    <div class="container text-center">
        <p>&copy; <?php echo date("Y"); ?> HealthyHub System. All rights reserved.</p>
    </div>
</footer>

<!-- jQuery (helpful for interactions, but we'll try to stick to vanilla JS mostly) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom JS -->
<script src="<?php echo $base_url; ?>/js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Global Intersection Observer for Scroll Animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.10 // Trigger slightly earlier for global elements
    };

    const animateOnScrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Optional: stop observing once it has appeared
                // observer.unobserve(entry.target); 
            }
        });
    }, observerOptions);

    const animatedElements = document.querySelectorAll('.fade-in-up');
    animatedElements.forEach(el => animateOnScrollObserver.observe(el));
});
</script>

</body>
</html>
