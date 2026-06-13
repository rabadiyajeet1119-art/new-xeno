/**
 * Transport & Logistics Management System
 * Modern Interactive JavaScript - Complete Revamp
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // 1. PARTICLE BACKGROUND
    // =========================================
    function createParticles() {
        const container = document.createElement('div');
        container.className = 'particles-bg';
        document.body.prepend(container);

        const colors = ['#6366f1', '#818cf8', '#06b6d4', '#a5f3fc'];
        const count = window.innerWidth < 768 ? 12 : 22;

        for (let i = 0; i < count; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const size = Math.random() * 6 + 2;
            const left = Math.random() * 100;
            const duration = Math.random() * 18 + 12;
            const delay = Math.random() * 10;
            const color = colors[Math.floor(Math.random() * colors.length)];

            p.style.cssText = `
                width:${size}px; height:${size}px;
                left:${left}%;
                background:${color};
                animation-duration:${duration}s;
                animation-delay:-${delay}s;
            `;
            container.appendChild(p);
        }
    }
    createParticles();

    // =========================================
    // 2. NAVBAR SCROLL EFFECT
    // =========================================
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const onScroll = () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // =========================================
    // 3. SCROLL-REVEAL ANIMATIONS
    // =========================================
    const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    if (revealEls.length > 0) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    // Stagger if inside stagger-children
                    const parent = entry.target.closest('.stagger-children');
                    let delay = 0;
                    if (parent) {
                        const siblings = [...parent.children];
                        delay = siblings.indexOf(entry.target) * 0.1;
                    }
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, delay * 1000);
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        revealEls.forEach(el => revealObserver.observe(el));
    }

    // =========================================
    // 4. ANIMATED COUNTER (Stats)
    // =========================================
    function animateCounter(el, target, duration = 1800) {
        const start = 0;
        const startTime = performance.now();
        const suffix = el.dataset.suffix || '';

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.floor(eased * target);
            el.textContent = value.toLocaleString() + suffix;
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    const statNumbers = document.querySelectorAll('.stat-number[data-target]');
    if (statNumbers.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.target, 10);
                    animateCounter(el, target);
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(el => counterObserver.observe(el));
    }

    // =========================================
    // 5. MOUSE PARALLAX ON HERO
    // =========================================
    const heroVisual = document.querySelector('.hero-truck-wrapper');
    if (heroVisual) {
        document.addEventListener('mousemove', (e) => {
            const { innerWidth, innerHeight } = window;
            const x = (e.clientX / innerWidth - 0.5) * 12;
            const y = (e.clientY / innerHeight - 0.5) * 10;
            heroVisual.style.transform = `translate(${x}px, ${y}px)`;
        });
    }

    // =========================================
    // 6. RIPPLE EFFECT ON BUTTONS
    // =========================================
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('span');
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ripple.style.cssText = `
                position:absolute; border-radius:50%; pointer-events:none;
                width:10px; height:10px;
                left:${x - 5}px; top:${y - 5}px;
                background:rgba(255,255,255,0.4);
                animation: rippleAnim 0.6s ease-out forwards;
            `;
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 700);
        });
    });

    // Ripple keyframe
    if (!document.getElementById('rippleStyle')) {
        const style = document.createElement('style');
        style.id = 'rippleStyle';
        style.textContent = `
            @keyframes rippleAnim {
                to { width:150px; height:150px; left:calc(50% - 75px); top:calc(50% - 75px); opacity:0; }
            }
        `;
        document.head.appendChild(style);
    }

    // =========================================
    // 7. FEATURE CARDS - TILT EFFECT
    // =========================================
    document.querySelectorAll('.feature-card, .service-card, .dashboard-card').forEach(card => {
        card.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width - 0.5) * 12;
            const y = ((e.clientY - rect.top) / rect.height - 0.5) * -12;
            this.style.transform = `perspective(800px) rotateY(${x}deg) rotateX(${y}deg) translateY(-6px)`;
        });
        card.addEventListener('mouseleave', function () {
            this.style.transform = '';
        });
    });

    // =========================================
    // 8. TYPING EFFECT ON HERO TITLE
    // =========================================
    const heroTitle = document.querySelector('.hero-title .typed-text');
    if (heroTitle) {
        const words = heroTitle.dataset.words ? heroTitle.dataset.words.split('|') : [];
        if (words.length > 0) {
            let wi = 0, ci = 0, deleting = false;
            function type() {
                const word = words[wi];
                if (deleting) {
                    heroTitle.textContent = word.slice(0, --ci);
                } else {
                    heroTitle.textContent = word.slice(0, ++ci);
                }
                let delay = deleting ? 60 : 110;
                if (!deleting && ci === word.length) {
                    delay = 1800;
                    deleting = true;
                } else if (deleting && ci === 0) {
                    deleting = false;
                    wi = (wi + 1) % words.length;
                    delay = 400;
                }
                setTimeout(type, delay);
            }
            type();
        }
    }

    // =========================================
    // 9. BACK TO TOP BUTTON
    // =========================================
    const backToTop = document.createElement('button');
    backToTop.id = 'backToTop';
    backToTop.title = 'Back to top';
    backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTop.style.cssText = `
        position:fixed; bottom:30px; right:30px; z-index:9999;
        width:48px; height:48px; border-radius:50%;
        background:linear-gradient(135deg,#6366f1,#06b6d4);
        color:#fff; border:none; cursor:pointer;
        box-shadow:0 4px 20px rgba(99,102,241,0.4);
        display:none; align-items:center; justify-content:center;
        font-size:1rem; transition:all 0.3s ease;
        opacity:0; transform:translateY(10px);
    `;
    document.body.appendChild(backToTop);

    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            backToTop.style.display = 'flex';
            setTimeout(() => {
                backToTop.style.opacity = '1';
                backToTop.style.transform = 'translateY(0)';
            }, 10);
        } else {
            backToTop.style.opacity = '0';
            backToTop.style.transform = 'translateY(10px)';
            setTimeout(() => { backToTop.style.display = 'none'; }, 300);
        }
    }, { passive: true });

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    backToTop.addEventListener('mouseenter', () => {
        backToTop.style.transform = 'translateY(-3px) scale(1.1)';
        backToTop.style.boxShadow = '0 8px 30px rgba(99,102,241,0.6)';
    });
    backToTop.addEventListener('mouseleave', () => {
        backToTop.style.transform = 'translateY(0) scale(1)';
        backToTop.style.boxShadow = '0 4px 20px rgba(99,102,241,0.4)';
    });

    // =========================================
    // 10. FORM VALIDATION (kept from original)
    // =========================================
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            let isValid = true;

            const name = document.getElementById('name');
            if (name && name.value.trim().length < 3) {
                name.classList.add('is-invalid'); isValid = false;
            } else if (name) {
                name.classList.remove('is-invalid'); name.classList.add('is-valid');
            }

            const email = document.getElementById('email');
            if (email) {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email.value)) {
                    email.classList.add('is-invalid'); isValid = false;
                } else {
                    email.classList.remove('is-invalid'); email.classList.add('is-valid');
                }
            }

            const phone = document.getElementById('phone');
            if (phone) {
                const phoneValue = phone.value.replace(/[^0-9]/g, '');
                if (!/^[0-9]{10,15}$/.test(phoneValue)) {
                    phone.classList.add('is-invalid'); isValid = false;
                } else {
                    phone.classList.remove('is-invalid'); phone.classList.add('is-valid');
                }
            }

            const password = document.getElementById('password');
            if (password) {
                const v = password.value;
                if (v.length < 6 || !/[A-Z]/.test(v) || !/[a-z]/.test(v) || !/[0-9]/.test(v)) {
                    password.classList.add('is-invalid'); isValid = false;
                } else {
                    password.classList.remove('is-invalid'); password.classList.add('is-valid');
                }
            }

            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword && password) {
                if (confirmPassword.value !== password.value) {
                    confirmPassword.classList.add('is-invalid'); isValid = false;
                } else {
                    confirmPassword.classList.remove('is-invalid'); confirmPassword.classList.add('is-valid');
                }
            }

            const terms = document.getElementById('terms');
            if (terms && !terms.checked) {
                terms.classList.add('is-invalid'); isValid = false;
            } else if (terms) {
                terms.classList.remove('is-invalid');
            }

            if (!isValid) { event.preventDefault(); event.stopPropagation(); }
        });
    }

    // =========================================
    // 11. PASSWORD TOGGLE
    // =========================================
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Legacy id="togglePassword"
    document.querySelectorAll('#togglePassword').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // =========================================
    // 12. PHONE & DATE HELPERS
    // =========================================
    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);
        });
    });

    document.querySelectorAll('input#delivery_date, input[name="delivery_date"]').forEach(input => {
        input.setAttribute('min', new Date().toISOString().split('T')[0]);
    });

    document.querySelectorAll('input[name="weight"]').forEach(input => {
        input.addEventListener('input', function () {
            if (parseFloat(this.value) < 0) this.value = 0;
        });
    });

    // =========================================
    // 13. CHARACTER COUNTER FOR TEXTAREAS
    // =========================================
    document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
        const max = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted';
        counter.textContent = `0 / ${max} characters`;
        textarea.parentNode.appendChild(counter);
        textarea.addEventListener('input', function () {
            const len = this.value.length;
            counter.textContent = `${len} / ${max} characters`;
            counter.style.color = len >= max ? 'var(--danger)' : '';
        });
    });

    // =========================================
    // 14. CONFIRM DELETE
    // =========================================
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // =========================================
    // 15. SUBMIT BUTTON LOADER
    // =========================================
    document.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            }
        });
    });

    // =========================================
    // 16. TOOLTIPS (Bootstrap 5)
    // =========================================
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // =========================================
    // 17. SMOOTH ANCHOR SCROLL
    // =========================================
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // =========================================
    // 18. ALERT AUTO-DISMISS
    // =========================================
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            try { new bootstrap.Alert(alert).close(); } catch (e) { }
        }, 5000);
    });

    // =========================================
    // 19. ACTIVE NAV HIGHLIGHT (Add reveal to sections)
    // =========================================
    document.querySelectorAll('section').forEach(section => {
        section.classList.add('reveal');
    });

    // =========================================
    // 20. PRINT BUTTON
    // =========================================
    document.querySelectorAll('.btn-print').forEach(btn => {
        btn.addEventListener('click', () => window.print());
    });

    // =========================================
    // 21. CURSOR GLOW TRAIL (Desktop only)
    // =========================================
    if (window.innerWidth > 992) {
        const glow = document.createElement('div');
        glow.style.cssText = `
            position:fixed; width:300px; height:300px;
            background:radial-gradient(circle, rgba(99,102,241,0.06) 0%, transparent 70%);
            border-radius:50%; pointer-events:none; z-index:0;
            transition:transform 0.15s ease;
            transform:translate(-50%, -50%);
        `;
        document.body.appendChild(glow);

        document.addEventListener('mousemove', e => {
            glow.style.left = e.clientX + 'px';
            glow.style.top = e.clientY + 'px';
        }, { passive: true });
    }

    console.log('%c🚛 Transport & Logistics — Modern UI Loaded!', 'color:#6366f1;font-size:14px;font-weight:700;');
});

// ==========================================
// UTILITY FUNCTIONS (kept from original)
// ==========================================

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
}

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function ajaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                xhr.status === 200 ? resolve(xhr.response) : reject(xhr.statusText);
            }
        };
        xhr.onerror = () => reject(xhr.statusText);
        xhr.send(data ? new URLSearchParams(data).toString() : null);
    });
}
