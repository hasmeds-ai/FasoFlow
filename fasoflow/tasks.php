<?php
// tasks.php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user = $_SESSION['user'];
$userId = (int)$user['id'];
$isAdmin = (($user['role'] ?? '') === 'admin');

// Users list (assignation)
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

// Create
if (is_post() && isset($_POST['create'])) {
    csrf_validate();

    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $assignedTo = ($_POST['assigned_to'] ?? '') !== '' ? (int)$_POST['assigned_to'] : null;
    $status = (string)($_POST['status'] ?? 'En attente');
    $deadline = ($_POST['deadline'] ?? '') !== '' ? (string)$_POST['deadline'] : null;
    $scheduledAt = ($_POST['scheduled_at'] ?? '') !== '' ? (string)$_POST['scheduled_at'] : null;
    $isAutomated = isset($_POST['is_automated']) ? 1 : 0;

    if ($title === '') {
        flash_set('danger', "Titre obligatoire.");
        redirect('tasks.php');
    }

    $stmt = $pdo->prepare("INSERT INTO tasks(title, description, assigned_to, status, deadline, scheduled_at, is_automated) VALUES(?,?,?,?,?,?,?)");
    $stmt->execute([$title, $description ?: null, $assignedTo, $status, $deadline, $scheduledAt, $isAutomated]);

    $taskId = (int)$pdo->lastInsertId();

    // Notification confirmation
    if ($assignedTo) {
        $pdo->prepare("INSERT INTO notifications(user_id, message, type) VALUES(?,?,?)")
            ->execute([$assignedTo, "Nouvelle tâche assignée : {$title}", "info"]);
    }

    // Historique
    $pdo->prepare("INSERT INTO task_history(task_id, action, details, created_by) VALUES(?,?,?,?)")
        ->execute([$taskId, "CREATION", "Tâche créée (automatisée={$isAutomated}).", $userId]);

    flash_set('success', "Tâche créée avec succès.");
    redirect('tasks.php');
}

// Update
if (is_post() && isset($_POST['update'])) {
    csrf_validate();

    $id = (int)($_POST['id'] ?? 0);
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $assignedTo = ($_POST['assigned_to'] ?? '') !== '' ? (int)$_POST['assigned_to'] : null;
    $status = (string)($_POST['status'] ?? 'En attente');
    $deadline = ($_POST['deadline'] ?? '') !== '' ? (string)$_POST['deadline'] : null;
    $scheduledAt = ($_POST['scheduled_at'] ?? '') !== '' ? (string)$_POST['scheduled_at'] : null;
    $isAutomated = isset($_POST['is_automated']) ? 1 : 0;

    if ($id <= 0 || $title === '') {
        flash_set('danger', "Données invalides.");
        redirect('tasks.php');
    }

    $stmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, assigned_to=?, status=?, deadline=?, scheduled_at=?, is_automated=? WHERE id=?");
    $stmt->execute([$title, $description ?: null, $assignedTo, $status, $deadline, $scheduledAt, $isAutomated, $id]);

    $pdo->prepare("INSERT INTO task_history(task_id, action, details, created_by) VALUES(?,?,?,?)")
        ->execute([$id, "UPDATE", "Tâche mise à jour.", $userId]);

    flash_set('success', "Tâche modifiée.");
    redirect('tasks.php');
}

// Delete
if (is_post() && isset($_POST['delete'])) {
    csrf_validate();
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        flash_set('danger', "ID invalide.");
        redirect('tasks.php');
    }

    // Historique avant suppression
    $pdo->prepare("INSERT INTO task_history(task_id, action, details, created_by) VALUES(?,?,?,?)")
        ->execute([$id, "DELETE", "Tâche supprimée.", $userId]);

    $pdo->prepare("DELETE FROM tasks WHERE id=?")->execute([$id]);

    flash_set('warning', "Tâche supprimée.");
    redirect('tasks.php');
}

// Listing tasks
$stmt = $pdo->query("
SELECT t.*, u.name AS assigned_name
FROM tasks t
LEFT JOIN users u ON u.id = t.assigned_to
ORDER BY t.id DESC
");
$tasks = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h2 class="h4 mb-3">Gestion des tâches</h2>

<div class="card shadow-sm mb-4">
  <div class="card-header fw-bold">Ajouter une tâche</div>
  <div class="card-body">
    <form method="post" class="row g-3">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="col-md-6">
        <label class="form-label">Titre *</label>
        <input class="form-control" name="title" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Assignée à</label>
        <select class="form-select" name="assigned_to">
          <option value="">— Non assignée —</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="2"></textarea>
      </div>

      <div class="col-md-3">
        <label class="form-label">Statut</label>
        <select class="form-select" name="status">
          <option>En attente</option>
          <option>En cours</option>
          <option>Terminé</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Deadline</label>
        <input class="form-control" type="datetime-local" name="deadline">
      </div>

      <div class="col-md-3">
        <label class="form-label">Planification</label>
        <input class="form-control" type="datetime-local" name="scheduled_at">
      </div>

      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_automated" id="auto1" value="1">
          <label class="form-check-label" for="auto1">Automatisée</label>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-dark" name="create" value="1">Créer</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header fw-bold">Liste des tâches</div>
  <div class="card-body table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Titre</th>
          <th>Assignée</th>
          <th>Statut</th>
          <th>Deadline</th>
          <th>Autom.</th>
          <th style="width: 280px;">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($tasks as $t): ?>
        <tr>
          <td><?= (int)$t['id'] ?></td>
          <td>
            <div class="fw-semibold"><?= e($t['title']) ?></div>
            <div class="small text-muted"><?= e((string)$t['description']) ?></div>
          </td>
          <td><?= e($t['assigned_name'] ?? '—') ?></td>
          <td>
            <span class="badge bg-<?= $t['status']==='Terminé'?'success':($t['status']==='En cours'?'primary':'secondary') ?>">
              <?= e($t['status']) ?>
            </span>
          </td>
          <td class="small text-muted"><?= e((string)$t['deadline']) ?></td>
          <td><?= ((int)$t['is_automated'] === 1) ? 'Oui' : 'Non' ?></td>

          <td>
            <!-- Update inline form -->
            <form method="post" class="d-inline">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <input type="hidden" name="title" value="<?= e($t['title']) ?>">
              <input type="hidden" name="description" value="<?= e((string)$t['description']) ?>">
              <input type="hidden" name="assigned_to" value="<?= e((string)$t['assigned_to']) ?>">
              <input type="hidden" name="status" value="<?= e($t['status']) ?>">
              <input type="hidden" name="deadline" value="<?= e((string)$t['deadline']) ?>">
              <input type="hidden" name="scheduled_at" value="<?= e((string)$t['scheduled_at']) ?>">
              <?php if ((int)$t['is_automated'] === 1): ?>
                <input type="hidden" name="is_automated" value="1">
              <?php endif; ?>
              <button class="btn btn-sm btn-outline-primary" type="button"
                data-bs-toggle="modal" data-bs-target="#editModal<?= (int)$t['id'] ?>">
                Modifier
              </button>
            </form>

            <!-- Delete -->
            <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cette tâche ?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" name="delete" value="1">Supprimer</button>
            </form>

            <!-- Modal edit -->
            <div class="modal fade" id="editModal<?= (int)$t['id'] ?>" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form method="post">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title">Modifier tâche #<?= (int)$t['id'] ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Titre *</label>
                          <input class="form-control" name="title" value="<?= e($t['title']) ?>" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Assignée à</label>
                          <select class="form-select" name="assigned_to">
                            <option value="">— Non assignée —</option>
                            <?php foreach ($users as $u): ?>
                              <option value="<?= (int)$u['id'] ?>" <?= ((int)$t['assigned_to']===(int)$u['id'])?'selected':'' ?>>
                                <?= e($u['name']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-12">
                          <label class="form-label">Description</label>
                          <textarea class="form-control" name="description" rows="2"><?= e((string)$t['description']) ?></textarea>
                        </div>

                        <div class="col-md-3">
                          <label class="form-label">Statut</label>
                          <select class="form-select" name="status">
                            <?php foreach (['En attente','En cours','Terminé'] as $s): ?>
                              <option <?= ($t['status']===$s)?'selected':'' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-md-3">
                          <label class="form-label">Deadline</label>
                          <input class="form-control" type="datetime-local" name="deadline"
                            value="<?= $t['deadline'] ? e(date('Y-m-d\TH:i', strtotime($t['deadline']))) : '' ?>">
                        </div>

                        <div class="col-md-3">
                          <label class="form-label">Planification</label>
                          <input class="form-control" type="datetime-local" name="scheduled_at"
                            value="<?= $t['scheduled_at'] ? e(date('Y-m-d\TH:i', strtotime($t['scheduled_at']))) : '' ?>">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_automated" value="1"
                              id="auto<?= (int)$t['id'] ?>" <?= ((int)$t['is_automated']===1)?'checked':'' ?>>
                            <label class="form-check-label" for="auto<?= (int)$t['id'] ?>">Automatisée</label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
                      <button class="btn btn-dark" name="update" value="1">Enregistrer</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>