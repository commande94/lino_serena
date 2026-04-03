from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional
from datetime import datetime
import mysql.connector
from mysql.connector import Error
import os
from dotenv import load_dotenv

# Charger les variables d'environnement
load_dotenv()

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
@app.get("/")
def read_root():
    return {
        "message": "Bienvenue sur l'API Cuisine du Lido Serena",
        "status": "online",
        "endpoints_disponibles": [
            "/commandes/cuisine",
            "/commandes/{commande_id}/statut",
            "/commandes/{commande_id}/prete",
            "/commandes/{commande_id}/details"
        ]
    }

# Récupérer toutes les commandes en cuisine
@app.get("/commandes/cuisine", response_model=List[CommandeResponse])
def get_commandes_cuisine():
    """
    Récupère toutes les commandes qui sont actuellement en cuisine
    (statut = 'en cuisine')
    """
    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)
    
    try:
        # Récupérer les commandes avec statut 'en cuisine'
        query = """
            SELECT 
                c.id_com,
                c.prix_total,
                c.statut_commande,
                c.date_commande,
                COALESCE(tc.numero_table, NULL) as numero_table
            FROM commandes c
            LEFT JOIN tables_commandes tc ON c.id_com = tc.id_com
            WHERE c.statut_commande = 'en cuisine'
            ORDER BY c.date_commande ASC
        """
        
        cursor.execute(query)
        commandes = cursor.fetchall()
        
        resultats = []
        
        for commande in commandes:
            # Récupérer les produits de la commande
            produits_query = """
                SELECT 
                    p.id_produit,
                    p.nom,
                    pc.quantite,
                    p.prix
                FROM produit_commande pc
                JOIN produits p ON pc.id_produit = p.id_produit
                WHERE pc.id_com = %s
            """
            cursor.execute(produits_query, (commande['id_com'],))
            produits = cursor.fetchall()
            
            # Transformer les produits en objets PlatCommande
            plats = [
                PlatCommande(
                    id_produit=p['id_produit'],
                    nom=p['nom'],
                    quantite=p['quantite'],
                    prix=float(p['prix'])
                )
                for p in produits
            ]
            
            resultats.append(
                CommandeResponse(
                    id_com=commande['id_com'],
                    numero_table=commande.get('numero_table'),
                    plats=plats,
                    statut_commande=commande['statut_commande'],
                    date_commande=commande['date_commande'],
                    prix_total=float(commande['prix_total'])
                )
            )
        
        return resultats
        
    except Error as e:
        print(f"Erreur lors de la récupération des commandes: {e}")
        raise HTTPException(status_code=500, detail=f"Erreur base de données: {str(e)}")
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
    Marque une commande comme prête et notifie les serveurs
    C'est l'endpoint principal que les cuisiniers utiliseront
    """
    connection = get_db_connection()
    cursor = connection.cursor()
    
    try:
        # Vérifier que la commande est bien en cuisine
        check_query = """
            SELECT statut_commande FROM commandes 
            WHERE id_com = %s
        """
        cursor.execute(check_query, (commande_id,))
        result = cursor.fetchone()
        
        if not result:
            raise HTTPException(status_code=404, detail="Commande non trouvée")
        
        current_status = result[0]
        
        if current_status != 'en cuisine':
            raise HTTPException(
                status_code=400,
                detail=f"Impossible de marquer comme prête. La commande est actuellement '{current_status}'"
            )
        
        # Mettre à jour le statut à 'prête'
        update_query = """
            UPDATE commandes 
            SET statut_commande = 'prête' 
            WHERE id_com = %s
        """
        cursor.execute(update_query, (commande_id,))
        connection.commit()
        
        # Ici vous pourriez ajouter une logique de notification WebSocket
        # Pour notifier instantanément les serveurs
        
        return NotificationResponse(
            message=f"✅ Commande #{commande_id} est prête à être servie !",
            commande_id=commande_id
        )
        
    except Error as e:
        connection.rollback()
        print(f"Erreur lors du marquage 'prête': {e}")
        raise HTTPException(status_code=500, detail=f"Erreur base de données: {str(e)}")
    finally:
        cursor.close()
        connection.close()

# Récupérer les détails complets d'une commande spécifique
@app.get("/commandes/{commande_id}/details", response_model=CommandeResponse)
def get_commande_details(commande_id: int):
    """
    Récupère tous les détails d'une commande spécifique
    """
    connection = get_db_connection()
    cursor = connection.cursor(dictionary=True)
    
    try:
        # Récupérer la commande
        query = """
            SELECT 
                c.id_com,
                c.prix_total,
                c.statut_commande,
                c.date_commande,
                COALESCE(tc.numero_table, NULL) as numero_table
            FROM commandes c
            LEFT JOIN tables_commandes tc ON c.id_com = tc.id_com
            WHERE c.id_com = %s
        """
        cursor.execute(query, (commande_id,))
        commande = cursor.fetchone()
        
        if not commande:
            raise HTTPException(status_code=404, detail="Commande non trouvée")
        
        # Récupérer les produits
        produits_query = """
            SELECT 
                p.id_produit,
                p.nom,
                pc.quantite,
                p.prix
            FROM produit_commande pc
            JOIN produits p ON pc.id_produit = p.id_produit
            WHERE pc.id_com = %s
        """
        cursor.execute(produits_query, (commande_id,))
        produits = cursor.fetchall()
        
        plats = [
            PlatCommande(
                id_produit=p['id_produit'],
                nom=p['nom'],
                quantite=p['quantite'],
                prix=float(p['prix'])
            )
            for p in produits
        ]
        
        return CommandeResponse(
            id_com=commande['id_com'],
            numero_table=commande.get('numero_table'),
            plats=plats,
            statut_commande=commande['statut_commande'],
            date_commande=commande['date_commande'],
            prix_total=float(commande['prix_total'])
        )
        
    except Error as e:
        print(f"Erreur lors de la récupération des détails: {e}")
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