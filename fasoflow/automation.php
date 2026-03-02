<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Lancement manuel (admin conseillé)
if (is_post() && isset($_POST['run'])) {
    csrf_validate();

    // Exécuter le script cron en local (simple include)
    ob_start();
    include __DIR__ . '/cron/automation.php';
    $output = ob_get_clean();

    flash_set('success', "Automatisation lancée. Résultat : " . trim((string)$output));
    redirect('automation.php');
}

require __DIR__ . '/includes/header.php';
?>
<h2 class="h4 mb-3">Automatisation</h2>

<div class="card shadow-sm">
  <div class="card-body">
    <p class="text-muted mb-3">
      Cette page permet de lancer manuellement le script d’automatisation.
      Pour une exécution automatique, configure un cron job (ou planificateur Windows).
    </p>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button class="btn btn-dark" name="run" value="1">Lancer l’automatisation maintenant</button>
    </form>

    <hr>
    <div class="small text-muted">
      Exemple cron Linux : <code>php /path/to/fasoflow/cron/automation.php</code>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>