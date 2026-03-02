# FasoFlow
Système web d’automatisation des tâches en entreprise (PHP + MySQL + Bootstrap).

## Objectif
FasoFlow permet de :
- gérer les tâches (ajout, modification, suppression),
- automatiser l’attribution selon des règles,
- envoyer des rappels internes pour les tâches en retard,
- afficher des notifications sur le tableau de bord,
- générer des rapports / historique des actions.

## Technologies
- PHP (backend)
- MySQL (phpMyAdmin)
- HTML5 / CSS3
- Bootstrap 5
- JavaScript (optionnel)

## Modules / Pages
- Dashboard : `dashboard.php`
- À propos : `about.php`
- Gestion des tâches (CRUD) : `tasks.php`
- Automatisation : `automation.php`
- Rapports / Historique : `reports.php`
- Contact / Support : `contact.php`
- Connexion : `index.php`
- Déconnexion : `logout.php`

## Installation (XAMPP / WAMP)
1. Copier le dossier **fasoflow** dans :
   - XAMPP : `htdocs/fasoflow`
2. Lancer Apache + MySQL.
3. Ouvrir phpMyAdmin et **importer** le fichier SQL :
   - `fasoflow_database.sql`
4. Vérifier la connexion DB dans :
   - `config/database.php` (host, dbname, user, pass)
5. Ouvrir dans le navigateur :
   - `http://localhost/fasoflow/`

## Comptes de démonstration
- Admin : `admin@fasoflow.local` / `FasoFlow@123`
- User : `user@fasoflow.local` / `FasoFlow@123`

## Automatisation (Cron / Planificateur)
### Exécution manuelle
- Aller sur `automation.php` et cliquer **Lancer l’automatisation maintenant**.

### Exécution automatique (Linux Cron)
Exemple :
```bash
php /chemin/vers/htdocs/fasoflow/cron/automation.php
