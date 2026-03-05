-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 05 mars 2026 à 09:40
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE
= "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone
= "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `lido_serena`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE
IF NOT EXISTS `categories`
(
  `id_category` int NOT NULL AUTO_INCREMENT,
  `nom` varchar
(100) NOT NULL,
  `description` text,
  PRIMARY KEY
(`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
CREATE TABLE
IF NOT EXISTS `commandes`
(
  `id_com` int NOT NULL AUTO_INCREMENT,
  `id_staff` int NOT NULL,
  `prix_total` decimal
(10,2) NOT NULL,
  PRIMARY KEY
(`id_com`),
  KEY `id_staff`
(`id_staff`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commandes_menus`
--

DROP TABLE IF EXISTS `commandes_menus`;
CREATE TABLE
IF NOT EXISTS `commandes_menus`
(
  `id_com` int NOT NULL,
  `id_menu` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  KEY `id_com`
(`id_com`),
  KEY `id_menu`
(`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE
IF NOT EXISTS `menus`
(
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `nom` varchar
(100) NOT NULL,
  `description` text,
  `prix` decimal
(10,2) NOT NULL,
  `date_creation` date DEFAULT NULL,
  `disponible` tinyint
(1) DEFAULT '1',
  PRIMARY KEY
(`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE
IF NOT EXISTS `produits`
(
  `id_produit` int NOT NULL AUTO_INCREMENT,
  `nom` varchar
(150) NOT NULL,
  `prix` decimal
(10,2) NOT NULL,
  `id_category` int DEFAULT NULL,
  PRIMARY KEY
(`id_produit`),
  KEY `id_category`
(`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit_commande`
--

DROP TABLE IF EXISTS `produit_commande`;
CREATE TABLE
IF NOT EXISTS `produit_commande`
(
  `id_com` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int NOT NULL,
  KEY `id_com`
(`id_com`),
  KEY `id_produit`
(`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit_menus`
--

DROP TABLE IF EXISTS `produit_menus`;
CREATE TABLE
IF NOT EXISTS `produit_menus`
(
  `id` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `id_produit` int NOT NULL,
  PRIMARY KEY
(`id`),
  KEY `id_menu`
(`id_menu`),
  KEY `id_produit`
(`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE
IF NOT EXISTS `staff`
(
    `id_staff` int NOT NULL AUTO_INCREMENT,
    `nom` varchar
(100) NOT NULL,
    `prenom` varchar
(100) NOT NULL,
    `email` varchar
(150) NOT NULL,
    `role` enum
('super-admin','admin') NOT NULL DEFAULT 'admin',
    `mot_de_passe` varchar
(255) NOT NULL,
    `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
(`id_staff`),
    UNIQUE KEY `email`
(`email`)
  ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `staff`
--

INSERT INTO `staff` (`
id_staff`,
`nom
`, `prenom`, `email`, `role`, `mot_de_passe`, `date_creation`) VALUES
(1, 'TATIOTSOP ZEBAZE', 'Miguel', 'migueltatiotsop@icloud.com', 'super-admin', '$2y$10$abc123def456GHI789jkl012mno345pq678rst90uvwx', '2026-02-19 09:43:49'),
(2, 'MALBLANC', 'Joackim', 'joackimmalblanc@gmail.com', 'admin', '$2y$10$JE1f4E5xnZsa5CBZ5LCso.nqH5OYyX6OyqruvCVBGuzC/B7b.H5Ey', '2026-02-19 10:47:47'),
(3, 'FRERE', 'Adam', 'adam.frere@gmail.com', 'admin', '$2y$10$GnbIUVw/TLKtLkHePDKow..gzasDYggugi/VTuUBjOMMwv8lz/8Ae', '2026-02-19 11:50:20');

-- --------------------------------------------------------

--
-- Structure de la table `staff_menus`
--

DROP TABLE IF EXISTS `staff_menus`;
CREATE TABLE
IF NOT EXISTS `staff_menus`
(
    `id` int NOT NULL AUTO_INCREMENT,
    `id_staff` int NOT NULL,
    `id_menu` int NOT NULL,
    PRIMARY KEY
(`id`),
    KEY `id_staff`
(`id_staff`),
    KEY `id_menu`
(`id_menu`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY
(`id_staff`) REFERENCES `staff`
(`id_staff`);

--
-- Contraintes pour la table `commandes_menus`
--
ALTER TABLE `commandes_menus`
ADD CONSTRAINT `commandes_menus_ibfk_1` FOREIGN KEY
(`id_com`) REFERENCES `commandes`
(`id_com`),
ADD CONSTRAINT `commandes_menus_ibfk_2` FOREIGN KEY
(`id_menu`) REFERENCES `menus`
(`id_menu`);

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY
(`id_category`) REFERENCES `categories`
(`id_category`) ON
DELETE
SET NULL;

--
-- Contraintes pour la table `produit_commande`
--
ALTER TABLE `produit_commande`
ADD CONSTRAINT `produit_commande_ibfk_1` FOREIGN KEY
(`id_com`) REFERENCES `commandes`
(`id_com`),
ADD CONSTRAINT `produit_commande_ibfk_2` FOREIGN KEY
(`id_produit`) REFERENCES `produits`
(`id_produit`);

--
-- Contraintes pour la table `produit_menus`
--
ALTER TABLE `produit_menus`
ADD CONSTRAINT `produit_menus_ibfk_1` FOREIGN KEY
(`id_menu`) REFERENCES `menus`
(`id_menu`),
ADD CONSTRAINT `produit_menus_ibfk_2` FOREIGN KEY
(`id_produit`) REFERENCES `produits`
(`id_produit`);

--
-- Contraintes pour la table `staff_menus`
--
ALTER TABLE `staff_menus`
ADD CONSTRAINT `staff_menus_ibfk_1` FOREIGN KEY
(`id_staff`) REFERENCES `staff`
(`id_staff`),
ADD CONSTRAINT `staff_menus_ibfk_2` FOREIGN KEY
(`id_menu`) REFERENCES `menus`
(`id_menu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
