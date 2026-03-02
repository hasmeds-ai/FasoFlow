<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!empty($_SESSION['user'])) redirect('dashboard.php');

$error = null;

if (is_post()) {
    csrf_validate();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $error = "Veuillez renseigner email et mot de passe.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u || !password_verify($pass, $u['password'])) {
            $error = "Identifiants incorrects.";
        } else {
            // Stocker user en session (sans password)
            $_SESSION['user'] = [
                'id' => (int)$u['id'],
                'name' => $u['name'],
                'email' => $u['email'],
                'role' => $u['role'],
            ];
            flash_set('success', "Connexion réussie. Bienvenue sur FasoFlow !");
            redirect('dashboard.php');
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FasoFlow - Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5" style="max-width:520px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h1 class="h4 fw-bold mb-1">FasoFlow</h1>
      <p class="text-muted mb-4">Connexion sécurisée (Admin / Utilisateur)</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post">

        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mot de passe</label>
          <input class="form-control" type="password" name="password" required>
        </div>

        <button class="btn btn-dark w-100">Se connecter</button>

      </form>

      <hr class="my-4">
      <div class="small text-muted">
        <br>
        Admin : admin@fasoflow.local / FasoFlow@123 <br>
        User : user@fasoflow.local / FasoFlow@123
      </div>
    </div>
  </div>
</div>
</body>
</html>