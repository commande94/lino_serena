<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require 'bdd.php';


$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $nom = trim($_POST['Nom']);
    $prenom = trim($_POST['Prénom']);
    $role = trim($_POST['rôle']); 
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];


    if ($password !== $confirm_password) {
        $message = "<p style='color:red'> Les mots de passe ne correspondent pas.</p>";
    } else {
       
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

      
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $message = "<p style='color:red'> Email déjà utilisé.</p>";
        } else {
            
            $stmt = $pdo->prepare("INSERT INTO staff (nom, prenom, email, role, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $role, $hashed_password]);

            $message = "<p style='color:green'> Inscription réussie !</p>";
        }
    }
}
?>