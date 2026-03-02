<?php

declare(strict_types=1);
require_once __DIR__ . '/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$flash = flash_get();
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FasoFlow</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">FasoFlow</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Tableau de Bord</a></li>
          <li class="nav-item"><a class="nav-link" href="tasks.php">Tâches</a></li>
          <li class="nav-item"><a class="nav-link" href="automation.php">Automatisation</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php">Rapports</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">À propos</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php">Support</a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav">
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="navbar-text me-3">
              <?= e($user['name']) ?> (<?= e($user['role']) ?>)
            </span>
          </li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="logout.php">Déconnexion</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>