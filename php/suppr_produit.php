<?php
require_once 'bdd.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("DELETE FROM produits WHERE id_produit = ?");
    $stmt->execute([$id]);

    header('Location: ../html/administration.php?delete=ok');
}
?>