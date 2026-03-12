<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/lidorena.png">
    <link rel="stylesheet" href="../css/style.css">
    <title>commandes</title>
</head>

<body>

    <a href="inscription.php" class="btn-inscription">
        Inscription d'un nouveau membre par le Super-Admin
    </a>

    <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: connexion.html');
        exit();
    }
    require_once '../php/bdd.php';



    $sql = "SELECT p.id_produit, p.nom, p.prix, c.nom AS categorie_nom
            FROM produits p
            LEFT JOIN categories c ON p.id_category = c.id_category
            ORDER BY p.id_produit";
    $stmt = $pdo->query($sql);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = $pdo->query("SELECT id_category, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    $sql_commandes = "SELECT c.id_com as id_commande, s.nom as nom, c.prix_total as prix, GROUP_CONCAT(m.nom SEPARATOR ', ') as menus FROM commandes c LEFT JOIN commandes_menus cm ON c.id_com = cm.id_com LEFT JOIN menus m ON cm.id_menu = m.id_menu LEFT JOIN staff s ON s.id_staff = c.id_staff GROUP BY c.id_com ORDER BY c.id_com;";
    $commandes = $pdo->query($sql_commandes)->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <h1 class="dashboard-title" style="background-color: white;">COMMANDES</h1>
    <nav>
        <span class="welcome-user" style="background-color: white;">Bienvenue,
            <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></span>
        <a href="administration.php" class="btn-back" style="background-color: white;">Retour à l'administration</a>
        <a href="../php/logout.php" class="btn-logout" style="background-color: white;">Déconnexion</a>
    </nav>
    <section class="commandes-section">
        <h2>Liste des commandes</h2>
        <?php if (empty($commandes)): ?>
            <p>Aucune commande trouvée.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>prix (€)</th>
                        <th>produits</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($commande['id_commande']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($commande['nom']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($commande['prix']) ?> €
                            </td>
                            <td>
                                <?= htmlspecialchars($commande['menus'] ?? '') ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

</body>

</html>