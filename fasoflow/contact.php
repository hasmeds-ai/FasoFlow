<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$sent = false;
if (is_post() && isset($_POST['send'])) {
    csrf_validate();
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($subject === '' || $message === '') {
        flash_set('danger', "Objet et message obligatoires.");
        redirect('contact.php');
    }

    // Simple support : on confirme juste côté interface (mail() dépend serveur)
    flash_set('success', "Message envoyé au support (simulation).");
    redirect('contact.php');
}

require __DIR__ . '/includes/header.php';
?>
<h2 class="h4 mb-3">Contact / Support</h2>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="fw-bold mb-2">Support FasoFlow</div>
        <div class="text-muted small">
          Utilise ce formulaire pour signaler un problème ou poser une question.
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <div class="mb-3">
            <label class="form-label">Objet *</label>
            <input class="form-control" name="subject" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message *</label>
            <textarea class="form-control" name="message" rows="4" required></textarea>
          </div>
          <button class="btn btn-dark" name="send" value="1">Envoyer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>