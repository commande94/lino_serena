<?php
session_start(); 
require 'bdd.php'; 

$message = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['prenom'] . " " . $user['nom'];
        $_SESSION['user_role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        $message = "Email ou mot de passe incorrect.";
    }
}?>
