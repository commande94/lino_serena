# 🍳 Système de Gestion Cuisine - Lido Serena

## 📋 Vue d'ensemble

Ce système permet aux cuisiniers de visualiser les commandes en temps réel et de les marquer comme prêtes.

**Architecture:**
- **Frontend (Cuisine)**: Interface web responsive pour les cuisiniers (`CUISINE/index.html`)
- **Backend API**: API FastAPI (`BACK-END API/api.py`) qui gère les données
- **Base de Données**: MySQL (`lido_serena.sql`)

---

## 🚀 Mise en place

### 1. Prérequis

- **Python 3.8+**
- **MySQL/MariaDB** en cours d'exécution
- **WAMP64** ou similaire (optionnel, pour le serveur web)

### 2. Installation des dépendances

Depuis le dossier `BACK-END API/`:

```bash
pip install -r requirements.txt
```

**Packages installés:**
- `fastapi` - Framework web
- `uvicorn` - Serveur ASGI
- `mysql-connector-python` - Connexion MySQL
- `python-dotenv` - Gestion des variables d'environnement
- `pydantic` - Validation des données

### 3. Configuration de la Base de Données

**Option A - Windows (avec WAMP):**
1. Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Importer `ADMIN/lido_serena.sql`

**Option B - Ligne de commande:**
```bash
mysql -u root -p < ADMIN/lido_serena.sql
```

Vérifier les identifiants dans `BACK-END API/conf.env`:
```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=lido_serena
```

---

## 🎮 Démarrage

### Via le script batch (Windows)

```bash
cd "BACK-END API"
run.bat
```

### Via la ligne de commande

```bash
cd "BACK-END API"
uvicorn api:app --host 0.0.0.0 --port 8000 --reload
```

### Via Python directement

```bash
cd "BACK-END API"
python -m uvicorn api:app --host 0.0.0.0 --port 8000
```

**L'API démarrera sur:** `http://localhost:8000`

---

## 🌐 Accès aux interfaces

### 1. Interface Cuisine 
**URL:** `http://localhost:8000/cuisine`

Affiche les commandes en cuisine avec:
- ✅ Numéro de commande
- ✅ Table concernée
- ✅ Liste des plats avec quantités
- ✅ Heure de la commande
- ✅ Prix total
- ✅ Bouton "Marquer comme prête"

**Fonctionnalités:**
- 🔄 Rafraîchissement automatique toutes les 5 secondes
- 🔄 Bouton de rafraîchissement manuel
- 📞 Notifications de statut
- 📱 Interface responsive (desktop, tablette, mobile)
- ⌨️ Raccourcis clavier (F5 pour rafraîchir, Échap pour fermer les modals)

### 2. Documentation API
**URL:** `http://localhost:8000/docs`

Interface interactive Swagger avec tous les endpoints.

### 3. API REST

#### Récupérer les commandes en cuisine
```
GET /commandes/cuisine
```

**Réponse:**
```json
[
  {
    "id_com": 1,
    "numero_table": 5,
    "plats": [
      {
        "id_produit": 1,
        "nom": "Pizza Margherita",
        "quantite": 2,
        "prix": 12.50
      }
    ],
    "statut_commande": "en cuisine",
    "date_commande": "2026-04-07T14:30:00",
    "prix_total": 39.00
  }
]
```

#### Marquer une commande comme prête
```
POST /commandes/{commande_id}/prete
```

**Exemple:**
```bash
curl -X POST http://localhost:8000/commandes/1/prete
```

#### Mettre à jour le statut
```
PUT /commandes/{commande_id}/statut
```

**Body:**
```json
{
  "statut_commande": "prête"
}
```

---

## 📱 Interface Cuisine - Guide d'utilisation

### Affichage des commandes

Chaque commande est affichée sous forme de carte contenant:

1. **En-tête (bleu)**
   - Numéro de commande
   - Numéro de table/À emporter
   - Statut (En cuisine / Prête)

2. **Corps (blanc)**
   - Heure de passation
   - Nombre de plats
   - Liste complète des plats avec:
     - Nom du plat 🍽️
     - Quantité
     - Prix

3. **Pied de page (gris)**
   - Prix total
   - Bouton "Marquer comme prête" (vert)

### Actions

**Marquer une commande comme prête:**
1. Cliquer sur le bouton vert "✅ Marquer comme prête"
2. Confirmer dans le modal de confirmation
3. La commande s'affichera en vert avec un badge "✅ Prête"

**Rafraîchir les commandes:**
- Cliquer sur "🔄 Rafraîchir les commandes"
- Ou appuyer sur **F5**
- Ou attendre le rafraîchissement automatique (5 secondes)

### Indicateurs

| Indicateur   | Signification           |
| ------------ | ----------------------- |
| 🟢 Connecté   | API disponible          |
| 🔴 Déconnecté | API non accessible      |
| ⏳ En cuisine | Commande en préparation |
| ✅ Prête      | Commande prête à servir |

---

## 🔧 Configuration avancée

### Modifier le port de l'API

**Dans le terminal:**
```bash
uvicorn api:app --port 3000
```

Puis mettre à jour `CUISINE/main.js`:
```javascript
const API_BASE_URL = 'http://localhost:3000';
```

### Modifier l'intervalle de rafraîchissement

Dans `CUISINE/main.js`:
```javascript
const REFRESH_INTERVAL = 5000; // 5 secondes
```

### CORS (Accès depuis d'autres domaines)

L'API accepte actuellement les requêtes de tous les domaines (`allow_origins=["*"]`).

Pour restreindre: dans `api.py`, modifier:
```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000", "http://192.168.1.100"],
    ...
)
```

---

## ⚡ Optimisation

### Performance
- Les commandes se rafraîchissent toutes les 5 secondes
- Le rafraîchissement s'arrête si l'onglet est en arrière-plan
- Utilisation de la mise en cache côté client

### Pour les tablettes de cuisine
- Interface grande et facile à lire
- Boutons grands et tactiles
- Notifications visibles
- Support du mode plein écran

---

## 🐛 Dépannage

### "Erreur de connexion à la BDD"
- ✅ Vérifier que MySQL est en cours d'exécution
- ✅ Vérifier les identifiants dans `conf.env`
- ✅ Vérifier que la base `lido_serena` existe

### "API non accessible"
- ✅ Vérifier que le serveur FastAPI est démarré
- ✅ Vérifier l'adresse: `http://localhost:8000/docs`
- ✅ Vérifier les pare-feu

### Les commandes ne se mettent pas à jour
- ✅ Vérifier la connexion réseau
- ✅ Ouvrir la console du navigateur (F12) pour voir les erreurs
- ✅ Vérifier que l'API répond: `http://localhost:8000/test/db`

### "Module not found"
```bash
pip install --upgrade -r requirements.txt
```

---

## 📊 Structure de la base de données

### Table `commandes`
```
- id_com: INT (identifiant unique)
- id_staff: INT (serveur)
- prix_total: DECIMAL(10,2)
```

### Table `commandes_menus`
```
- id_com: INT (lien commande)
- id_menu: INT (lien menu)
- quantite: INT
```

### Table `menus`
```
- id_menu: INT
- nom: VARCHAR(100)
- description: TEXT
- prix: DECIMAL(10,2)
- date_creation: DATE
- disponible: TINYINT
```

---

## 🔐 Sécurité

**À faire en production:**
- [ ] Remplacer `allow_origins=["*"]` par des domaines spécifiques
- [ ] Ajouter une authentification (JWT tokens)
- [ ] Utiliser HTTPS avec certificats SSL
- [ ] Ajouter un rate limiting
- [ ] Valider toutes les entrées utilisateur
- [ ] Ajouter des logs de sécurité

---

## 📝 Notes

- L'API est actuellement en **mode DEMO** (données fictives)
- Pour utiliser la vraie BDD, modifier `@app.get("/commandes/cuisine")`
- Les changements de statut sont persistés dans la BDD
- Le système supporte les tablettes Android et iPad

---

## 📞 Support

Pour toute question ou problème, consulter:
- Documentation FastAPI: https://fastapi.tiangolo.com
- Logs d'erreur dans la console du serveur
- Console du navigateur (F12) pour les erreurs client

---

**Dernière mise à jour:** 7 Avril 2026  
**Version:** 1.0.0
