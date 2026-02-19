<?php
$host = 'localhost';
$dbname = 'lido_serena';
$username = 'root';     
$password = '';          

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}
?>