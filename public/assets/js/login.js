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

    // Initialize color rotation
    initColorRotation();

    // Initialize footer effects if footer exists
    initFooterEffects();
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

        // Update button text with enhanced animation
        const btnText = submitBtn.querySelector('span');
        if (btnText) {
            btnText.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xl"></i> <span class="animate-pulse">Giriş yapılıyor...</span>';
        }

        // Enhanced card animation with shake effect
        const card = document.querySelector('.login-card');
        if (card) {
            card.style.transform = 'scale(0.98)';
            card.classList.add('animate-pulse');
            setTimeout(() => {
                card.style.transform = '';
                card.classList.remove('animate-pulse');
            }, 2000);
        }

        // Add loading particles around the button
        createLoadingParticles(submitBtn);

        // Disable all inputs during loading
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            input.disabled = true;
            input.style.opacity = '0.7';
        });
    });
}

// Create loading particles effect
function createLoadingParticles(button) {
    const rect = button.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    for (let i = 0; i < 8; i++) {
        const particle = document.createElement('div');
        particle.className = 'loading-particle';
        particle.style.cssText = `
            position: fixed;
            width: 4px;
            height: 4px;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            left: ${centerX}px;
            top: ${centerY}px;
            animation: loadingParticle 1.5s ease-out ${i * 0.1}s;
        `;

        document.body.appendChild(particle);

        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 2000);
    }

    // Add CSS for loading particle animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes loadingParticle {
            0% {
                transform: scale(0) rotate(0deg);
                opacity: 1;
            }
            50% {
                transform: scale(1) rotate(180deg);
                opacity: 0.8;
            }
            100% {
                transform: scale(0) rotate(360deg) translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Enhanced mouse movement parallax effect
document.addEventListener('mousemove', function(e) {
    if (!document.querySelector('.login-background')) return;

    const particles = document.querySelectorAll('.particle');
    const decorativeElements = document.querySelectorAll('.absolute');
    const mouseX = e.clientX / window.innerWidth;
    const mouseY = e.clientY / window.innerHeight;

    // Particle movement
    particles.forEach((particle, index) => {
        const speed = (index % 3 + 1) * 0.5;
        const x = (mouseX - 0.5) * speed;
        const y = (mouseY - 0.5) * speed;

        particle.style.transform += ` translate(${x}px, ${y}px)`;
    });

    // Decorative elements parallax
    decorativeElements.forEach((element, index) => {
        if (element.classList.contains('blur-xl') || element.classList.contains('blur-lg')) {
            const speed = 0.3;
            const x = (mouseX - 0.5) * speed;
            const y = (mouseY - 0.5) * speed;
            element.style.transform = `translate(${x}px, ${y}px)`;
        }
    });

    // Dynamic background color shift based on mouse position
    const body = document.body;
    const intensity = Math.abs(mouseX - 0.5) + Math.abs(mouseY - 0.5);
    const hue = (mouseX * 360) % 360;
    body.style.filter = `hue-rotate(${hue}deg) brightness(${1 + intensity * 0.2})`;
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

// Color Rotation Function
function initColorRotation() {
    const colors = [
        'from-indigo-600 via-purple-600 to-blue-600',
        'from-cyan-500 via-purple-500 to-pink-500',
        'from-green-500 via-blue-500 to-purple-500',
        'from-yellow-500 via-pink-500 to-red-500',
        'from-teal-500 via-cyan-500 to-blue-500'
    ];

    let colorIndex = 0;

    setInterval(() => {
        const logoContainer = document.querySelector('.logo-container .relative.p-4');
        if (logoContainer) {
            logoContainer.className = `relative p-4 bg-gradient-to-r ${colors[colorIndex]} rounded-full floating animate-spin-slow`;
        }

        // Rotate button colors too
        const button = document.querySelector('.login-btn');
        if (button) {
            const buttonColors = [
                'from-indigo-600 via-purple-600 to-pink-600',
                'from-cyan-600 via-purple-600 to-blue-600',
                'from-green-600 via-blue-600 to-purple-600',
                'from-yellow-600 via-pink-600 to-red-600',
                'from-teal-600 via-cyan-600 to-blue-600'
            ];
            button.className = `login-btn w-full bg-gradient-to-r ${buttonColors[colorIndex]} text-white font-semibold rounded-xl p-4 hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-purple-500/50 relative overflow-hidden group`;
        }

        colorIndex = (colorIndex + 1) % colors.length;
    }, 3000);

    // Add sparkle effect to random elements
    setInterval(() => {
        const sparkles = document.querySelectorAll('.fa-star, .fa-sparkles, .fa-circle');
        if (sparkles.length > 0) {
            const randomSparkle = sparkles[Math.floor(Math.random() * sparkles.length)];
            randomSparkle.style.animation = 'none';
            setTimeout(() => {
                randomSparkle.style.animation = '';
            }, 10);
        }
    }, 2000);
}

// Add sparkle animation
const sparkleStyle = document.createElement('style');
sparkleStyle.textContent = `
    .sparkle-bounce {
        animation: sparkleBounce 0.8s ease-in-out;
    }

    @keyframes sparkleBounce {
        0%, 100% { transform: scale(1) rotate(0deg); }
        25% { transform: scale(1.2) rotate(90deg); }
        50% { transform: scale(0.8) rotate(180deg); }
        75% { transform: scale(1.1) rotate(270deg); }
    }
`;
document.head.appendChild(sparkleStyle);

// Footer Effects Initialization
function initFooterEffects() {
    const footer = document.querySelector('footer');
    if (!footer) return;

    // Initialize footer particles
    initFooterParticles();

    // Initialize footer color rotation
    initFooterColorRotation();

    // Initialize footer interactions
    initFooterInteractions();

    // Initialize footer sparkle effects
    initFooterSparkles();
}

// Footer Particle System
function initFooterParticles() {
    const footerParticles = document.querySelector('.footer-particles');
    if (!footerParticles) return;

    const particleCount = 30;

    for (let i = 0; i < particleCount; i++) {
        createFooterParticle(footerParticles);
    }

    // Create new particles periodically
    setInterval(() => {
        if (document.querySelector('.footer-particles')) {
            createFooterParticle(footerParticles);
        }
    }, 3000);
}

function createFooterParticle(container) {
    const particle = document.createElement('div');
    particle.className = 'footer-particle';

    // Random size and position
    const size = Math.random() * 8 + 4;
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    particle.style.left = Math.random() * 100 + '%';

    // Random animation delay and duration
    particle.style.animationDelay = Math.random() * 15 + 's';
    particle.style.animationDuration = (Math.random() * 10 + 15) + 's';

    container.appendChild(particle);

    // Remove particle after animation
    setTimeout(() => {
        if (particle.parentNode) {
            particle.parentNode.removeChild(particle);
        }
    }, 25000);
}

// Footer Color Rotation
function initFooterColorRotation() {
    const colors = [
        'from-indigo-600 via-purple-600 to-pink-600',
        'from-cyan-500 via-purple-500 to-blue-600',
        'from-green-500 via-blue-500 to-purple-600',
        'from-yellow-500 via-pink-500 to-red-600',
        'from-teal-500 via-cyan-500 to-indigo-600',
        'from-orange-500 via-red-500 to-pink-600'
    ];

    let colorIndex = 0;

    setInterval(() => {
        const footerLogo = document.querySelector('.footer-logo');
        if (footerLogo) {
            footerLogo.className = `relative p-4 bg-gradient-to-r ${colors[colorIndex]} rounded-full footer-logo animate-spin-slow`;
        }

        // Rotate footer background colors
        const footer = document.querySelector('footer');
        if (footer) {
            const bgColors = [
                'from-indigo-600/20 via-purple-600/20 to-pink-600/20',
                'from-cyan-500/20 via-purple-500/20 to-blue-600/20',
                'from-green-500/20 via-blue-500/20 to-purple-600/20',
                'from-yellow-500/20 via-pink-500/20 to-red-600/20',
                'from-teal-500/20 via-cyan-500/20 to-indigo-600/20',
                'from-orange-500/20 via-red-500/20 to-pink-600/20'
            ];
            footer.style.background = `linear-gradient(to right, ${bgColors[colorIndex].split('/')[0].replace('from-', '').replace('via-', '').replace('to-', '')})`;
        }

        colorIndex = (colorIndex + 1) % colors.length;
    }, 4000);
}

// Footer Interactions
function initFooterInteractions() {
    // Contact link hover effects
    const contactLinks = document.querySelectorAll('footer a[href*="t.me"]');
    contactLinks.forEach((link, index) => {
        link.addEventListener('mouseenter', function() {
            // Add bounce effect to icon
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.animation = 'bounce 0.6s ease-in-out';
            }

            // Add particle burst effect
            createContactParticleBurst(this);
        });

        link.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) {
                icon.style.animation = '';
            }
        });
    });

    // Install button effects
    const installBtn = document.getElementById('install-btn');
    if (installBtn) {
        installBtn.addEventListener('click', function() {
            createButtonParticleBurst(this);
        });
    }
}

// Contact particle burst effect
function createContactParticleBurst(element) {
    const rect = element.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    for (let i = 0; i < 6; i++) {
        const particle = document.createElement('div');
        particle.className = 'footer-button-particle';
        particle.style.left = centerX + 'px';
        particle.style.top = centerY + 'px';
        particle.style.setProperty('--tx', (Math.random() - 0.5) * 100 + 'px');
        particle.style.setProperty('--ty', (Math.random() - 0.5) * 100 + 'px');
        particle.style.animationDelay = (i * 0.1) + 's';

        document.body.appendChild(particle);

        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 1000);
    }
}

// Button particle burst for install button
function createButtonParticleBurst(button) {
    const buttonParticles = document.createElement('div');
    buttonParticles.className = 'footer-button-particles';

    for (let i = 0; i < 12; i++) {
        const particle = document.createElement('div');
        particle.className = 'footer-button-particle';
        particle.style.setProperty('--tx', (Math.random() - 0.5) * 200 + 'px');
        particle.style.setProperty('--ty', (Math.random() - 0.5) * 200 + 'px');
        particle.style.animationDelay = (i * 0.05) + 's';

        buttonParticles.appendChild(particle);
    }

    button.appendChild(buttonParticles);

    setTimeout(() => {
        if (buttonParticles.parentNode) {
            buttonParticles.parentNode.removeChild(buttonParticles);
        }
    }, 1000);
}

// Footer sparkles
function initFooterSparkles() {
    const footer = document.querySelector('footer');
    if (!footer) return;

    // Add random sparkle effects
    setInterval(() => {
        const sparkles = footer.querySelectorAll('.footer-sparkle');
        if (sparkles.length > 0) {
            const randomSparkle = sparkles[Math.floor(Math.random() * sparkles.length)];
            randomSparkle.style.animation = 'none';
            setTimeout(() => {
                randomSparkle.style.animation = '';
            }, 10);
        }
    }, 3000);

    // Add mouse interaction for footer
    footer.addEventListener('mousemove', function(e) {
        const rect = footer.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Create temporary sparkle at mouse position
        if (Math.random() > 0.95) { // 5% chance
            const sparkle = document.createElement('div');
            sparkle.className = 'footer-sparkle';
            sparkle.innerHTML = '✨';
            sparkle.style.left = x + 'px';
            sparkle.style.top = y + 'px';
            sparkle.style.fontSize = (Math.random() * 10 + 10) + 'px';

            footer.appendChild(sparkle);

            setTimeout(() => {
                if (sparkle.parentNode) {
                    sparkle.parentNode.removeChild(sparkle);
                }
            }, 2000);
        }
    });
}

// Export functions for potential use
window.LoginAnimations = {
    showSuccessAnimation,
    createParticle,
    initParticles,
    initColorRotation,
    initFooterEffects
};