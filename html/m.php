<?php
// 1. Récupération des produits avec leur catégorie (inchangé)
$sql = "SELECT p.id_produit, p.nom, p.prix, c.nom AS categorie_nom
        FROM produits p
        LEFT JOIN categories c ON p.id_category = c.id_category
        ORDER BY p.id_produit";
$produits = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 2. Récupération des catégories (inchangé)
$categories = $pdo->query("SELECT id_category, nom FROM categories ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// 3. NOUVELLE REQUÊTE COMMANDES (Avec Jointures Staff et Produits)
$sql_commandes = "SELECT 
    c.id_com AS id_commande, 
    s.nom AS nom_staff,          -- Récupère le vrai NOM du staff
    c.prix_total AS prix, 
    GROUP_CONCAT(DISTINCT m.nom SEPARATOR ', ') AS noms_menus,   -- Liste des menus
    GROUP_CONCAT(DISTINCT p.nom SEPARATOR ', ') AS noms_produits -- Liste des produits individuels
FROM commandes c
LEFT JOIN staff s ON c.id_staff = s.id_staff                -- Jointure pour le nom de l'employé
LEFT JOIN commandes_menus cm ON c.id_com = cm.id_com        -- Lien commande -> menus
LEFT JOIN menus m ON cm.id_menu = m.id_menu                 -- Récupère le nom du menu
LEFT JOIN produit_menus pm ON m.id_menu = pm.id_menu        -- Lien menu -> produits
LEFT JOIN produits p ON pm.id_produit = p.id_produit        -- Récupère le nom du produit
GROUP BY c.id_com
ORDER BY c.id_com DESC";

$commandes = $pdo->query($sql_commandes)->fetchAll(PDO::FETCH_ASSOC);
?>