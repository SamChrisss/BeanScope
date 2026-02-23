// BeanScope - Main JavaScript

// ========================================
// Home Page Functions
// ========================================

// Smooth scroll for anchor links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Navbar active state management
function initNavbarActiveState() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    // Get stored active nav from localStorage
    const activeNav = localStorage.getItem('activeNav');

    // Set active state on page load
    if (activeNav) {
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-nav') === activeNav) {
                link.classList.add('active');
            }
        });
    }

    // Add click event to all nav links
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Remove active from all links
            navLinks.forEach(l => l.classList.remove('active'));

            // Add active to clicked link
            this.classList.add('active');

            // Store active nav in localStorage
            const navName = this.getAttribute('data-nav');
            localStorage.setItem('activeNav', navName);
        });
    });

    // Detect scroll position and highlight navbar on home page
    if (window.location.pathname === '/' || window.location.pathname.includes('home')) {
        window.addEventListener('scroll', function () {
            const sections = document.querySelectorAll('section[id]');
            const scrollPos = window.scrollY + 100;

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');

                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('data-nav') === sectionId) {
                            link.classList.add('active');
                            localStorage.setItem('activeNav', sectionId);
                        }
                    });
                }
            });
        });
    }
}

// ========================================
// Predict Page Functions
// ========================================

// Image preview function
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
            fileName.textContent = input.files[0].name;
        }

        reader.readAsDataURL(input.files[0]);
    }
}

// ========================================
// Initialize on DOM Ready
// ========================================

document.addEventListener('DOMContentLoaded', function () {
    // Initialize smooth scroll for home page
    initSmoothScroll();

    // Initialize navbar active state
    initNavbarActiveState();
});

// Make functions globally available
window.previewImage = previewImage;
