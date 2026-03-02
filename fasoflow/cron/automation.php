<?php

declare(strict_types=1);

/**
 * Automatisation FasoFlow :
 * 1) Attribution automatique des tâches non assignées (is_automated=1)
 * 2) Rappels automatiques pour tâches en retard (deadline < now et non terminées)
 * 3) Historique + notifications internes
 */

require_once __DIR__ . '/../config/database.php';

// 1) Auto-assign : récupérer users "user"
$users = $pdo->query("SELECT id FROM users WHERE role='user' ORDER BY id")->fetchAll();
$userIds = array_map(fn($r) => (int)$r['id'], $users);

if (count($userIds) > 0) {
    // Tâches à auto-assigner : non assignées + automatisées
    $tasks = $pdo->query("
        SELECT id, title
        FROM tasks
        WHERE assigned_to IS NULL
          AND is_automated = 1
          AND status IN ('En attente','En cours')
        ORDER BY id ASC
        LIMIT 50
    ")->fetchAll();

    $i = 0;
    foreach ($tasks as $t) {
        $assignedTo = $userIds[$i % count($userIds)];
        $i++;

        $pdo->prepare("UPDATE tasks SET assigned_to=? WHERE id=?")->execute([$assignedTo, $t['id']]);

        $pdo->prepare("INSERT INTO notifications(user_id, message, type) VALUES(?,?,?)")
            ->execute([$assignedTo, "Attribution automatique : {$t['title']}", "success"]);

        $pdo->prepare("INSERT INTO task_history(task_id, action, details, created_by) VALUES(?,?,?,NULL)")
            ->execute([(int)$t['id'], "AUTO_ASSIGN", "Attribution automatique à user_id={$assignedTo}."]);
    }
}

// 2) Rappels retard : deadline passée & non terminées
$overdues = $pdo->query("
    SELECT id, title, assigned_to, deadline
    FROM tasks
    WHERE deadline IS NOT NULL
      AND deadline < NOW()
      AND status <> 'Terminé'
    ORDER BY deadline ASC
    LIMIT 100
")->fetchAll();

foreach ($overdues as $t) {
    $assignedTo = $t['assigned_to'] ? (int)$t['assigned_to'] : null;

    if ($assignedTo) {
        $pdo->prepare("INSERT INTO notifications(user_id, message, type) VALUES(?,?,?)")
            ->execute([$assignedTo, "⚠️ Tâche en retard : {$t['title']} (deadline: {$t['deadline']})", "warning"]);
    }

    $pdo->prepare("INSERT INTO task_history(task_id, action, details, created_by) VALUES(?,?,?,NULL)")
        ->execute([(int)$t['id'], "AUTO_REMINDER", "Rappel automatique (retard)."]);
}

echo "OK - Automatisation exécutée. Auto-assign: ".(isset($tasks)?count($tasks):0).", Overdue reminders: ".count($overdues).PHP_EOL;