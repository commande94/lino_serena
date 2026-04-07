-- Migration SQL pour ajouter les colonnes manquantes à la table commandes
-- À exécuter dans phpMyAdmin ou via la ligne de commande MySQL

USE lido_serena;

-- Ajouter les colonnes manquantes
ALTER TABLE commandes
ADD COLUMN statut_commande ENUM
('en attente','en cuisine','prête','livrée') DEFAULT 'en attente' AFTER prix_total,
ADD COLUMN mode_paiement ENUM
('espèces','carte','chèque') DEFAULT NULL AFTER statut_commande,
ADD COLUMN statut_paiement ENUM
('payé','non payé') DEFAULT 'non payé' AFTER mode_paiement,
ADD COLUMN date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER statut_paiement,
ADD COLUMN numero_table INT DEFAULT NULL AFTER date_commande;

-- Mettre à jour les enregistrements existants avec des valeurs par défaut
UPDATE commandes SET
    statut_commande = 'en attente',
    statut_paiement = 'non payé',
    date_commande = CURRENT_TIMESTAMP
WHERE statut_commande IS NULL;