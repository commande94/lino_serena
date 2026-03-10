<?php
require_once 'bdd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = htmlspecialchars($_POST['nom']);
    $prix = $_POST['prix'];
    $id_category = $_POST['id_category'];

    try {
        $sql = "INSERT INTO produits (nom, prix, id_category) VALUES (:nom, :prix, :id_category)";
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nom' => $nom,
            ':prix' => $prix,
            ':id_category' => $id_category
        ]);

        // 6. Redirection vers le dashboard avec un message de succès
        header('Location: ../html/administration.php?insert=success');
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de l'ajout : " . $e->getMessage());
    }
}
?>