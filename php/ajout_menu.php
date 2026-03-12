<?php
require_once 'bdd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description'] ?? '');
    $prix = $_POST['prix'];
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    // date création par défaut aujourd'hui
    $date_creation = date('Y-m-d');

    try {
        $sql = "INSERT INTO menus (nom, description, prix, date_creation, disponible)
                VALUES (:nom, :description, :prix, :date_creation, :disponible)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':description' => $description,
            ':prix' => $prix,
            ':date_creation' => $date_creation,
            ':disponible' => $disponible
        ]);

        $id_menu = $pdo->lastInsertId();

        // gestion des produits du menu
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

        header('Location: ../html/administration.php?insert=menu_success');
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'ajout du menu : " . $e->getMessage());
    }
}
?>
