let panier = [];
let produitsDB = [];

// Récupérer les produits depuis FastAPI
async function fetchProduits() {
    try {
        const response = await fetch("http://127.0.0.1:9000/produits");  // port FastAPI
        produitsDB = await response.json();
        afficherProduits();
    } catch (err) {
        console.error("Erreur lors de la récupération des produits:", err);
    }
}

// Afficher les produits par catégorie
function afficherProduits() {
    const container = document.getElementById("categories-container");
    container.innerHTML = "";
    const categories = [...new Set(produitsDB.map(p => p.nom_categorie))];

    categories.forEach(cat => {
        const catDiv = document.createElement("div");
        catDiv.className = "category";
        catDiv.innerHTML = `<h3>${cat}</h3>`;
        produitsDB.filter(p => p.nom_categorie === cat).forEach(prod => {
            const prodDiv = document.createElement("div");
            prodDiv.className = "product-card";
            prodDiv.innerHTML = `
                <span>${prod.nom} - €${prod.prix}</span>
                <button onclick="ajouterPanier(${prod.id_produit})"><i class="fas fa-cart-plus"></i> Ajouter</button>
            `;
            catDiv.appendChild(prodDiv);
        });
        container.appendChild(catDiv);
    });
}

// Ajouter au panier
function ajouterPanier(id) {
    const produit = produitsDB.find(p => p.id_produit === id);
    const existing = panier.find(p => p.id_produit === id);
    if (existing) existing.quantite++;
    else panier.push({ ...produit, quantite: 1 });
    afficherPanier();
}

// Supprimer du panier
function supprimerPanier(id) {
    panier = panier.filter(p => p.id_produit !== id);
    afficherPanier();
}

// Afficher le panier
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
            <button onclick="supprimerPanier(${item.id_produit})"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(div);
    });
    document.getElementById("panier-total").innerText = total.toFixed(2);
}

// Valider la commande
async function validerCommande() {
    if (panier.length === 0) {
        alert("Panier vide !");
        return;
    }
    const mode_paiement = document.getElementById("mode_paiement").value;

    // Calcul du montant total
    const montantTotal = panier.reduce((acc, item) => acc + item.prix * item.quantite, 0);

    // Préparer la liste des produits au format attendu par FastAPI
    const produitsData = panier.map(item => ({
        id_produit: item.id_produit,
        quantite: item.quantite
    }));

    try {
        const response = await fetch("http://127.0.0.1:9000/commande", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                produits: produitsData,
                montant: montantTotal,
                mode_paiement: mode_paiement,
                statut_commande: "commande reçue",
                statut_paiement: "en_attente"
            })
        });

        const data = await response.json();
        alert("✅ Commande validée ! ID: " + data.id_com);
        panier = [];
        afficherPanier();
    } catch (err) {
        console.error("Erreur lors de l'envoi de la commande:", err);
    }
}
// Initialisation
fetchProduits();
afficherPanier();