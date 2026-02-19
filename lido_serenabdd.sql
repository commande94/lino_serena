CREATE TABLE staff (
    id_staff INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    role ENUM('super-admin','admin') NOT NULL DEFAULT 'admin',
    mot_de_passe VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)ENGINE=INNODB;
CREATE TABLE categories (
    id_category INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    id_parent INT NULL,
    FOREIGN KEY (id_parent) REFERENCES categories(id_category)
        ON DELETE SET NULL
)ENGINE=INNODB;
CREATE TABLE produits (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    id_category INT,
    FOREIGN KEY (id_category) REFERENCES categories(id_category)
        ON DELETE SET NULL
)ENGINE=INNODB;
