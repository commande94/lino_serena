# Lido Serena - API Cuisine

## Configuration de l'API avec données réelles

### 1. Mise à jour de la base de données

Avant de pouvoir utiliser l'API avec des données réelles, vous devez mettre à jour le schéma de la base de données.

**Option A: Via phpMyAdmin**
1. Ouvrez phpMyAdmin
2. Sélectionnez la base `lido_serena`
3. Allez dans l'onglet "SQL"
4. Copiez-collez le contenu du fichier `ADMIN/migration_manuelle.sql`
5. Cliquez sur "Exécuter"

**Option B: Via ligne de commande MySQL**
```bash
mysql -u root -p lido_serena < ADMIN/migration_manuelle.sql
```

### 2. Installation des dépendances

```bash
cd "BACK-END API"
pip install -r requirements.txt
```

### 3. Configuration de la base de données

Vérifiez le fichier `BACK-END API/conf.env` :
```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=lido_serena
```

### 4. Lancement de l'API

```bash
cd "BACK-END API"
python api.py
```

Ou avec uvicorn :
```bash
uvicorn api:app --host 0.0.0.0 --port 8000 --reload
```

### 5. Test de l'API

#### Test de connexion à la BDD
```
GET http://localhost:8000/test/db
```

#### Ajouter des données de test
```
POST http://localhost:8000/test/init-commandes-cuisine
```

#### Récupérer les commandes en cuisine
```
GET http://localhost:8000/commandes/cuisine
```

#### Documentation Swagger
```
http://localhost:8000/docs
```

### 6. Endpoints disponibles

- `GET /commandes/cuisine` - Récupère toutes les commandes en cuisine
- `GET /commandes/{id}/details` - Détails d'une commande spécifique
- `PUT /commandes/{id}/statut` - Met à jour le statut d'une commande
- `POST /commandes/{id}/prete` - Marque une commande comme prête
- `POST /test/init-commandes-cuisine` - Ajoute des données de test

### 7. Structure des données

L'API retourne maintenant des données réelles de la base de données avec :
- Informations sur les commandes (numéro de table, statut, date, prix total)
- Liste des plats commandés avec quantités et prix
- Jointures entre les tables `commandes`, `produit_commande` et `produits`