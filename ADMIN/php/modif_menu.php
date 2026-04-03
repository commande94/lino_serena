<?php
require_once 'bdd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_menu = $_POST['id_menu'];
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description'] ?? '');
    $prix = $_POST['prix'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    try {
        $sql = "UPDATE menus SET nom = :nom, description = :description, prix = :prix, disponible = :disponible WHERE id_menu = :id_menu";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':description' => $description,
            ':prix' => $prix,
            ':disponible' => $disponible,
            ':id_menu' => $id_menu
        ]);

        // supprimer anciennes associations puis réinsérer
        $pdo->prepare("DELETE FROM produit_menus WHERE id_menu = ?")->execute([$id_menu]);
        if (!empty($_POST['produits']) && is_array($_POST['produits'])) {
            $sql2 = "INSERT INTO produit_menus (id_menu, id_produit) VALUES (:id_menu, :id_produit)";
            $stmt2 = $pdo->prepare($sql2);
            foreach ($_POST['produits'] as $id_produit) {
                $stmt2->execute([
                    ':id_menu' => $id_menu,
                    ':id_produit' => $id_produit
                ]);
            }
        }

        header('Location: ../html/administration.php?update=menu_success');
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la modification du menu : " . $e->getMessage());
    }
} else {
    $id_menu = $_GET['id'] ?? null;
    if (!$id_menu) {
        die("ID menu manquant");
    }

    $sql = "SELECT * FROM menus WHERE id_menu = :id_menu";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_menu' => $id_menu]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$menu) {
        die("Menu non trouvé");
    }

    // récupérer les produits disponibles et ceux déjà associés
    $allProducts = $pdo->query("SELECT id_produit, nom, id_category FROM produits ORDER BY id_category, nom")->fetchAll(PDO::FETCH_ASSOC);
    $selected = $pdo->prepare("SELECT id_produit FROM produit_menus WHERE id_menu = ?");
    $selected->execute([$id_menu]);
    $selectedIds = $selected->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un menu - Lido Serena</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Modifier un menu</h1>
    <section class="form-container">
        <form action="../php/modif_menu.php" method="POST">
            <input type="hidden" name="id_menu" value="<?= htmlspecialchars($menu['id_menu']) ?>">
            <input type="text" name="nom" placeholder="Nom du menu" value="<?= htmlspecialchars($menu['nom']) ?>" required>
            <textarea name="description" placeholder="Description" rows="3"><?= htmlspecialchars($menu['description']) ?></textarea>
            <input type="number" name="prix" step="0.01" placeholder="Prix (€)" value="<?= htmlspecialchars($menu['prix']) ?>" required>
            <label>
                Disponible ?
                <input type="checkbox" name="disponible" value="1" <?= $menu['disponible'] ? 'checked' : '' ?>>
            </label>
            <fieldset>
                <legend>Produits du menu (cochez ceux présents)</legend>
                <?php
                $categories = $pdo->query("SELECT id_category, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
                $productsByCategory = [];
                foreach ($allProducts as $prod) {
                    $productsByCategory[$prod['id_category']][] = $prod;
                }
                foreach ($categories as $categorie):
                    $catId = $categorie['id_category'];
                    echo '<strong>' . htmlspecialchars($categorie['nom']) . ' :</strong><br>';
                    if (isset($productsByCategory[$catId])):
                        foreach ($productsByCategory[$catId] as $prod):
                            $checked = in_array($prod['id_produit'], $selectedIds) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="produits[]" value="' . $prod['id_produit'] . '" ' . $checked . '> ' . htmlspecialchars($prod['nom']) . '</label><br>';
                        endforeach;
                    else:
                        echo '<em>Aucun produit dans cette catégorie</em><br>';
                    endif;
                    echo '<br>';
                endforeach;
                ?>
            </fieldset>
            <button type="submit">Enregistrer</button>
        </form>
    </section>
</div>
</body>
</html>
