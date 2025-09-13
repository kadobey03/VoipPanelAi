// Login Page JavaScript Animations and Interactions

document.addEventListener('DOMContentLoaded', function() {
    // Initialize particles
    initParticles();

    // Initialize password toggle
    initPasswordToggle();

    // Initialize form animations
    initFormAnimations();

    // Initialize loading state
    initLoadingState();
});

// Particle Animation Function
function initParticles() {
    const particlesContainer = document.getElementById('particles');
    if (!particlesContainer) return;

    const particleCount = 50;

    for (let i = 0; i < particleCount; i++) {
        createParticle(particlesContainer);
    }

    // Create new particles periodically
    setInterval(() => {
        if (document.getElementById('particles')) {
            createParticle(particlesContainer);
        }
    }, 2000);
}

function createParticle(container) {
    const particle = document.createElement('div');
    particle.className = 'particle';

    // Random size
    const size = Math.random() * 6 + 2;
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';

    // Random position
    particle.style.left = Math.random() * 100 + '%';

    // Random animation delay
    particle.style.animationDelay = Math.random() * 20 + 's';
    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';

    container.appendChild(particle);

    // Remove particle after animation
    setTimeout(() => {
        if (particle.parentNode) {
            particle.parentNode.removeChild(particle);
        }
    }, 20000);
}

// Password Toggle Function
function initPasswordToggle() {
    const toggleBtn = document.getElementById('togglePassword');
    if (!toggleBtn) return;

    const passwordInput = document.querySelector('input[name="password"]');
    if (!passwordInput) return;

    toggleBtn.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;

        const icon = this.querySelector('i');
        if (icon) {
            icon.className = type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
        }

        // Add click animation
        this.style.transform = 'scale(1.2)';
        setTimeout(() => {
            this.style.transform = '';
        }, 200);
    });
}

// Form Animations
function initFormAnimations() {
    const inputs = document.querySelectorAll('.form-input');

    inputs.forEach((input, index) => {
        // Focus animations
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');

            // Animate label
            const label = this.parentElement.previousElementSibling;
            if (label) {
                label.style.transform = 'translateY(-2px)';
                label.style.color = '#6366f1';
            }
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');

            // Reset label
            const label = this.parentElement.previousElementSibling;
            if (label) {
                label.style.transform = '';
                label.style.color = '';
            }
        });

        // Input validation animations
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.classList.add('has-content');
            } else {
                this.classList.remove('has-content');
            }
        });
    });
}

// Loading State for Form Submission
function initLoadingState() {
    const form = document.getElementById('loginForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form || !submitBtn) return;

    form.addEventListener('submit', function(e) {
        // Add loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;

        // Update button text
        const btnText = submitBtn.querySelector('span');
        if (btnText) {
            btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Giriş yapılıyor...';
        }

        // Add subtle animation to card
        const card = document.querySelector('.login-card');
        if (card) {
            card.style.transform = 'scale(0.98)';
            setTimeout(() => {
                card.style.transform = '';
            }, 2000);
        }
    });
}

// Mouse movement parallax effect
document.addEventListener('mousemove', function(e) {
    if (!document.querySelector('.login-background')) return;

    const particles = document.querySelectorAll('.particle');
    const mouseX = e.clientX / window.innerWidth;
    const mouseY = e.clientY / window.innerHeight;

    particles.forEach((particle, index) => {
        const speed = (index % 3 + 1) * 0.5;
        const x = (mouseX - 0.5) * speed;
        const y = (mouseY - 0.5) * speed;

        particle.style.transform += ` translate(${x}px, ${y}px)`;
    });
});

// Intersection Observer for scroll animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe elements that should animate in
document.querySelectorAll('.input-group, .login-btn').forEach(el => {
    observer.observe(el);
});

// Add CSS for animate-in class
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .focused {
        transform: translateY(-1px);
    }

    .has-content + label {
        color: #6366f1 !important;
    }

    .form-input.has-content {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
`;
document.head.appendChild(style);

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter to submit form
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const form = document.getElementById('loginForm');
        if (form) {
            form.dispatchEvent(new Event('submit'));
        }
    }

    // Tab navigation improvements
    if (e.key === 'Tab') {
        const inputs = document.querySelectorAll('.form-input');
        const currentIndex = Array.from(inputs).indexOf(document.activeElement);

        if (currentIndex !== -1) {
            const nextIndex = (currentIndex + 1) % inputs.length;
            setTimeout(() => {
                inputs[nextIndex].focus();
            }, 100);
        }
    }
});

// Add touch support for mobile
if ('ontouchstart' in window) {
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('touchstart', function() {
            this.focus();
        });
    });
}

// Performance optimization - reduce animations on low-end devices
if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
    document.documentElement.classList.add('reduced-motion');
}

// Add loading animation delay for better UX
window.addEventListener('load', function() {
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 100);
});

// Success animation (can be triggered after successful login)
function showSuccessAnimation() {
    const card = document.querySelector('.login-card');
    if (!card) return;

    card.classList.add('success');

    setTimeout(() => {
        card.classList.remove('success');
    }, 1000);
}

// Export functions for potential use
window.LoginAnimations = {
    showSuccessAnimation,
    createParticle,
    initParticles
};