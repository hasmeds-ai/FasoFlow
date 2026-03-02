<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/header.php';
?>
<h2 class="h4 mb-3">À propos du système</h2>

<div class="card shadow-sm">
  <div class="card-body">
    <p><strong>FasoFlow</strong> est un système web d’automatisation des tâches en entreprise.</p>
    <ul class="mb-0">
      <li>Gestion des tâches (CRUD)</li>
      <li>Attribution automatique selon règles</li>
      <li>Rappels automatiques des tâches en retard</li>
      <li>Notifications internes sur le Tableau de Bord</li>
      <li>Rapports / Historique</li>
    </ul>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>