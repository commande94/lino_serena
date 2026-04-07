-- Script de mise à jour de la BDD Lido Serena
-- (Si la BDD existe déjà, ce script ajoute les colonnes manquantes)

USE lido_serena;

-- Ajouter les colonnes manquantes à la table commandes (si elles n'existent pas)
ALTER TABLE `commandes` ADD COLUMN `statut_commande` varchar(50) NOT NULL DEFAULT 'en attente' AFTER `prix_total`;
ALTER TABLE `commandes` ADD COLUMN `numero_table` int(11) DEFAULT NULL AFTER `statut_commande`;
ALTER TABLE `commandes` ADD COLUMN `date_commande` timestamp NOT NULL DEFAULT current_timestamp() AFTER `numero_table`;

-- Ajouter les données de test pour les menus (vider d'abord si nécessaire)
DELETE FROM commandes_menus;
DELETE FROM commandes;
DELETE FROM menus;

-- Ajouter les menus
INSERT INTO `menus` (`id_menu`, `nom`, `description`, `prix`, `date_creation`, `disponible`) VALUES
(1, 'Pizza Margherita', 'Pizza classique tomate mozzarella', 12.50, '2026-03-01', 1),
(2, 'Pâtes Carbonara', 'Pâtes à la crème et jambon', 14.00, '2026-03-01', 1),
(3, 'Burger Deluxe', 'Double burger viande fromage', 10.50, '2026-03-01', 1),
(4, 'Frites', 'Frites fraiches croustillantes', 3.50, '2026-03-01', 1),
(5, 'Salade César', 'Salade fraiche avec poulet', 9.00, '2026-03-01', 1),
(6, 'Poulet Grillé', 'Poulet fermier grillé', 16.00, '2026-03-01', 1),
(7, 'Steak Frites', 'Steak 200g avec frites', 18.00, '2026-03-01', 1),
(8, 'Salade Verte', 'Salade verte fraiches', 5.00, '2026-03-01', 1);

-- Ajouter les commandes de test
INSERT INTO `commandes` (`id_com`, `id_staff`, `prix_total`, `statut_commande`, `numero_table`, `date_commande`) VALUES
(1, 1, 39.00, 'en cuisine', 5, NOW()),
(2, 1, 42.00, 'en cuisine', 3, NOW()),
(3, 2, 25.00, 'en cuisine', 7, NOW()),
(4, 2, 23.00, 'en cuisine', 2, NOW());

-- Ajouter les liens commandes-menus
INSERT INTO `commandes_menus` (`id_com`, `id_menu`, `quantite`) VALUES
(1, 1, 2),
(1, 2, 1),
(2, 3, 3),
(2, 4, 3),
(3, 5, 1),
(3, 6, 1),
(4, 7, 1),
(4, 8, 1);

-- Mettre à jour les AUTO_INCREMENT
ALTER TABLE `menus` AUTO_INCREMENT = 9;
ALTER TABLE `commandes` AUTO_INCREMENT = 5;

-- Vérifier que tout est bon
SELECT 'Base de données mise à jour avec succès!' as status;
SELECT COUNT(*) as nb_commandes FROM commandes;
SELECT COUNT(*) as nb_menus FROM menus;
