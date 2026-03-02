// assets/js/script.js
// JS optionnel (Bootstrap fonctionne déjà sans ça si bundle utilisés)

(function () {
  // Auto-fermeture des alertes après 5 secondes
  const alerts = document.querySelectorAll('.alert');
  if (alerts.length) {
    setTimeout(() => {
      alerts.forEach(a => {
        // Si bootstrap Alert est dispo
        try {
          const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
          bsAlert.close();
        } catch (e) {
          // fallback: cacher
          a.style.display = 'none';
        }
      });
    }, 5000);
  }
})();