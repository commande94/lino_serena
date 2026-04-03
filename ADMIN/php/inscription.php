<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/connexion.html');
    exit();
}

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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lido Selena - Inscription</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php if (!empty($message))
        echo $message; ?>
    <form action="../php/inscription.php" method="post">
        <div class="form-group">
            <label for="Nom">Nom:</label>
            <input type="text" id="Nom" name="Nom" required>
        </div>

        <div class="form-group">
            <label for="Prénom">Prénom:</label>
            <input type="text" id="Prénom" name="Prénom" required>
        </div>

        <div class="form-group">
            <label for="rôle">Rôle:</label>
            <select id="rôle" name="rôle" required>
                <option value="">Sélectionnez un rôle</option>
                <option value="admin">Admin</option>
                <option value="super-admin">Super Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" class="password-field" required>
        </div>

        <div class="form-group">
            <label for="confirm-password">Confirmer mot de passe:</label>
            <input type="password" id="confirm-password" name="confirm-password" class="password-field" required>
        </div>

        <button type="submit" class="btn-submit">OK </button>
    </form>
</body>

</html>