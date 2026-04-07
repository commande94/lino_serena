# 🍽️ Lido Serena - Système de Gestion Restauration

Système complet pour gérer les commandes dans un restaurant avec interface cuisine temps réel.

## 📊 Structure du projet

```
lido_serena/
├── ADMIN/
│   ├── lido_serena.sql          # Dump base de données
│   ├── README.md               # Documentation admin
│   ├── consignes.md            # Instructions
│   ├── css/
│   ├── html/                  # Interfaces admin PHP
│   ├── js/
│   └── image/
│
├── BACK-END API/
│   ├── api.py                 # 🔑 API FastAPI principale
│   ├── conf.env               # Configuration base de données
│   ├── requirements.txt        # Dépendances Python
│   ├── run.bat               # Script Windows
│   ├── start.py              # Démarreur Python
│   └── README.md             # Guide d'utilisation
│
├── CUISINE/                   # 🍳 Interface Cuisine
│   ├── index.html            # Interface HTML
│   ├── index.css             # Styles
│   ├── main.js              # Logique (fetch API)
│   └── README.md            # Guide spécifique
│
└── COMMANDE/                  # 📱 Interface Client (WIP)
    ├── index.html
    └── index.css
```

## 🎯 Fonctionnalités

### 🍳 Interface Cuisine
- ✅ Affichage temps réel des commandes
- ✅ Détails complets de chaque commande (table, plats, quantités, prix)
- ✅ Marquer les commandes comme "prêtes"
- ✅ Rafraîchissement automatique (5 secondes)
- ✅ Interface responsive (desktop/tablette/mobile)
- ✅ Notifications visuelles
- ✅ Support plein écran pour tablettes

### 🔧 Backend API
- ✅ Architecture FastAPI moderne
- ✅ API RESTful pour toutes les opérations
- ✅ Connexion MySQL en temps réel
- ✅ CORS activé pour accès de tous les domaines
- ✅ Documentation automatique Swagger (/docs)
- ✅ Gestion des erreurs robuste

### 🛠️ Administration
- ✅ Interface PHP pour gérer les menus
- ✅ Système utilisateur/staff
- ✅ Gestion des produits et catégories

---

## 🚀 Démarrage rapide

### 1. Prérequis

```bash
# Windows
- Python 3.8+
- MySQL/MariaDB
- WAMP64 ou équivalent
```

### 2. Installation

```bash
# Aller dans le dossier backend
cd "BACK-END API"

# Installer les dépendances
pip install -r requirements.txt
```

### 3. Configuration BDD

**Importer la base de données:**
```bash
mysql -u root < ..\ADMIN\lido_serena.sql
```

**Ou via phpMyAdmin:**
- Aller sur http://localhost/phpmyadmin
- Importer `ADMIN/lido_serena.sql`

### 4. Démarrer l'API

**Option A - Script Windows:**
```bash
run.bat
```

**Option B - Script Python:**
```bash
python start.py
```

**Option C - Commande directe:**
```bash
uvicorn api:app --port 8000
```

### 5. Accéder à l'interface

```
🍳 Cuisine:         http://localhost:8000/cuisine
📚 Documentation:   http://localhost:8000/docs
🔍 Test BDD:        http://localhost:8000/test/db
```

---

## 📱 Utilisation

### Interface Cuisine

Chaque commande affiche:
- **Numéro de commande**
- **Heure de passation**
- **Liste complète des plats** avec quantités et prix unitaires
- **Prix total de la commande**
- **Bouton d'action** "✅ Marquer comme prête"

**Actions:**
1. 🔄 Rafraîchir manuellement avec le bouton "Rafraîchir les commandes"
2. ✅ Cliquer "Marquer comme prête" (bouton vert) pour chaque commande
3. ⏰ L'interface rafraîchit **automatiquement toutes les 5 secondes**
4. 🟢 Indicateur **"Connecté"** = API fonctionne
5. Les données affichées proviennent **directement de la BDD MySQL**

---

## � Données en Base de Données

L'interface cuisine affiche **les vraies données** stockées dans MySQL:

### Table `commandes`
- `id_com` - ID unique de la commande
- `id_staff` - Serveur qui a pris la commande
- `prix_total` - Prix total
- `statut_commande` - État (en attente, **en cuisine**, prête, livrée)
- `mode_paiement` - espèces ou carte
- `statut_paiement` - payé ou non payé
- `date_commande` - Timestamp

### Table `commandes_menus`
- Lie les commandes aux menus
- Enregistre la quantité de chaque plat

### Ajouter des commandes de test

Pour tester l'interface, il faut insérer des commandes **en cuisine** en BDD:

```sql
-- Ajouter une commande
INSERT INTO `commandes` (`id_com`, `id_staff`, `prix_total`, `statut_commande`, `mode_paiement`, `statut_paiement`, `date_commande`) 
VALUES (10, 1, 18.00, 'en cuisine', 'carte', 'payé', NOW());

-- Lier un ou plusieurs menus à cette commande
INSERT INTO `commandes_menus` (`id_com`, `id_menu`, `quantite`) 
VALUES (10, 1, 1);
```

⚠️ **Important:** La colonne `statut_commande` doit être **'en cuisine'** pour que la commande s'affiche!

---

## �🔌 API Endpoints

### Récupérer les commandes en cuisine

```
GET /commandes/cuisine
```
Récupère **toutes les commandes en cuisine** depuis la base de données MySQL

**Réponse:**
```json
[
  {
    "id_com": 1,
    "numero_table": null,
    "plats": [
      {
        "id_produit": 1,
        "nom": "Steak Frites",
        "quantite": 1,
        "prix": 18.00
      }
    ],
    "statut_commande": "en cuisine",
    "date_commande": "2026-04-07T15:17:00",
    "prix_total": 18.00
  }
]
```

### Marquer comme prête

```
POST /commandes/{commande_id}/prete
```

**Exemple:**
```bash
curl -X POST http://localhost:8000/commandes/1/prete
```

### Update statut

```
PUT /commandes/{commande_id}/statut
Body: {"statut_commande": "prête"}
```

### Documentation complète

Disponible sur: **http://localhost:8000/docs**

---

## ⚙️ Configuration

### Connexion BDD

Vérifier `BACK-END API/conf.env`:
```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=lido_serena
```

### Port API

Modifier le port de démarrage:
```bash
uvicorn api:app --port 3000
```

Puis mettre à jour `CUISINE/main.js`:
```javascript
const API_BASE_URL = 'http://localhost:3000';
```

### Intervalle rafraîchissement

Dans `CUISINE/main.js`:
```javascript
const REFRESH_INTERVAL = 5000; // ms
```

---

## 🐛 Dépannage

| Problème               | Solution                                       |
| ---------------------- | ---------------------------------------------- |
| "Erreur connexion BDD" | Vérifier MySQL + identifiants dans conf.env    |
| "API non accessible"   | Vérifier que le serveur est démarré :8000      |
| "Modules not found"    | `pip install -r requirements.txt`              |
| "Port déjà utilisé"    | Changer de port dans la commande uvicorn       |
| "CORS error"           | À priori pas de problème (all origins allowed) |

**Test de diagnotic:**
```bash
curl http://localhost:8000/test/db
```

---

## 📚 Documentation détaillée

- **Cuisine**: [CUISINE/README.md](CUISINE/README.md) - Guide complet interface cuisine
- **API**: [BACK-END API/README.md](BACK-END API/README.md) - Documentation technique API
- **Admin**: [ADMIN/README.md](ADMIN/README.md) - Gestion administration

---

## 🔐 Production

**Avant de go en production:**

- [ ] Mettre `allow_origins` à des domaines spécifiques
- [ ] Ajouter authentification JWT
- [ ] Utiliser HTTPS/SSL
- [ ] Configurer firewall/reverse proxy
- [ ] Mettre à jour la BDD en vrai (pas de mode DEMO)
- [ ] Ajouter logging et monitoring
- [ ] Backup/recovery plan
- [ ] Load testing
- [ ] Documenter les incidents

---

## 🎮 Raccourcis clavier

| Touche | Action                   |
| ------ | ------------------------ |
| F5     | Rafraîchir les commandes |
| Ctrl+R | Rafraîchir les commandes |
| Échap  | Fermer les modals        |

---

## 📊 Statistiques

- **Commandes en cuisine**: Affichées en temps réel
- **Rafraîchissement auto**: 5 secondes
- **Temps de réponse API**: < 100ms (mode démo)
- **Interface**: Responsive, 0-1400px

---

## 👥 Équipe

**Développement:** Lido Serena Team  
**Dernière mise à jour:** 7 Avril 2026  
**Version:** 1.0.0  

---

## 📞 Support

Pour les problèmes:
1. Vérifier les logs du serveur FastAPI
2. Ouvrir la console du navigateur (F12)
3. Consulter les README spécifiques
4. Tester avec `curl` ou Postman

---

## 📝 Changelog

### v1.0.0 (7 Avril 2026)
- ✅ Interface cuisine complète
- ✅ API FastAPI fonctionnelle
- ✅ Base de données MySQL
- ✅ Documentation complète
- ✅ Scripts de démarrage
- ✅ Mode DEMO intégré

---

**Bon courage! 🚀**
