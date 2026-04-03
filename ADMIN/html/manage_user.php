<?php
session_start();

// y'a que les mamagers qui peuvent accéder à cette page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    // redirection si c'est pas un manager ou si il est pas connecté
    header('Location: connexion.html');
    exit();
}

require_once '../php/bdd.php';

// requête pour récupérer tous les utilisateurs du staff
$stmt = $pdo->query("SELECT id_staff, nom, prenom, email, role, date_creation FROM staff ORDER BY id_staff");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/lidorena.png">
    <link rel="stylesheet" href="../css/style.css">
    <title>Gestion des utilisateurs - Lido Serena</title>
    <style>
        /* passe au blanc */
        body {
            background: white !important;
            background-image: none !important;
        }
        .users-table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 1000px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <nav>
        <a href="administration.php" class="btn-back">Retour au tableau de bord</a>
        <a href="../php/logout.php" class="btn-logout">Déconnexion</a>
    </nav>

    <h1>Gestion des utilisateurs</h1>
    <!-- message de succès après la suppression d'un utilisateur -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="message success">Utilisateur supprimé avec succès.</div>
    <?php endif; ?>
    <?php if (empty($users)): ?>
        <p>Aucun utilisateur enregistré.</p>
    <?php else: ?>
        <!-- tableau pour afficher les dans la page -->
        <div class="users-table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Inscrit le</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <!-- affichage des informations de chaque utilisateur avec la fonction htmlspecialchars pour la sécurité -->
                        <td><?= htmlspecialchars($u['id_staff']) ?></td>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['prenom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td><?= htmlspecialchars($u['date_creation']) ?></td>
                        <td style="white-space: nowrap;">
                            <form class="delete-form" method="post" action="../php/suppr_staff.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                                <input type="hidden" name="id" value="<?= $u['id_staff'] ?>">
                                <button type="submit" class="btn-delete">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</body>
</html>
