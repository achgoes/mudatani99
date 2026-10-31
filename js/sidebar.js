/**
 * mudatani99 - Sidebar Slider & Dropdown Submenu JavaScript (js/sidebar.js)
 * Manages:
 * 1. Sidebar slider open/close (desktop collapse & mobile slide-in drawer)
 * 2. Dropdown submenu accordion with smooth chevron rotation
 * 3. Auto-expanding current active submenu based on URL
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ============================================================
     1. SIDEBAR SLIDER TOGGLE (DESKTOP & MOBILE)
  ============================================================ */
  const sliderTriggers = document.querySelectorAll('.btn-slider-trigger');
  const backdrop = document.getElementById('sidebar-backdrop');

  function toggleSidebarSlider() {
    if (window.innerWidth <= 1024) {
      // Mobile: Toggle drawer overlay
      document.body.classList.toggle('sidebar-mobile-open');
    } else {
      // Desktop: Toggle collapsed / expanded state
      document.body.classList.toggle('sidebar-collapsed');
      
      // Save state to localStorage for persistence
      const isCollapsed = document.body.classList.contains('sidebar-collapsed');
      localStorage.setItem('mudatani_sidebar_collapsed', isCollapsed ? 'true' : 'false');
    }
  }

  function closeMobileSidebar() {
    document.body.classList.remove('sidebar-mobile-open');
  }

  // Bind click events
  sliderTriggers.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      toggleSidebarSlider();
    });
  });

  const btnClose = document.getElementById('btnSidebarClose');
  if (btnClose) {
    btnClose.addEventListener('click', (e) => {
      e.preventDefault();
      closeMobileSidebar();
    });
  }

  if (backdrop) {
    backdrop.addEventListener('click', closeMobileSidebar);
    backdrop.addEventListener('touchstart', closeMobileSidebar, { passive: true });
  }

  // Restore desktop collapsed preference on load
  if (window.innerWidth > 1024) {
    const savedState = localStorage.getItem('mudatani_sidebar_collapsed');
    if (savedState === 'true') {
      document.body.classList.add('sidebar-collapsed');
    }
  }

  /* ============================================================
     2. DROPDOWN SUBMENU ACCORDION
  ============================================================ */
  const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');

  dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const parentItem = toggle.closest('.nav-dropdown-item');
      if (!parentItem) return;

      const isOpen = parentItem.classList.contains('open');

      // Optional: Close other open dropdowns for a clean accordion effect
      document.querySelectorAll('.nav-dropdown-item.open').forEach(item => {
        if (item !== parentItem) {
          item.classList.remove('open');
          const t = item.querySelector('.nav-dropdown-toggle');
          if (t) t.setAttribute('aria-expanded', 'false');
        }
      });

      // Toggle current dropdown
      if (isOpen) {
        parentItem.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      } else {
        parentItem.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
      }
    });
  });

  /* ============================================================
     3. AUTO-EXPAND ACTIVE MENU BASED ON CURRENT FILE
  ============================================================ */
  const currentFile = window.location.pathname.split('/').pop() || 'dashboard.php';
  const currentFull = currentFile + window.location.search;

  let matchedExact = false;
  document.querySelectorAll('.submenu-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href === currentFull) {
      matchedExact = true;
      link.classList.add('active');
      const parentDropdown = link.closest('.nav-dropdown-item');
      if (parentDropdown) {
        parentDropdown.classList.add('open');
        const t = parentDropdown.querySelector('.nav-dropdown-toggle');
        if (t) {
          t.classList.add('active');
          t.setAttribute('aria-expanded', 'true');
        }
      }
    }
  });

  if (!matchedExact) {
    document.querySelectorAll('.submenu-link').forEach(link => {
      const href = link.getAttribute('href');
      if (href && href === currentFile) {
        link.classList.add('active');
        const parentDropdown = link.closest('.nav-dropdown-item');
        if (parentDropdown) {
          parentDropdown.classList.add('open');
          const t = parentDropdown.querySelector('.nav-dropdown-toggle');
          if (t) {
            t.classList.add('active');
            t.setAttribute('aria-expanded', 'true');
          }
        }
      }
    });
  }

  document.querySelectorAll('.nav-item-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href === currentFile) {
      link.classList.add('active');
    }
  });

  /* ============================================================
     4. GLOBAL AUTO TITLE CASE PADA SEMUA INPUT FORM / MODAL
        (Nama, Alamat, Produk, Luas Lahan, Keterangan, dll.)
  ============================================================ */
  function toTitleCaseValue(str) {
    return str.toLowerCase().replace(/(?:^|\s|\/|\(|-)\S/g, (match) => match.toUpperCase());
  }

  document.querySelectorAll('input.modal-input[type="text"], textarea.modal-input').forEach(inp => {
    const fieldName = (inp.getAttribute('name') || '').toLowerCase();
    const fieldId   = (inp.getAttribute('id') || '').toLowerCase();
    // Jangan ubah field username, password, atau nik
    if (fieldName === 'username' || fieldName === 'password' || fieldName === 'nik' || fieldId.includes('password') || fieldId.includes('username') || fieldId.includes('nik')) {
      return;
    }

    inp.addEventListener('input', () => {
      const start = inp.selectionStart;
      const end   = inp.selectionEnd;
      inp.value   = toTitleCaseValue(inp.value);
      if (start !== null && end !== null) {
        inp.setSelectionRange(start, end);
      }
    });
  });

  /* ============================================================
     5. TOPBAR USER PROFILE DROPDOWN (POJOK KANAN ATAS)
  ============================================================ */
  const userDropdownWrapper = document.getElementById('topbarUserDropdownWrapper');
  const userBtn = document.getElementById('topbarUserBtn');

  if (userDropdownWrapper && userBtn) {
    userBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = userDropdownWrapper.classList.contains('open');
      if (isOpen) {
        userDropdownWrapper.classList.remove('open');
        userBtn.setAttribute('aria-expanded', 'false');
      } else {
        userDropdownWrapper.classList.add('open');
        userBtn.setAttribute('aria-expanded', 'true');
      }
    });

  // Close on click outside (click & touch)
  const handleOutsideUserMenu = (e) => {
    if (!userDropdownWrapper.contains(e.target)) {
      userDropdownWrapper.classList.remove('open');
      userBtn.setAttribute('aria-expanded', 'false');
    }
  };
  document.addEventListener('click', handleOutsideUserMenu);
  document.addEventListener('touchend', handleOutsideUserMenu, { passive: true });

  // Close mobile drawer when clicking a navigation link
  document.querySelectorAll('.submenu-link, .nav-item-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 1024) {
        closeMobileSidebar();
      }
    });
  });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (userDropdownWrapper.classList.contains('open')) {
          userDropdownWrapper.classList.remove('open');
          userBtn.setAttribute('aria-expanded', 'false');
          userBtn.focus();
        }
        if (document.body.classList.contains('sidebar-mobile-open')) {
          document.body.classList.remove('sidebar-mobile-open');
        }
      }
    });
  }

});
