<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/lidorena.png">
    <link rel="stylesheet" href="../css/style.css">
    <title>Dashboard Produits - Lido Serena</title>
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


    $message = '';
    if (isset($_GET['insert'])) {
        if ($_GET['insert'] == 'success') {
            $message = 'Produit ajouté avec succès!';
        } elseif ($_GET['insert'] == 'menu_success') {
            $message = 'Menu ajouté avec succès!';
        }
    } elseif (isset($_GET['update'])) {
        if ($_GET['update'] == 'success') {
            $message = 'Produit modifié avec succès!';
        } elseif ($_GET['update'] == 'menu_success') {
            $message = 'Menu modifié avec succès!';
        }
    } elseif (isset($_GET['delete'])) {
        if ($_GET['delete'] == 'ok') {
            $message = 'Produit supprimé avec succès!';
        } elseif ($_GET['delete'] == 'menu_ok') {
            $message = 'Menu supprimé avec succès!';
        }
    }

    // Fetch all products with category names
    $sql = "SELECT p.id_produit, p.nom, p.prix, c.nom AS categorie_nom
            FROM produits p
            LEFT JOIN categories c ON p.id_category = c.id_category
            ORDER BY p.id_produit";
    $stmt = $pdo->query($sql);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch categories for the add form
    $categories = $pdo->query("SELECT id_category, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="container">
        <h1>Dashboard des Produits</h1>
        <nav>
            <span class="welcome-user" style="background-color: white;">Bienvenue,
                <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></span>
            <a href="chart.php" class="btn-chart">Voir les statistiques</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
                <a href="manage_user.php" class="btn-manage-users">Gérer les utilisateurs</a>
            <?php endif; ?>
            <a href="commandes.php" class="btn-chart">Voir les commandes</a>

            <a href="../php/logout.php" class="btn-logout" style="background-color: white;">Déconnexion</a>
        </nav>

        <?php if ($message): ?>
            <div class="message success">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'ajout -->
        <section class="form-container">
            <h2>Ajouter un nouveau produit</h2>
            <form action="../php/ajout_produit.php" method="POST">
                <input type="text" name="nom" placeholder="Nom du produit" required>
                <input type="number" name="prix" step="0.01" placeholder="Prix (€)" required>
                <select name="id_category" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= $categorie['id_category'] ?>"><?= htmlspecialchars($categorie['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Ajouter</button>
            </form>
        </section>

        <!-- Liste des produits -->
        <section class="products-list">
            <h2>Liste des produits</h2>
            <?php if (empty($produits)): ?>
                <p>Aucun produit trouvé.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix (€)</th>
                            <th>Catégorie</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $produit): ?>
                            <tr>
                                <td><?= htmlspecialchars($produit['id_produit']) ?></td>
                                <td><?= htmlspecialchars($produit['nom']) ?></td>
                                <td><?= htmlspecialchars($produit['prix']) ?> €</td>
                                <td><?= htmlspecialchars($produit['categorie_nom'] ?? 'Aucune') ?></td>
                                <td>
                                    <a href="../php/modif_produit.php?id=<?= $produit['id_produit'] ?>"
                                        class="btn-edit">Modifier</a>
                                    <a href="../php/suppr_produit.php?id=<?= $produit['id_produit'] ?>" class="btn-delete"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <!-- Gestion des menus -->
        <?php

        $sql = "SELECT m.id_menu, m.nom, m.prix, m.disponible, m.description,
                       GROUP_CONCAT(p.nom SEPARATOR ', ') AS produits"
            . " FROM menus m"
            . " LEFT JOIN produit_menus pm ON m.id_menu = pm.id_menu"
            . " LEFT JOIN produits p ON pm.id_produit = p.id_produit"
            . " GROUP BY m.id_menu"
            . " ORDER BY m.id_menu";
        $stmt = $pdo->query($sql);
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $productsByCategory = [];
        $allProducts = $pdo->query("SELECT id_produit, nom, id_category FROM produits ORDER BY id_category, nom")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allProducts as $prod) {
            $productsByCategory[$prod['id_category']][] = $prod;
        }
        ?>

        <section class="form-container">
            <h2>Ajouter un nouveau menu</h2>
            <form action="../php/ajout_menu.php" method="POST">
                <input type="text" name="nom" placeholder="Nom du menu" required>
                <textarea name="description" placeholder="Description" rows="3"></textarea>
                <input type="number" name="prix" step="0.01" placeholder="Prix (€)" required>
                <label>
                    Disponible ?
                    <input type="checkbox" name="disponible" value="1" checked>
                </label>
                <fieldset>
                    <legend>Produits du menu (choisir plusieurs si nécessaire)</legend>
                    <?php foreach ($categories as $categorie): ?>
                        <strong><?= htmlspecialchars($categorie['nom']) ?> :</strong><br>
                        <?php
                        $catId = $categorie['id_category'];
                        if (isset($productsByCategory[$catId])):
                            foreach ($productsByCategory[$catId] as $prod):
                                ?>
                                <label>
                                    <input type="checkbox" name="produits[]" value="<?= $prod['id_produit'] ?>">
                                    <?= htmlspecialchars($prod['nom']) ?>
                                </label><br>
                                <?php
                            endforeach;
                        else:
                            echo '<em>Aucun produit dans cette catégorie</em><br>';
                        endif;
                        ?>
                        <br>
                    <?php endforeach; ?>
                </fieldset>
                <button type="submit">Ajouter</button>
            </form>
        </section>

        <section class="menus-list">
            <h2>Liste des menus</h2>
            <?php if (empty($menus)): ?>
                <p>Aucun menu trouvé.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Disponibilité</th>
                            <th>Produits</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                            <tr>
                                <td><?= htmlspecialchars($menu['id_menu']) ?></td>
                                <td><?= htmlspecialchars($menu['nom']) ?></td>
                                <td><?= htmlspecialchars($menu['prix']) ?> €</td>
                                <td><?= $menu['disponible'] ? 'Oui' : 'Non' ?></td>
                                <td><?= htmlspecialchars($menu['produits'] ?? '') ?></td>
                                <td>
                                    <a href="../php/modif_menu.php?id=<?= $menu['id_menu'] ?>" class="btn-edit">Modifier</a>
                                    <a href="../php/suppr_menu.php?id=<?= $menu['id_menu'] ?>" class="btn-delete"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>
</content>
<parameter name="filePath">