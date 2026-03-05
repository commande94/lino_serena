<?php
require_once '../php/bdd.php';

// Note: Assuming $pdo is available, as in other files. If not, it might be $bdd.

$message = '';
if (isset($_GET['insert']) && $_GET['insert'] == 'success') {
    $message = 'Produit ajouté avec succès!';
} elseif (isset($_GET['update']) && $_GET['update'] == 'success') {
    $message = 'Produit modifié avec succès!';
} elseif (isset($_GET['delete']) && $_GET['delete'] == 'ok') {
    $message = 'Produit supprimé avec succès!';
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

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Dashboard Produits - Lido Serena</title>
</head>

<body>
    <div class="container">
        <h1>Dashboard des Produits</h1>

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
    </div>
</body>

</html>
</content>
<parameter name="filePath">c:\xampp\htdocs\lido_serena\html\dashboard.php