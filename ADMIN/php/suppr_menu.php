<?php
require_once 'bdd.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // supprimer les associations avant de supprimer le menu
    $pdo->prepare("DELETE FROM produit_menus WHERE id_menu = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM staff_menus WHERE id_menu = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM commandes_menus WHERE id_menu = ?")->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM menus WHERE id_menu = ?");
    $stmt->execute([$id]);

    header('Location: ../html/administration.php?delete=menu_ok');
}
?>
