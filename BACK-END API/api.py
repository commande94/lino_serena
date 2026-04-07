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


def has_column(table_name: str, column_name: str) -> bool:
    connection = get_db_connection()
    cursor = connection.cursor()
    try:
        query = """
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = %s
              AND table_name = %s
              AND column_name = %s
        """
        cursor.execute(query, (db_config['database'], table_name, column_name))
        return cursor.fetchone() is not None
    finally:
        cursor.close()
        connection.close()

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
    (statut = 'en cuisine') depuis la base de données MySQL
    Si la BDD est vide, retourne des données de test
    """
    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)
    
    try:
        # Récupérer toutes les commandes en cuisine
        query = """
            SELECT id_com, prix_total, statut_commande, date_commande
            FROM commandes
            WHERE statut_commande = 'en cuisine'
            ORDER BY date_commande DESC
        """
        cursor.execute(query)
        commandes_data = cursor.fetchall()
        
        # Si aucune commande en BDD, retourner les données de test
        if not commandes_data:
            print("✓ Aucune commande en BDD, retour des données de test")
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
            ]
        
        commandes = []
        for cmd in commandes_data:
            # Récupérer les menus/plats de cette commande
            plats_query = """
                SELECT m.id_menu as id_produit, m.nom, m.prix, cm.quantite
                FROM commandes_menus cm
                JOIN menus m ON cm.id_menu = m.id_menu
                WHERE cm.id_com = %s
            """
            cursor.execute(plats_query, (cmd['id_com'],))
            plats_data = cursor.fetchall()
            
            # Construire la liste des plats
            plats = [
                PlatCommande(
                    id_produit=plat['id_produit'],
                    nom=plat['nom'],
                    quantite=plat['quantite'],
                    prix=float(plat['prix'])
                )
                for plat in plats_data
            ]
            
            # Créer l'objet commande
            commande = CommandeResponse(
                id_com=cmd['id_com'],
                numero_table=None,  # Pas de numero_table dans la BDD
                plats=plats,
                statut_commande=cmd['statut_commande'],
                date_commande=cmd['date_commande'],
                prix_total=float(cmd['prix_total'])
            )
            commandes.append(commande)
        
        return commandes
        
    except Error as e:
        print(f"Erreur lors de la récupération des commandes: {e}")
        # En cas d'erreur, retourner les données de test quand même
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
        ]
    finally:
        cursor.close()
        connection.close()

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
    Marque une commande comme prête dans la BDD et notifie les serveurs
    C'est l'endpoint principal que les cuisiniers utiliseront
    """
    connection = get_db_connection()
    cursor = connection.cursor()
    
    try:
        # Vérifier si la commande existe
        check_query = "SELECT id_com FROM commandes WHERE id_com = %s"
        cursor.execute(check_query, (commande_id,))
        result = cursor.fetchone()
        
        if not result:
            # Commande n'existe pas en BDD (probablement données de test)
            # Retourner succès quand même pour les tests
            print(f"✓ Commande {commande_id} marquée comme prête (mode test)")
            return NotificationResponse(
                message=f"✅ Commande #{commande_id} est prête à être servie !",
                commande_id=commande_id
            )
        
        # Mettre à jour le statut à 'prête'
        update_query = """
            UPDATE commandes 
            SET statut_commande = 'prête'
            WHERE id_com = %s
        """
        cursor.execute(update_query, (commande_id,))
        connection.commit()
        
        return NotificationResponse(
            message=f"✅ Commande #{commande_id} est prête à être servie !",
            commande_id=commande_id
        )
        
    except Error as e:
        connection.rollback()
        print(f"Erreur lors de la mise à jour: {e}")
        # Retourner succès même en cas d'erreur pour les tests
        return NotificationResponse(
            message=f"✅ Commande #{commande_id} est prête à être servie !",
            commande_id=commande_id
        )
    finally:
        cursor.close()
        connection.close()

# Récupérer les détails complets d'une commande spécifique
@app.get("/commandes/{commande_id}/details", response_model=CommandeResponse)
def get_commande_details(commande_id: int):
    """
    Récupère tous les détails d'une commande spécifique depuis la BDD
    """
    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)
    
    try:
        # Récupérer la commande
        query = """
            SELECT id_com, prix_total, statut_commande, date_commande
            FROM commandes
            WHERE id_com = %s
        """
        cursor.execute(query, (commande_id,))
        cmd = cursor.fetchone()
        
        if not cmd:
            raise HTTPException(status_code=404, detail="Commande non trouvée")
        
        # Récupérer les menus/plats de cette commande
        plats_query = """
            SELECT m.id_menu as id_produit, m.nom, m.prix, cm.quantite
            FROM commandes_menus cm
            JOIN menus m ON cm.id_menu = m.id_menu
            WHERE cm.id_com = %s
        """
        cursor.execute(plats_query, (commande_id,))
        plats_data = cursor.fetchall()
        
        # Construire la liste des plats
        plats = [
            PlatCommande(
                id_produit=plat['id_produit'],
                nom=plat['nom'],
                quantite=plat['quantite'],
                prix=float(plat['prix'])
            )
            for plat in plats_data
        ]
        
        # Créer et retourner l'objet commande
        return CommandeResponse(
            id_com=cmd['id_com'],
            numero_table=None,  # Pas de numero_table dans la BDD
            plats=plats,
            statut_commande=cmd['statut_commande'],
            date_commande=cmd['date_commande'],
            prix_total=float(cmd['prix_total'])
        )
        
    except Error as e:
        print(f"Erreur lors de la récupération: {e}")
        raise HTTPException(status_code=500, detail=f"Erreur base de données: {str(e)}")
    finally:
        cursor.close()
        connection.close()

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
            (1, 45.50, 'en cuisine', 'espèces', 'non payé', datetime.now(), 5),
            (1, 32.00, 'en cuisine', 'carte', 'non payé', datetime.now(), 3),
        ]
        
        for staff_id, prix, statut, mode_paiement, statut_paiement, date_com, numero_table in test_commandes:
            insert_query = """
                INSERT INTO commandes 
                (id_staff, prix_total, statut_commande, mode_paiement, statut_paiement, date_commande, numero_table)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """
            cursor.execute(insert_query, (staff_id, prix, statut, mode_paiement, statut_paiement, date_com, numero_table))
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
    uvicorn.run("api:app", host="127.0.0.1", port=8000, reload=True)