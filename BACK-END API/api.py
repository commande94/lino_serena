from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from fastapi.responses import HTMLResponse
from pydantic import BaseModel
from typing import List, Optional
from datetime import datetime
import mysql.connector
from mysql.connector import Error
import os
from dotenv import load_dotenv

# Charger les variables d'environnement
load_dotenv('conf.env')  # Spécifier le nom du fichier

# Configuration de la base de données
db_config = {
    'host': os.getenv('DB_HOST', 'localhost'),
    'user': os.getenv('DB_USER', 'root'),
    'password': os.getenv('DB_PASSWORD', ''),
    'database': os.getenv('DB_NAME', 'lido_serena')
}

# Création de l'application FastAPI
app = FastAPI(
    title="Lido Serena - API Cuisine",
    description="API pour l'application cuisine du restaurant Lido Serena",
    version="1.0.0"
)

# Configuration CORS pour permettre les requêtes depuis les tablettes
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # En production, remplacez par les IPs des tablettes
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Servir les fichiers statiques pour le front-end
try:
    from pathlib import Path
    base_dir = Path(__file__).parent.parent
    app.mount("/cuisine", StaticFiles(directory=str(base_dir / "CUISINE"), html=True), name="cuisine")
    app.mount("/commande", StaticFiles(directory=str(base_dir / "COMMANDE"), html=True), name="commande")
except Exception as e:
    print(f"Avertissement: Impossible de monter les dossiers statiques: {e}")

# Modèles Pydantic pour la validation des données
class PlatCommande(BaseModel):
    id_produit: int
    nom: str
    quantite: int
    prix: float

class CommandeResponse(BaseModel):
    id_com: int
    numero_table: Optional[int] = None
    plats: List[PlatCommande]
    statut_commande: str
    date_commande: datetime
    prix_total: float

class UpdateStatutRequest(BaseModel):
    statut_commande: str

class NotificationResponse(BaseModel):
    message: str
    commande_id: int

# Fonction pour se connecter à la base de données
def get_db_connection():
    try:
        connection = mysql.connector.connect(**db_config)
        return connection
    except Error as e:
        print(f"Erreur de connexion à la base de données: {e}")
        raise HTTPException(status_code=500, detail="Erreur de connexion à la base de données")

# Route de test
@app.get("/", response_class=HTMLResponse)
def read_root():
    try:
        with open("index.html", "r", encoding="utf-8") as f:
            return f.read()
    except FileNotFoundError:
        return """
        <html>
        <head><title>Lido Serena API</title></head>
        <body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
            <h1>🍳 Lido Serena API</h1>
            <p>API en cours de chargement...</p>
            <p><a href="/docs">📚 Documentation Swagger</a></p>
        </body>
        </html>
        """

# Test de connexion à la BDD
@app.get("/test/db")
def test_db():
    try:
        connection = get_db_connection()
        connection.close()
        return {"status": "OK", "message": "Connexion à la BDD réussie"}
    except Exception as e:
        return {"status": "ERROR", "message": str(e)}

# Récupérer toutes les commandes en cuisine
@app.get("/commandes/cuisine", response_model=List[CommandeResponse])
def get_commandes_cuisine():
    """
    Récupère toutes les commandes qui sont actuellement en cuisine
    (statut = 'en cuisine')
    """
    # MODE DEMO - Retourner des données de test
    return [
        CommandeResponse(
            id_com=1,
            numero_table=5,
            plats=[
                PlatCommande(id_produit=1, nom="Pizza Margherita", quantite=2, prix=12.50),
                PlatCommande(id_produit=2, nom="Pâtes Carbonara", quantite=1, prix=14.00),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=39.00
        ),
        CommandeResponse(
            id_com=2,
            numero_table=3,
            plats=[
                PlatCommande(id_produit=3, nom="Burger Deluxe", quantite=3, prix=10.50),
                PlatCommande(id_produit=4, nom="Frites", quantite=3, prix=3.50),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=42.00
        ),
        CommandeResponse(
            id_com=3,
            numero_table=7,
            plats=[
                PlatCommande(id_produit=5, nom="Salade César", quantite=1, prix=9.00),
                PlatCommande(id_produit=6, nom="Poulet Grillé", quantite=1, prix=16.00),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=25.00
        ),
        CommandeResponse(
            id_com=4,
            numero_table=2,
            plats=[
                PlatCommande(id_produit=7, nom="Steak Frites", quantite=1, prix=18.00),
                PlatCommande(id_produit=8, nom="Salade Verte", quantite=1, prix=5.00),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=23.00
        ),
    ]

# Mettre à jour le statut d'une commande
@app.put("/commandes/{commande_id}/statut")
def update_commande_statut(commande_id: int, statut_request: UpdateStatutRequest):
    """
    Met à jour le statut d'une commande spécifique
    Statuts possibles: 'en attente', 'en cuisine', 'prête', 'livrée'
    """
    valid_statuses = ['en attente', 'en cuisine', 'prête', 'livrée']
    
    if statut_request.statut_commande not in valid_statuses:
        raise HTTPException(
            status_code=400, 
            detail=f"Statut invalide. Choisir parmi: {', '.join(valid_statuses)}"
        )
    
    connection = get_db_connection()
    cursor = connection.cursor()
    
    try:
        # Vérifier si la commande existe
        check_query = "SELECT id_com FROM commandes WHERE id_com = %s"
        cursor.execute(check_query, (commande_id,))
        if not cursor.fetchone():
            raise HTTPException(status_code=404, detail="Commande non trouvée")
        
        # Mettre à jour le statut
        update_query = """
            UPDATE commandes 
            SET statut_commande = %s 
            WHERE id_com = %s
        """
        cursor.execute(update_query, (statut_request.statut_commande, commande_id))
        connection.commit()
        
        return {
            "message": f"Statut de la commande {commande_id} mis à jour",
            "nouveau_statut": statut_request.statut_commande,
            "commande_id": commande_id
        }
        
    except Error as e:
        connection.rollback()
        print(f"Erreur lors de la mise à jour: {e}")
        raise HTTPException(status_code=500, detail=f"Erreur base de données: {str(e)}")
    finally:
        cursor.close()
        connection.close()

# Marquer une commande comme prête (spécial cuisine)
@app.post("/commandes/{commande_id}/prete", response_model=NotificationResponse)
def marquer_commande_prete(commande_id: int):
    """
    Marque une commande comme prête et notifie les serveurs
    C'est l'endpoint principal que les cuisiniers utiliseront
    """
    # MODE DEMO - Toujours retourner succès
    return NotificationResponse(
        message=f"✅ Commande #{commande_id} est prête à être servie !",
        commande_id=commande_id
    )

# Récupérer les détails complets d'une commande spécifique
@app.get("/commandes/{commande_id}/details", response_model=CommandeResponse)
def get_commande_details(commande_id: int):
    """
    Récupère tous les détails d'une commande spécifique
    """
    # MODE DEMO
    commandes_demo = [
        CommandeResponse(
            id_com=1,
            numero_table=5,
            plats=[
                PlatCommande(id_produit=1, nom="Pizza Margherita", quantite=2, prix=12.50),
                PlatCommande(id_produit=2, nom="Pâtes Carbonara", quantite=1, prix=14.00),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=39.00
        ),
        CommandeResponse(
            id_com=2,
            numero_table=3,
            plats=[
                PlatCommande(id_produit=3, nom="Burger Deluxe", quantite=3, prix=10.50),
                PlatCommande(id_produit=4, nom="Frites", quantite=3, prix=3.50),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=42.00
        ),
        CommandeResponse(
            id_com=3,
            numero_table=7,
            plats=[
                PlatCommande(id_produit=5, nom="Salade César", quantite=1, prix=9.00),
                PlatCommande(id_produit=6, nom="Poulet Grillé", quantite=1, prix=16.00),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=25.00
        ),
        CommandeResponse(
            id_com=4,
            numero_table=2,
            plats=[
                PlatCommande(id_produit=7, nom="Steak Frites", quantite=1, prix=18.00),
                PlatCommande(id_produit=8, nom="Salade Verte", quantite=1, prix=5.00),
            ],
            statut_commande="en cuisine",
            date_commande=datetime.now(),
            prix_total=23.00
        ),
    ]
    
    for commande in commandes_demo:
        if commande.id_com == commande_id:
            return commande
    
    raise HTTPException(status_code=404, detail="Commande non trouvée")

# Endpoint pour avoir des données de test en dur dans la BDD
@app.post("/test/init-commandes-cuisine")
def init_test_commandes():
    """
    Endpoint de test pour insérer des commandes en cuisine
    (À utiliser uniquement pour les tests)
    """
    connection = get_db_connection()
    cursor = connection.cursor()
    
    try:
        # Vérifier s'il y a déjà des commandes
        cursor.execute("SELECT COUNT(*) FROM commandes WHERE statut_commande = 'en cuisine'")
        count = cursor.fetchone()[0]
        
        if count > 0:
            return {
                "message": f"Il y a déjà {count} commande(s) en cuisine",
                "commandes_existantes": True
            }
        
        # Insérer des commandes de test
        test_commandes = [
            (1, 45.50, 'en cuisine', 'espèces', 'non payé', datetime.now()),
            (1, 32.00, 'en cuisine', 'carte', 'non payé', datetime.now()),
        ]
        
        for staff_id, prix, statut, mode_paiement, statut_paiement, date_com in test_commandes:
            insert_query = """
                INSERT INTO commandes 
                (id_staff, prix_total, statut_commande, mode_paiement, statut_paiement, date_commande)
                VALUES (%s, %s, %s, %s, %s, %s)
            """
            cursor.execute(insert_query, (staff_id, prix, statut, mode_paiement, statut_paiement, date_com))
            commande_id = cursor.lastrowid
            
            # Ajouter des produits de test
            produits_test = [(11, 2), (14, 1)]  # (id_produit, quantite)
            for prod_id, qte in produits_test:
                insert_produit = """
                    INSERT INTO produit_commande (id_com, id_produit, quantite)
                    VALUES (%s, %s, %s)
                """
                cursor.execute(insert_produit, (commande_id, prod_id, qte))
        
        connection.commit()
        
        return {
            "message": "Commandes de test ajoutées avec succès",
            "nombre_commandes": len(test_commandes)
        }
        
    except Error as e:
        connection.rollback()
        print(f"Erreur lors de l'insertion des données de test: {e}")
        raise HTTPException(status_code=500, detail=f"Erreur base de données: {str(e)}")
    finally:
        cursor.close()
        connection.close()

# Pour lancer le serveur
# Pour lancer le serveur
if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api:app", host="0.0.0.0", port=8000, reload=True)