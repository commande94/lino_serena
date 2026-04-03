<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/connexion.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../image/lidorena.png">
    <title>Lido Selena - Inscription</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
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