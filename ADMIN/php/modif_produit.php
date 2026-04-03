<?php
require_once 'bdd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = $_POST['id_produit'];
    $nom = htmlspecialchars($_POST['nom']);
    $prix = $_POST['prix'];
    $id_category = $_POST['id_category'];

    try {
        $sql = "UPDATE produits SET nom = :nom, prix = :prix, id_category = :id_category WHERE id_produit = :id_produit";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nom' => $nom,
            ':prix' => $prix,
            ':id_category' => $id_category,
            ':id_produit' => $id_produit
        ]);

        header('Location: ../html/administration.php?update=success');
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de la modification : " . $e->getMessage());
    }
} else {
    // Récupérer l'ID du produit depuis l'URL
    $id_produit = $_GET['id'] ?? null;

    if (!$id_produit) {
        die("ID produit manquant");
    }

    // Récupérer les données du produit
    $sql = "SELECT * FROM produits WHERE id_produit = :id_produit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_produit' => $id_produit]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        die("Produit non trouvé");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier un produit - Lido Serena</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="container">
        <h1>Modifier un produit</h1>

        <section class="form-container">
            <h2>Détails du produit</h2>
            <form action="../php/modif_produit.php" method="POST">
                <input type="hidden" name="id_produit" value="<?= htmlspecialchars($produit['id_produit']) ?>">
                <input type="text" name="nom" placeholder="Nom du produit"
                    value="<?= htmlspecialchars($produit['nom']) ?>" required>
                <input type="number" name="prix" step="0.01" placeholder="Prix (€)"
                    value="<?= htmlspecialchars($produit['prix']) ?>" required>

                <select name="id_category" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <?php
                    $categories = $pdo->query("SELECT id_category, nom FROM categories")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $categorie) {
                        $selected = ($categorie['id_category'] == $produit['id_category']) ? 'selected' : '';
                        echo "<option value=\"{$categorie['id_category']}\" $selected>" . htmlspecialchars($categorie['nom']) . "</option>";
                    }
                    ?>
                </select>

                <button type="submit">Enregistrer</button>
            </form>
        </section>
    </div>

</body>

</html>