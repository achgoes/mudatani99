/**
 * mudatani99 - Session Activity Sync & 15-Minute Idle Timeout
 * Memastikan sesi tetap aktif selama ada aktivitas pengguna dan memaksa
 * logout ke halaman login jika tidak ada aktivitas selama 15 menit.
 */
(function () {
  const cfg = window.MUDATANI_SESSION_CONFIG;
  if (!cfg || !cfg.timeoutSeconds || !cfg.logoutUrl || !cfg.loginUrl) {
    return;
  }

  const STORAGE_KEY = 'mudatani_last_activity';
  const timeoutMs = Number(cfg.timeoutSeconds) * 1000;
  const remainingMs = Math.max(0, Number(cfg.remainingSeconds ?? cfg.timeoutSeconds) * 1000);
  const userId = String(cfg.userId ?? '');

  const ACTIVITY_WRITE_THROTTLE_MS = 2000;
  const SERVER_PING_THROTTLE_MS = 60000;
  const CHECK_INTERVAL_MS = 5000;

  let lastActivityAt = Date.now() - Math.max(0, timeoutMs - remainingMs);
  let lastStorageWriteAt = 0;
  let lastServerPingAt = Date.now();
  let isCheckingTimeout = false;
  let isRedirecting = false;

  function readStoredActivity() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || String(parsed.userId ?? '') !== userId) {
        return null;
      }
      const ts = Number(parsed.ts);
      return Number.isFinite(ts) ? ts : null;
    } catch (e) {
      return null;
    }
  }

  function writeStoredActivity(ts) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ userId, ts }));
    } catch (e) {
      // Abaikan jika penyimpanan lokal dibatasi browser
    }
  }

  function clearStoredActivity() {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      // Abaikan jika penyimpanan lokal dibatasi browser
    }
  }

  function getEffectiveLastActivityAt() {
    const storedTs = readStoredActivity();
    if (storedTs !== null && storedTs > lastActivityAt) {
      lastActivityAt = storedTs;
    }
    return lastActivityAt;
  }

  function redirectToLogin(url) {
    if (isRedirecting) return;
    isRedirecting = true;
    clearStoredActivity();
    window.location.replace(url);
  }

  async function pingServerActivity() {
    if (!cfg.pingUrl || isRedirecting) return;
    try {
      const res = await fetch(`${cfg.pingUrl}?mode=ping`, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (res.status === 401) {
        redirectToLogin(cfg.loginUrl);
      }
    } catch (e) {
      // Abaikan gangguan jaringan sementara saat ping
    }
  }

  async function verifyAndHandleTimeout() {
    if (isCheckingTimeout || isRedirecting) return;

    const now = Date.now();
    const effectiveLast = getEffectiveLastActivityAt();
    if (now - effectiveLast < timeoutMs) {
      return;
    }

    isCheckingTimeout = true;
    try {
      if (cfg.pingUrl) {
        const res = await fetch(`${cfg.pingUrl}?mode=check`, {
          method: 'GET',
          credentials: 'same-origin',
          cache: 'no-store',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
          const data = await res.json();
          if (data && data.authenticated && Number(data.remainingSeconds) > 0) {
            const syncedAt = Date.now() - Math.max(0, timeoutMs - Number(data.remainingSeconds) * 1000);
            lastActivityAt = Math.max(lastActivityAt, syncedAt);
            writeStoredActivity(lastActivityAt);
            return;
          }
        }
      }
    } catch (e) {
      // Lanjut paksa logout jika sudah melewati batas waktu idle
    } finally {
      isCheckingTimeout = false;
    }

    redirectToLogin(cfg.logoutUrl);
  }

  function recordUserActivity() {
    if (isRedirecting) return;

    const now = Date.now();
    // Jika sesi sudah terlanjur melewati batas 15 menit (mis. laptop baru bangun dari sleep),
    // jangan perpanjang secara sepihak; verifikasi ke server terlebih dahulu.
    if (now - getEffectiveLastActivityAt() >= timeoutMs) {
      verifyAndHandleTimeout();
      return;
    }

    lastActivityAt = now;
    if (now - lastStorageWriteAt >= ACTIVITY_WRITE_THROTTLE_MS) {
      lastStorageWriteAt = now;
      writeStoredActivity(now);
    }

    if (now - lastServerPingAt >= SERVER_PING_THROTTLE_MS) {
      lastServerPingAt = now;
      pingServerActivity();
    }
  }

  // Inisialisasi timestamp aktivitas dari server
  const storedInitial = readStoredActivity();
  if (storedInitial !== null && storedInitial > lastActivityAt) {
    lastActivityAt = storedInitial;
  } else {
    writeStoredActivity(lastActivityAt);
  }

  const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
  activityEvents.forEach((eventName) => {
    window.addEventListener(eventName, recordUserActivity, { passive: true });
  });

  window.addEventListener('storage', (event) => {
    if (event.key !== STORAGE_KEY) return;
    const storedTs = readStoredActivity();
    if (storedTs !== null && storedTs > lastActivityAt) {
      lastActivityAt = storedTs;
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      verifyAndHandleTimeout();
    }
  });

  document.querySelectorAll('.dropdown-logout-btn').forEach((btn) => {
    btn.addEventListener('click', clearStoredActivity);
  });

  setInterval(verifyAndHandleTimeout, CHECK_INTERVAL_MS);
})();
