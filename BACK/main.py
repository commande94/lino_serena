# backend/main.py
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from database import get_connection
from models import CommandeCreate, CommandeUpdate

app = FastAPI(title="API Commandes Lido Serena")

# Autoriser le frontend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # adapte à ton front si besoin
    allow_methods=["*"],
    allow_headers=["*"],
)

# -------------------------
# GET /produits
# -------------------------
@app.get("/produits")
def get_produits():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT p.id_produit, p.nom, p.prix, c.nom AS categorie
        FROM produits p
        JOIN categories c ON p.id_category = c.id_category
    """)
    produits = cursor.fetchall()
    cursor.close()
    conn.close()
    return produits

# -------------------------
# GET /commandes
# -------------------------
@app.get("/commandes")
def get_commandes():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT * FROM commandes ORDER BY id_com DESC
    """)
    commandes = cursor.fetchall()
    cursor.close()
    conn.close()
    return commandes

# -------------------------
# POST /commande
# -------------------------
@app.post("/commande")
def add_commande(commande: CommandeCreate):
    conn = get_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("""
            INSERT INTO commandes (produit, quantite, montant, mode_paiement, statut_commande, statut_paiement)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (commande.produit, commande.quantite, commande.montant,
              commande.mode_paiement, commande.statut_commande, commande.statut_paiement))
        conn.commit()
        id_com = cursor.lastrowid
        cursor.close()
        conn.close()
        return {"message": "Commande ajoutée avec succès", "id_com": id_com}
    except Exception as e:
        cursor.close()
        conn.close()
        raise HTTPException(status_code=500, detail=str(e))

# -------------------------
# PATCH /commande/{id_com}
# -------------------------
@app.patch("/commande/{id_com}")
def update_commande(id_com: int, update: CommandeUpdate):
    conn = get_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("""
            UPDATE commandes SET statut_commande = %s WHERE id_com = %s
        """, (update.statut_commande, id_com))
        conn.commit()
        cursor.close()
        conn.close()
        return {"message": "Statut de la commande mis à jour"}
    except Exception as e:
        cursor.close()
        conn.close()
        raise HTTPException(status_code=500, detail=str(e))