/**
 * mudatani99 - Login Page Interactions (js/login.js)
 * Manages:
 * 1. 3D Card Tilt Parallax effect on mouse movement
 * 2. Floating Agro Spores Canvas animation
 * 3. Password show/hide visibility toggle
 * 4. Submit button loading spinner indicator during POST
 */

document.addEventListener('DOMContentLoaded', () => {
  try {
    localStorage.removeItem('mudatani_last_activity');
  } catch (e) {
    // Abaikan jika penyimpanan lokal dibatasi browser
  }

  /* ============================================================
     1. 3D CARD TILT PARALLAX EFFECT
  ============================================================ */
  const card = document.querySelector('.auth-card');
  if (card && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const cx = rect.width / 2;
      const cy = rect.height / 2;
      const dx = (x - cx) / cx;
      const dy = (y - cy) / cy;

      card.style.transition = 'transform 0.08s ease-out, box-shadow 0.2s ease';
      card.style.transform = `perspective(1000px) rotateY(${dx * 3.5}deg) rotateX(${-dy * 3.5}deg) translateY(-2px)`;
      card.style.boxShadow = `0 28px 60px -10px rgba(22, 163, 74, 0.2), 0 10px 25px -5px rgba(0, 0, 0, 0.06)`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.transition = 'transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.6s ease';
      card.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) translateY(0)';
      card.style.boxShadow = '0 20px 45px -12px rgba(20, 83, 45, 0.12), 0 10px 25px -5px rgba(0, 0, 0, 0.04)';
    });
  }

  /* ============================================================
     2. FLOATING AGRO PARTICLES CANVAS
  ============================================================ */
  (function initCanvasParticles() {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas || !canvas.parentElement) return;

    const ctx = canvas.getContext('2d');
    let W, H, particles = [];

    function resize() {
      W = canvas.width = canvas.parentElement.offsetWidth;
      H = canvas.height = canvas.parentElement.offsetHeight;
    }

    const COLORS = [
      'rgba(134, 239, 172, ', // agro-300
      'rgba(74, 222, 128, ',  // agro-400
      'rgba(34, 197, 94, ',   // agro-500
      'rgba(250, 204, 21, '   // amber glow
    ];

    function createParticle() {
      return {
        x: Math.random() * W,
        y: Math.random() * H,
        r: Math.random() * 2 + 0.6,
        vx: (Math.random() - 0.5) * 0.35,
        vy: -Math.random() * 0.45 - 0.1, // float upward
        base: COLORS[Math.floor(Math.random() * COLORS.length)],
        alpha: Math.random() * 0.5 + 0.15
      };
    }

    function init() {
      resize();
      particles = Array.from({ length: 40 }, createParticle);
    }

    function draw() {
      ctx.clearRect(0, 0, W, H);
      particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = p.base + p.alpha + ')';
        ctx.fill();

        p.x += p.vx;
        p.y += p.vy;

        if (p.y < -10) p.y = H + 10;
        if (p.x < -10) p.x = W + 10;
        if (p.x > W + 10) p.x = -10;
      });
      requestAnimationFrame(draw);
    }

    window.addEventListener('resize', resize);
    init();
    draw();
  })();

  /* ============================================================
     3. PASSWORD VISIBILITY TOGGLE
  ============================================================ */
  window.togglePasswordVisibility = function() {
    const passInp = document.getElementById('login-password');
    const eyeIcon = document.getElementById('password-eye-icon');
    if (!passInp || !eyeIcon) return;

    if (passInp.type === 'password') {
      passInp.type = 'text';
      eyeIcon.className = 'fa-regular fa-eye-slash';
    } else {
      passInp.type = 'password';
      eyeIcon.className = 'fa-regular fa-eye';
    }
  };

  /* ============================================================
     4. BUTTON SPINNER ON FORM SUBMISSION
  ============================================================ */
  const loginForm = document.getElementById('login-form');
  const btnSubmit = document.getElementById('btn-login-submit');

  if (loginForm && btnSubmit) {
    loginForm.addEventListener('submit', () => {
      // Show loading spinner while native POST request is sent to PHP
      btnSubmit.classList.add('loading');
    });
  }

});
