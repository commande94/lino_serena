from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List
import mysql.connector

app = FastAPI()

# ------------------- CORS -------------------
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# ------------------- CONNEXION DB -------------------
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="lido_serena"
)

# ------------------- SCHEMAS -------------------
class ProduitCommande(BaseModel):
    id_produit: int
    quantite: int

class Commande(BaseModel):
    produits: List[ProduitCommande]
    montant: float
    mode_paiement: str
    statut_commande: str
    statut_paiement: str

# ------------------- ROUTES -------------------

# 🔹 Obtenir tous les produits avec leurs catégories
@app.get("/produits")
def get_produits():
    cursor = db.cursor(dictionary=True)
    cursor.execute("""
        SELECT p.id_produit, p.nom, p.prix, c.nom AS nom_categorie
        FROM produits p
        JOIN categories c ON p.id_category = c.id_category
    """)
    produits = cursor.fetchall()
    cursor.close()
    return produits

# 🔹 Ajouter une commande complète avec tous les produits
@app.post("/commande")
def ajouter_commande(data: Commande):
    cursor = db.cursor()

    # 1️⃣ Ajouter la commande dans la table commandes
    cursor.execute("""
        INSERT INTO commandes (montant, mode_paiement, statut_commande, statut_paiement)
        VALUES (%s, %s, %s, %s)
    """, (data.montant, data.mode_paiement, data.statut_commande, data.statut_paiement))
    db.commit()
    id_com = cursor.lastrowid  # Récupère l'ID généré de la commande

    # 2️⃣ Ajouter chaque produit de la commande dans produits_commandes
    for p in data.produits:
        cursor.execute("""
            INSERT INTO produit_commande (id_com, id_produit, quantite)
            VALUES (%s, %s, %s)
        """, (id_com, p.id_produit, p.quantite))
    db.commit()
    cursor.close()

    return {"message": "Commande ajoutée avec succès", "id_com": id_com}

# 🔹 Obtenir toutes les commandes
@app.get("/commandes")
def get_commandes():
    cursor = db.cursor(dictionary=True)
    cursor.execute("SELECT * FROM commandes ORDER BY id_com DESC")
    commandes = cursor.fetchall()
    cursor.close()
    return commandes