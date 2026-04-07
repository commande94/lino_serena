-- Migration script to update the commandes table with missing columns
-- Run this script after updating the schema in lido_serena.sql

USE lido_serena;

-- Add missing columns to commandes table
ALTER TABLE commandes
ADD COLUMN statut_commande ENUM
('en attente','en cuisine','prête','livrée') DEFAULT 'en attente',
ADD COLUMN mode_paiement ENUM
('espèces','carte','chèque') DEFAULT NULL,
ADD COLUMN statut_paiement ENUM
('payé','non payé') DEFAULT 'non payé',
ADD COLUMN date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN numero_table INT DEFAULT NULL;

-- Update existing records with default values if needed
UPDATE commandes SET statut_commande = 'en attente' WHERE statut_commande IS NULL;
UPDATE commandes SET statut_paiement = 'non payé' WHERE statut_paiement IS NULL;
UPDATE commandes SET date_commande = CURRENT_TIMESTAMP WHERE date_commande IS NULL;