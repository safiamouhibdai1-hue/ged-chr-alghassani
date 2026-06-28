// JavaScript global du GED Médicale CHR Al Ghassani

// Désactive le bouton submit 50ms après le clic pour éviter les doublons en cas de double-clic rapide
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('[type=submit]');
      if (btn) {
        setTimeout(function () {
          btn.disabled      = true;
          btn.style.opacity = '0.7';
          btn.style.cursor  = 'not-allowed';
        }, 50);
      }
    });
  });
});

// Messages flash : fermeture manuelle ou automatique après 5s
document.addEventListener('DOMContentLoaded', function () {
  var flashMessages = document.querySelectorAll('[data-flash]');
  flashMessages.forEach(function (el) {
    // Bouton fermeture
    var closeBtn = el.querySelector('[data-flash-close]');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        dismissFlash(el);
      });
    }
    // Auto-fermeture après 5s
    setTimeout(function () {
      dismissFlash(el);
    }, 5000);
  });

  function dismissFlash(el) {
    el.style.transition = 'opacity .4s ease, transform .4s ease';
    el.style.opacity    = '0';
    el.style.transform  = 'translateY(-8px)';
    setTimeout(function () { el.remove(); }, 400);
  }
});

// Confirmation avant soumission pour les formulaires/boutons avec data-confirm="message"
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var message = form.getAttribute('data-confirm') || 'Êtes-vous sûr ?';
      if (!confirm(message)) {
        e.preventDefault();
        return false;
      }
    });
  });

  // Boutons avec data-confirm (hors formulaire)
  document.querySelectorAll('[data-confirm]:not(form)').forEach(function (el) {
    el.addEventListener('click', function (e) {
      var message = el.getAttribute('data-confirm') || 'Êtes-vous sûr ?';
      if (!confirm(message)) {
        e.preventDefault();
        return false;
      }
    });
  });
});

// Toggle sidebar mobile (bouton id="sidebarToggle")
document.addEventListener('DOMContentLoaded', function () {
  var toggleBtn = document.getElementById('sidebarToggle');
  var sidebar   = document.querySelector('.sidebar');
  var overlay   = document.getElementById('sidebarOverlay');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('sidebar-open');
      if (overlay) overlay.classList.toggle('active');
    });
  }

  if (overlay) {
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('sidebar-open');
      overlay.classList.remove('active');
    });
  }
});

// Tooltip simple sur les éléments avec data-tooltip="texte"
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-tooltip]').forEach(function (el) {
    el.style.position = 'relative';
    el.style.cursor   = 'help';

    el.addEventListener('mouseenter', function () {
      var tip = document.createElement('div');
      tip.className   = 'ged-tooltip';
      tip.textContent = el.getAttribute('data-tooltip');
      tip.style.cssText = [
        'position:absolute',
        'bottom:calc(100% + 6px)',
        'left:50%',
        'transform:translateX(-50%)',
        'background:#1E2A4A',
        'color:#fff',
        'font-size:11px',
        'padding:5px 10px',
        'border-radius:6px',
        'white-space:nowrap',
        'z-index:9999',
        'pointer-events:none',
        'box-shadow:0 2px 8px rgba(0,0,0,.2)',
      ].join(';');
      el.appendChild(tip);
    });

    el.addEventListener('mouseleave', function () {
      var tip = el.querySelector('.ged-tooltip');
      if (tip) tip.remove();
    });
  });
});

// Formate une taille en octets en chaîne lisible (ex: formatBytes(1048576) -> "1.0 Mo")
function formatBytes(bytes) {
  if (bytes === 0) return '0 o';
  var units = ['o', 'Ko', 'Mo', 'Go'];
  var i = Math.floor(Math.log(bytes) / Math.log(1024));
  return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
}

/**
 * Copie un texte dans le presse-papiers.
 * Ex : copyToClipboard('admin@chr.ma')
 */
function copyToClipboard(text) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(function () {
      showToast('Copié dans le presse-papiers !');
    });
  } else {
    var el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    showToast('Copié !');
  }
}

/**
 * Affiche un toast temporaire en bas de l'écran.
 */
function showToast(message, type) {
  type = type || 'success';
  var colors = {
    success : { bg: '#E5F7EF', tx: '#085041', bd: '#9FE1CB' },
    error   : { bg: '#FDE8EE', tx: '#993556', bd: '#F4C0D1' },
    info    : { bg: '#EEF2FF', tx: '#4338ca', bd: '#c7d2fe' },
  };
  var c   = colors[type] || colors.success;
  var div = document.createElement('div');
  div.textContent = message;
  div.style.cssText = [
    'position:fixed',
    'bottom:24px',
    'right:24px',
    'background:' + c.bg,
    'color:' + c.tx,
    'border:1px solid ' + c.bd,
    'padding:10px 18px',
    'border-radius:10px',
    'font-size:13px',
    'font-weight:600',
    'box-shadow:0 4px 16px rgba(0,0,0,.10)',
    'z-index:9999',
    'transition:opacity .4s',
    'font-family:Inter,sans-serif',
  ].join(';');
  document.body.appendChild(div);
  setTimeout(function () {
    div.style.opacity = '0';
    setTimeout(function () { div.remove(); }, 400);
  }, 2800);
}

/**
 * Affiche/masque un champ mot de passe.
 * @param {string} inputId  - ID du champ input
 * @param {Element} btn     - Le bouton œil cliqué
 */
function togglePassword(inputId, btn) {
  var inp = document.getElementById(inputId);
  if (!inp) return;
  if (inp.type === 'password') {
    inp.type        = 'text';
    btn.textContent = '🙈';
  } else {
    inp.type        = 'password';
    btn.textContent = '👁️';
  }
}

/**
 * Soumet le formulaire parent d'un élément.
 * Utile pour les selects avec onchange.
 */
function submitParentForm(el) {
  var form = el.closest('form');
  if (form) form.submit();
}
