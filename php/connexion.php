<?php
session_start();
require_once 'bdd.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email_saisi = trim($_POST['email']);
    $pass_saisi = $_POST['password']; 

    try {
        $query = $bdd->prepare("SELECT * FROM staff WHERE email = :email");
        $query->execute(['email' => $email_saisi]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass_saisi, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id_staff'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            
            header("Location: ../html/menu.html");
            exit();
        } else {
            header('Location: ../html/connexion.html?error=1');
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
} else {
    header('Location: ../html/connexion.html');
    exit();
}
?>