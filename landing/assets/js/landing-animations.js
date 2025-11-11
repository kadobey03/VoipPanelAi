/**
 * Landing Page Animation Engine
 * Handles all animations, particle systems, and interactive effects
 */

class LandingAnimationEngine {
    constructor() {
        this.observers = new Map();
        this.animations = new Map();
        this.particleCanvas = null;
        this.particleSystem = null;
        this.isInitialized = false;
        
        this.init();
    }
    
    /**
     * Initialize the animation engine
     */
    init() {
        if (this.isInitialized) return;
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeComponents();
            });
        } else {
            this.initializeComponents();
        }
    }
    
    initializeComponents() {
        this.initScrollAnimations();
        this.initParticleSystem();
        this.initHoverAnimations();
        this.initLoadingAnimations();
        this.initCounterAnimations();
        this.initTypewriterEffect();
        this.isInitialized = true;
    }
    
    /**
     * Initialize scroll-triggered animations using Intersection Observer
     */
    initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.triggerAnimation(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe elements with animation classes
        document.querySelectorAll('[data-animate]').forEach(el => {
            observer.observe(el);
        });
        
        this.observers.set('scroll', observer);
    }
    
    /**
     * Trigger animation based on element's data-animate attribute
     */
    triggerAnimation(element) {
        const animationType = element.dataset.animate;
        const delay = parseInt(element.dataset.delay) || 0;
        
        setTimeout(() => {
            switch(animationType) {
                case 'slide-up':
                    element.classList.add('animate-slide-up');
                    break;
                case 'fade-in':
                    element.classList.add('animate-fade-in');
                    break;
                case 'scale-in':
                    element.style.transform = 'scale(1)';
                    element.style.opacity = '1';
                    break;
                case 'bounce-in':
                    element.classList.add('animate-bounce-gentle');
                    break;
            }
            element.classList.add('animation-complete');
        }, delay);
    }
    
    /**
     * Initialize particle system for background animation
     */
    initParticleSystem() {
        const canvas = document.getElementById('particle-canvas');
        if (!canvas) return;
        
        this.particleCanvas = canvas;
        this.particleSystem = new ParticleSystem(canvas);
        this.particleSystem.start();
    }
    
    /**
     * Initialize hover animations for interactive elements
     */
    initHoverAnimations() {
        // Feature cards hover effects
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.addHoverGlow(card);
                this.scaleElement(card, 1.05);
            });
            
            card.addEventListener('mouseleave', () => {
                this.removeHoverGlow(card);
                this.scaleElement(card, 1);
            });
        });
        
        // Button hover effects
        document.querySelectorAll('.btn-primary, .btn-secondary').forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                this.addButtonGlow(btn);
            });
            
            btn.addEventListener('mouseleave', () => {
                this.removeButtonGlow(btn);
            });
            
            btn.addEventListener('click', () => {
                this.buttonClickEffect(btn);
            });
        });
        
        // Icon hover animations
        document.querySelectorAll('.feature-icon').forEach(icon => {
            icon.addEventListener('mouseenter', () => {
                icon.style.transform = 'scale(1.1) rotate(5deg)';
                icon.style.transition = 'transform 0.3s ease';
            });
            
            icon.addEventListener('mouseleave', () => {
                icon.style.transform = 'scale(1) rotate(0deg)';
            });
        });
    }
    
    /**
     * Initialize loading animations
     */
    initLoadingAnimations() {
        // Simulate progressive loading of sections
        const sections = document.querySelectorAll('.landing-section');
        sections.forEach((section, index) => {
            setTimeout(() => {
                section.classList.add('animate-fade-in');
            }, index * 200);
        });
    }
    
    /**
     * Initialize counter animations for statistics
     */
    initCounterAnimations() {
        document.querySelectorAll('[data-counter]').forEach(counter => {
            const target = parseInt(counter.dataset.counter);
            const duration = 2000; // 2 seconds
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.animateCounter(counter, target, duration);
                        observer.unobserve(counter);
                    }
                });
            }, { threshold: 0.5 });
            
            observer.observe(counter);
        });
    }
    
    /**
     * Initialize typewriter effect for hero text
     */
    initTypewriterEffect() {
        const typewriterElement = document.querySelector('[data-typewriter]');
        if (!typewriterElement) return;
        
        const texts = typewriterElement.dataset.typewriter.split('|');
        let currentIndex = 0;
        
        const typeText = () => {
            const currentText = texts[currentIndex];
            let charIndex = 0;
            
            typewriterElement.textContent = '';
            
            const typeChar = () => {
                if (charIndex < currentText.length) {
                    typewriterElement.textContent += currentText[charIndex];
                    charIndex++;
                    setTimeout(typeChar, 100);
                } else {
                    setTimeout(() => {
                        currentIndex = (currentIndex + 1) % texts.length;
                        setTimeout(typeText, 2000);
                    }, 3000);
                }
            };
            
            typeChar();
        };
        
        typeText();
    }
    
    /**
     * Helper methods for animations
     */
    addHoverGlow(element) {
        element.style.boxShadow = '0 0 30px rgba(139, 92, 246, 0.4)';
        element.style.transition = 'all 0.3s ease';
    }
    
    removeHoverGlow(element) {
        element.style.boxShadow = '';
    }
    
    scaleElement(element, scale) {
        element.style.transform = `scale(${scale})`;
        element.style.transition = 'transform 0.3s ease';
    }
    
    addButtonGlow(button) {
        button.style.boxShadow = '0 0 20px rgba(139, 92, 246, 0.5)';
        button.style.transform = 'scale(1.05)';
    }
    
    removeButtonGlow(button) {
        button.style.boxShadow = '';
        button.style.transform = 'scale(1)';
    }
    
    buttonClickEffect(button) {
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1.05)';
        }, 100);
    }
    
    animateCounter(element, target, duration) {
        const start = performance.now();
        const startValue = 0;
        
        const animate = (currentTime) => {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.floor(startValue + (target - startValue) * progress);
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }
}

/**
 * Particle System for Background Animation
 */
class ParticleSystem {
    constructor(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.particles = [];
        this.particleCount = window.innerWidth < 768 ? 25 : 50;
        this.animationId = null;
        
        this.resizeCanvas();
        this.createParticles();
        
        window.addEventListener('resize', () => {
            this.resizeCanvas();
            this.createParticles();
        });
    }
    
    resizeCanvas() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }
    
    createParticles() {
        this.particles = [];
        
        for (let i = 0; i < this.particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                size: Math.random() * 3 + 1,
                speedX: (Math.random() - 0.5) * 1.5,
                speedY: (Math.random() - 0.5) * 1.5,
                opacity: Math.random() * 0.5 + 0.2,
                color: this.getRandomColor()
            });
        }
    }
    
    getRandomColor() {
        const colors = [
            'rgba(139, 92, 246, 0.7)',  // Purple
            'rgba(124, 58, 237, 0.7)',  // Purple-600
            'rgba(99, 102, 241, 0.7)',  // Indigo
            'rgba(168, 85, 247, 0.7)',  // Violet
            'rgba(236, 72, 153, 0.7)'   // Pink
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
    
    updateParticles() {
        this.particles.forEach(particle => {
            particle.x += particle.speedX;
            particle.y += particle.speedY;
            
            // Wrap around edges
            if (particle.x < 0) particle.x = this.canvas.width;
            if (particle.x > this.canvas.width) particle.x = 0;
            if (particle.y < 0) particle.y = this.canvas.height;
            if (particle.y > this.canvas.height) particle.y = 0;
            
            // Slightly change opacity for twinkling effect
            particle.opacity += (Math.random() - 0.5) * 0.02;
            particle.opacity = Math.max(0.1, Math.min(0.8, particle.opacity));
        });
    }
    
    drawParticles() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.particles.forEach(particle => {
            this.ctx.save();
            this.ctx.globalAlpha = particle.opacity;
            this.ctx.fillStyle = particle.color;
            this.ctx.shadowBlur = 10;
            this.ctx.shadowColor = particle.color;
            
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            this.ctx.fill();
            
            this.ctx.restore();
        });
        
        // Draw connections between nearby particles
        if (window.innerWidth > 768) {
            this.drawConnections();
        }
    }
    
    drawConnections() {
        this.particles.forEach((particle1, index) => {
            this.particles.slice(index + 1).forEach(particle2 => {
                const distance = Math.sqrt(
                    Math.pow(particle1.x - particle2.x, 2) + 
                    Math.pow(particle1.y - particle2.y, 2)
                );
                
                if (distance < 100) {
                    this.ctx.save();
                    this.ctx.globalAlpha = (100 - distance) / 100 * 0.2;
                    this.ctx.strokeStyle = 'rgba(139, 92, 246, 0.3)';
                    this.ctx.lineWidth = 1;
                    
                    this.ctx.beginPath();
                    this.ctx.moveTo(particle1.x, particle1.y);
                    this.ctx.lineTo(particle2.x, particle2.y);
                    this.ctx.stroke();
                    
                    this.ctx.restore();
                }
            });
        });
    }
    
    animate() {
        this.updateParticles();
        this.drawParticles();
        this.animationId = requestAnimationFrame(() => this.animate());
    }
    
    start() {
        this.animate();
    }
    
    stop() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }
    }
}

/**
 * Smooth Scroll Handler
 */
class SmoothScroll {
    constructor() {
        this.initSmoothScroll();
    }
    
    initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetId = anchor.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    const headerHeight = document.getElementById('main-header')?.offsetHeight || 0;
                    const targetPosition = targetElement.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
}

/**
 * Testimonials Carousel
 */
class TestimonialsCarousel {
    constructor() {
        this.track = document.getElementById('testimonials-track');
        this.prevBtn = document.getElementById('prev-testimonial');
        this.nextBtn = document.getElementById('next-testimonial');
        this.currentIndex = 0;
        this.itemWidth = window.innerWidth < 768 ? 100 : (window.innerWidth < 1024 ? 50 : 33.333);
        this.maxIndex = 2; // 3 testimonials
        this.autoPlayInterval = null;
        
        this.init();
    }
    
    init() {
        if (!this.track || !this.prevBtn || !this.nextBtn) return;
        
        this.prevBtn.addEventListener('click', () => {
            this.prev();
            this.resetAutoPlay();
        });
        
        this.nextBtn.addEventListener('click', () => {
            this.next();
            this.resetAutoPlay();
        });
        
        // Auto-play
        this.startAutoPlay();
        
        // Pause on hover
        this.track.addEventListener('mouseenter', () => this.stopAutoPlay());
        this.track.addEventListener('mouseleave', () => this.startAutoPlay());
        
        // Handle resize
        window.addEventListener('resize', () => {
            this.itemWidth = window.innerWidth < 768 ? 100 : (window.innerWidth < 1024 ? 50 : 33.333);
            this.updatePosition();
        });
    }
    
    prev() {
        this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.maxIndex;
        this.updatePosition();
    }
    
    next() {
        this.currentIndex = this.currentIndex < this.maxIndex ? this.currentIndex + 1 : 0;
        this.updatePosition();
    }
    
    updatePosition() {
        if (!this.track) return;
        const translateX = -this.currentIndex * this.itemWidth;
        this.track.style.transform = `translateX(${translateX}%)`;
    }
    
    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => this.next(), 5000);
    }
    
    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
    
    resetAutoPlay() {
        this.stopAutoPlay();
        this.startAutoPlay();
    }
}

/**
 * Mobile Menu Handler
 */
class MobileMenu {
    constructor() {
        this.menuBtn = document.getElementById('mobile-menu-btn');
        this.mobileMenu = document.getElementById('mobile-menu');
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        if (!this.menuBtn || !this.mobileMenu) return;
        
        this.menuBtn.addEventListener('click', () => this.toggle());
        
        // Close menu when clicking on links
        this.mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => this.close());
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.menuBtn.contains(e.target) && !this.mobileMenu.contains(e.target)) {
                this.close();
            }
        });
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        this.mobileMenu.style.opacity = '1';
        this.mobileMenu.style.visibility = 'visible';
        this.mobileMenu.style.transform = 'translateY(0)';
        this.isOpen = true;
    }
    
    close() {
        this.mobileMenu.style.opacity = '0';
        this.mobileMenu.style.visibility = 'hidden';
        this.mobileMenu.style.transform = 'translateY(-10px)';
        this.isOpen = false;
    }
}

/**
 * Header Scroll Effect
 */
class HeaderScrollEffect {
    constructor() {
        this.header = document.getElementById('main-header');
        this.lastScrollY = 0;
        
        this.init();
    }
    
    init() {
        if (!this.header) return;
        
        window.addEventListener('scroll', () => this.handleScroll());
    }
    
    handleScroll() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 50) {
            this.header.style.background = 'rgba(139, 92, 246, 0.95)';
            this.header.style.backdropFilter = 'blur(20px)';
            this.header.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
        } else {
            this.header.style.background = 'rgba(255, 255, 255, 0.1)';
            this.header.style.backdropFilter = 'blur(10px)';
            this.header.style.boxShadow = 'none';
        }
        
        this.lastScrollY = currentScrollY;
    }
}

// Initialize all components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animation engine
    const animationEngine = new LandingAnimationEngine();
    
    // Initialize other components
    const smoothScroll = new SmoothScroll();
    const testimonialsCarousel = new TestimonialsCarousel();
    const mobileMenu = new MobileMenu();
    const headerScrollEffect = new HeaderScrollEffect();
    
    // Loading animation for page
    window.addEventListener('load', function() {
        document.body.classList.add('loaded');
    });
});