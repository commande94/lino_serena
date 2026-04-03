<?php
session_start();
require_once 'bdd.php';

// y'a que les mamagers qui peuvent supprimer des comptes staff
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: ../html/connexion.html');
    exit();
}

// on vérifie que la requete est bien une requete POST et que l'id est la
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    // le moment ou on delete
    $stmt = $pdo->prepare("DELETE FROM staff WHERE id_staff = :id");
    $stmt->execute(['id' => $id]);

    // si il suppr son propre compte, on le déconnecte
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $id) {
        session_unset();
        session_destroy();
        header('Location: ../html/connexion.html');
        exit();
    }
}

// on le redirige vers la page de gestion des utilisateurs après suppression 
$redirect = '../html/manage_user.php?deleted=1';
header('Location: ' . $redirect);
exit();
