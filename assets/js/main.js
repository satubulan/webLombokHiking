// =======================
// Mobile Menu Toggle
// =======================
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function () {
            navLinks.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });
    }

    // =======================
    // Testimonial Slider
    // =======================
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');
    const prevButton = document.querySelector('.prev-testimonial');
    const nextButton = document.querySelector('.next-testimonial');

    if (slides.length > 0 && dots.length > 0) {
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        if (prevButton && nextButton) {
            prevButton.addEventListener('click', () => {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });

            nextButton.addEventListener('click', () => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });
        }

        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);
    }

    // =======================
    // Sidebar Toggle (Admin)
    // =======================
    const layout = document.querySelector('.admin-layout');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const toggleMenu = document.querySelector('.toggle-menu');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            layout.classList.toggle('collapsed');
        });
    }

    if (toggleMenu) {
        toggleMenu.addEventListener('click', () => {
            layout.classList.toggle('mobile-open');
        });
    }

    document.addEventListener('click', function (event) {
        if (
            layout && layout.classList.contains('mobile-open') &&
            !event.target.closest('.admin-sidebar') &&
            !event.target.closest('.toggle-menu')
        ) {
            layout.classList.remove('mobile-open');
        }
    });

    // Active link highlighting
    const currentPath = window.location.pathname;
    const navLinksList = document.querySelectorAll('.nav-link');
    navLinksList.forEach(link => {
        if (currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

    // =======================
    // Notification Dropdown (future)
    // =======================
    const notificationBtn = document.querySelector('.notification-button');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            // Placeholder logic for notification dropdown
        });
    }

    // =======================
    // User Menu Dropdown (future)
    // =======================
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
        userMenu.addEventListener('click', () => {
            // Placeholder logic for user menu dropdown
        });
    }

    // =======================
    // Fade In Animation
    // =======================
    const fadeInElements = document.querySelectorAll('.fade-in');
    const fadeInObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                fadeInObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    fadeInElements.forEach(element => fadeInObserver.observe(element));
    
    // =======================
    // Show/Hide Password (mata)
    // =======================
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });
});

// =======================
// Smooth Scroll to Anchor
// =======================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// =======================
// Basic Form Validation
// =======================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    let isValid = true;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            toggleErrorMessage(input, true);
        } else {
            input.classList.remove('error');
            toggleErrorMessage(input, false);
        }
    });

    const emailInput = form.querySelector('input[type="email"]');
    if (emailInput && emailInput.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            isValid = false;
            emailInput.classList.add('error');
        }
    }

    return isValid;
}

function toggleErrorMessage(input, show) {
    const errorMsgId = input.id + '-error';
    const errorMsg = document.getElementById(errorMsgId);
    if (errorMsg) {
        errorMsg.style.display = show ? 'block' : 'none';
    }
}
