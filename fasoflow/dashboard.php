<?php
// dashboard.php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$userId = (int)$_SESSION['user']['id'];
$role = (string)$_SESSION['user']['role'];

// Stats
$stmt = $pdo->query("SELECT 
  SUM(status='En attente') AS pending,
  SUM(status='En cours') AS doing,
  SUM(status='Terminé') AS done,
  SUM(is_automated=1) AS automated
FROM tasks");
$stats = $stmt->fetch() ?: ['pending'=>0,'doing'=>0,'done'=>0,'automated'=>0];

// Notifications non lues (par user)
$stmt = $pdo->prepare("SELECT id, message, type, created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 10");
$stmt->execute([$userId]);
$notifs = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$userId]);
$unread = (int)($stmt->fetch()['c'] ?? 0);

// Marquer lu
if (is_post() && isset($_POST['mark_read'])) {
    csrf_validate();
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
    flash_set('success', "Notifications marquées comme lues.");
    redirect('dashboard.php');
}

require __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h4 mb-0">Dashboard</h2>
  <span class="badge bg-primary">Notifications non lues : <?= $unread ?></span>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">En attente</div>
      <div class="display-6"><?= (int)$stats['pending'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">En cours</div>
      <div class="display-6"><?= (int)$stats['doing'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">Terminées</div>
      <div class="display-6"><?= (int)$stats['done'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card shadow-sm"><div class="card-body">
      <div class="text-muted">Tâches automatisées</div>
      <div class="display-6"><?= (int)$stats['automated'] ?></div>
    </div></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div class="fw-bold">Dernières notifications</div>
    <form method="post" class="m-0">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button class="btn btn-sm btn-outline-secondary" name="mark_read" value="1">Marquer tout comme lu</button>
    </form>
  </div>
  <div class="card-body">
    <?php if (!$notifs): ?>
      <div class="text-muted">Aucune notification.</div>
    <?php else: ?>
      <ul class="list-group">
        <?php foreach ($notifs as $n): ?>
          <li class="list-group-item d-flex justify-content-between">
            <div>
              <span class="badge bg-<?= e($n['type']) ?> me-2"><?= e($n['type']) ?></span>
              <?= e($n['message']) ?>
            </div>
            <div class="text-muted small"><?= e($n['created_at']) ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>