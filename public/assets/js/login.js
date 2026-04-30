// Login Page - Optimized Animations
// All transforms use GPU-accelerated properties only (transform, opacity)

document.addEventListener('DOMContentLoaded', function() {
    initParticles();
    initPasswordToggle();
    initFormAnimations();
    initLoadingState();
});

// ── Particles (max 20, CSS-only transform/opacity) ─────────────────────────
function initParticles() {
    const container = document.getElementById('particles');
    if (!container) return;

    const MAX = 20;
    for (let i = 0; i < MAX; i++) spawnParticle(container);

    setInterval(function() {
        if (container.children.length < MAX) spawnParticle(container);
    }, 3000);
}

function spawnParticle(container) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = Math.random() * 5 + 2;
    p.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' +
        (Math.random() * 100) + '%;animation-delay:' +
        (Math.random() * 15) + 's;animation-duration:' +
        (Math.random() * 8 + 12) + 's;will-change:transform,opacity;';
    container.appendChild(p);
    setTimeout(function() { p.remove(); }, 22000);
}

// ── Password toggle ────────────────────────────────────────────────────────
function initPasswordToggle() {
    const btn = document.getElementById('togglePassword');
    if (!btn) return;
    const input = document.querySelector('input[name="password"]');
    if (!input) return;

    btn.addEventListener('click', function() {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) icon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        btn.style.transform = 'scale(1.2)';
        setTimeout(function() { btn.style.transform = ''; }, 150);
    });
}

// ── Form focus effects ─────────────────────────────────────────────────────
function initFormAnimations() {
    document.querySelectorAll('.form-input').forEach(function(input) {
        input.addEventListener('focus', function() {
            var label = this.closest('.input-group') && this.closest('.input-group').querySelector('label');
            if (label) label.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', function() {
            var label = this.closest('.input-group') && this.closest('.input-group').querySelector('label');
            if (label) label.style.transform = '';
        });
    });
}

// ── Form submit loading state ──────────────────────────────────────────────
function initLoadingState() {
    var form = document.getElementById('loginForm');
    if (!form) return;
    var btn = form.querySelector('button[type="submit"]');
    if (!btn) return;

    form.addEventListener('submit', function() {
        btn.disabled = true;
        btn.style.opacity = '0.8';
        var span = btn.querySelector('span');
        if (span) span.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Giriş yapılıyor...';
        form.querySelectorAll('input').forEach(function(i) { i.disabled = true; });
    });
}

// ── Keyboard shortcut: Ctrl/Cmd+Enter ─────────────────────────────────────
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        var form = document.getElementById('loginForm');
        if (form) form.submit();
    }
});