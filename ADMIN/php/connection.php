<?php
session_start();
require_once 'bdd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Recherche dans la table staff
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['id_staff'] = $user['id_staff'];
        $_SESSION['prenom'] = $user['prenom'];
        header('Location: ../html/dashboard.html');
    } else {
        echo "Identifiants incorrects.";
    }
}
?>