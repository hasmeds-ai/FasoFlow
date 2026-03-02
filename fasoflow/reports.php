<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Statistiques simples
$stats = $pdo->query("
SELECT
  COUNT(*) AS total,
  SUM(status='En attente') AS pending,
  SUM(status='En cours') AS doing,
  SUM(status='Terminé') AS done,
  SUM(is_automated=1) AS automated
FROM tasks
")->fetch();

// Historique (dernieres actions)
$history = $pdo->query("
SELECT h.*, t.title, u.name AS actor
FROM task_history h
JOIN tasks t ON t.id = h.task_id
LEFT JOIN users u ON u.id = h.created_by
ORDER BY h.id DESC
LIMIT 50
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h2 class="h4 mb-3">Rapports / Historique</h2>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">Total tâches</div><div class="display-6"><?= (int)$stats['total'] ?></div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">En attente</div><div class="display-6"><?= (int)$stats['pending'] ?></div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">En cours</div><div class="display-6"><?= (int)$stats['doing'] ?></div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted">Automatisées</div><div class="display-6"><?= (int)$stats['automated'] ?></div>
  </div></div></div>
</div>

<div class="card shadow-sm">
  <div class="card-header fw-bold">Historique (50 dernières actions)</div>
  <div class="card-body table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Date</th>
          <th>Tâche</th>
          <th>Action</th>
          <th>Détails</th>
          <th>Par</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $h): ?>
        <tr>
          <td class="small text-muted"><?= e($h['created_at']) ?></td>
          <td><?= e($h['title']) ?></td>
          <td><span class="badge bg-dark"><?= e($h['action']) ?></span></td>
          <td class="small text-muted"><?= e((string)$h['details']) ?></td>
          <td><?= e($h['actor'] ?? 'Système') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>