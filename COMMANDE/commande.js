let panier = [];

// Récupérer les produits depuis FastAPI
async function recupererProduits() {
    try {
        const res = await fetch("http://127.0.0.1:8000/produits");
        if (!res.ok) throw new Error("Erreur serveur : impossible de récupérer les produits");
        const produitsDB = await res.json();
        afficherProduits(produitsDB);
    } catch (error) {
        console.error(error);
        alert("❌ Impossible de récupérer les produits. Vérifiez le serveur.");
    }
}

// Afficher les produits par catégorie
function afficherProduits(produitsDB) {
    const container = document.getElementById("categories-container");
    container.innerHTML = "";

    // Créer la liste des catégories dynamiquement
    const categories = [...new Set(produitsDB.map(p => p.categorie))];

    categories.forEach(cat => {
        const catDiv = document.createElement("div");
        catDiv.className = "category";
        catDiv.innerHTML = `<h3>${cat}</h3>`;

        produitsDB.filter(p => p.categorie === cat).forEach(prod => {
            const prodDiv = document.createElement("div");
            prodDiv.className = "product-card";
            prodDiv.innerHTML = `
                <span>${prod.nom} - €${prod.prix}</span>
                <button onclick="ajouterPanier(${prod.id}, '${prod.nom}', ${prod.prix})">
                    <i class="fas fa-cart-plus"></i> Ajouter
                </button>
            `;
            catDiv.appendChild(prodDiv);
        });

        container.appendChild(catDiv);
    });
}

// Ajouter un produit au panier
function ajouterPanier(id, nom, prix) {
    const existing = panier.find(p => p.id === id);
    if (existing) existing.quantite++;
    else panier.push({ id, nom, prix, quantite: 1 });
    afficherPanier();
}

// Supprimer un produit du panier
function supprimerPanier(id) {
    panier = panier.filter(p => p.id !== id);
    afficherPanier();
}

// Afficher le panier avec totaux et statuts avant validation
function afficherPanier() {
    const container = document.getElementById("panier-list");
    container.innerHTML = "";
    let total = 0;

    panier.forEach(item => {
        total += item.prix * item.quantite;
        const div = document.createElement("div");
        div.className = "panier-item";
        div.innerHTML = `
            <span>${item.nom} x${item.quantite} - €${(item.prix * item.quantite).toFixed(2)}</span>
            <span style="margin-left: 10px; font-size: 0.85em; color: gray;">
                Statut commande: en_attente | Paiement: en_attente
            </span>
            <button onclick="supprimerPanier(${item.id})"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(div);
    });

    document.getElementById("panier-total").innerText = total.toFixed(2);
}

// Valider la commande et envoyer au backend FastAPI
async function validerCommande() {
    if (panier.length === 0) { alert("Panier vide !"); return; }
    const mode_paiement = document.getElementById("mode_paiement").value;

    try {
        for (const item of panier) {
            const res = await fetch("http://127.0.0.1:8000/commande", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    produit: item.nom,
                    quantite: item.quantite,
                    montant: item.prix * item.quantite,
                    mode_paiement: mode_paiement,
                    statut_commande: "en_attente",
                    statut_paiement: "en_attente"
                })
            });

            if (!res.ok) throw new Error(`Erreur serveur pour ${item.nom}`);
        }

        alert("✅ Commande validée !");
        panier = [];
        afficherPanier();
    } catch (error) {
        console.error(error);
        alert("❌ Erreur lors de l'envoi de la commande");
    }
}

// Initialisation
recupererProduits();
afficherPanier();