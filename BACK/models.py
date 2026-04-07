# backend/models.py
from pydantic import BaseModel

# Pour créer une commande
class CommandeCreate(BaseModel):
    produit: str
    quantite: int
    montant: float
    mode_paiement: str
    statut_commande: str
    statut_paiement: str

# Pour mettre à jour le statut d'une commande
class CommandeUpdate(BaseModel):
    statut_commande: str