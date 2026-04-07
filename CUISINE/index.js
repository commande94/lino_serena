const API_BASE_URL = 'http://localhost:8000';
let autoRefreshInterval;
let timerInterval;

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    const refreshBtn = document.getElementById('refresh-btn');
    refreshBtn.addEventListener('click', fetchCommandes);

    // Charger les commandes au démarrage
    fetchCommandes();

    // Actualisation automatique toutes les 5 secondes
    startAutoRefresh();
});

/**
 * Récupère les commandes depuis l'API
 */
async function fetchCommandes() {
    try {
        updateStatusUI(true, 'Chargement...');

        const response = await fetch(`${API_BASE_URL}/commandes/cuisine`);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const commandes = await response.json();
        updateStatusUI(true, 'Connecté');
        displayCommandes(commandes);

    } catch (error) {
        console.error('Erreur:', error);
        updateStatusUI(false, 'Erreur de connexion');
        displayError(error.message);
    }
}

/**
 * Affiche les commandes à l'écran
 */
function displayCommandes(commandes) {
    const container = document.getElementById('commandes-container');

    if (commandes.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <h2>✅ Aucune commande</h2>
                <p>Toutes les commandes en attente sont prêtes !</p>
            </div>
        `;
        return;
    }

    container.innerHTML = commandes.map(commande => createCommandeCard(commande)).join('');

    // Ajouter les event listeners pour les boutons
    document.querySelectorAll('.btn-prete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const commandeId = parseInt(e.target.dataset.commandeId);
            marquerCommePreet(commandeId, e.target);
        });
    });
}

/**
 * Crée une carte HTML pour une commande
 */
function createCommandeCard(commande) {
    const heureCommande = new Date(commande.date_commande).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    const platsHTML = commande.plats.map(plat => `
        <div class="plat-item">
            <div class="plat-nom">${plat.nom}</div>
            <div class="plat-details">
                Quantité: <span class="plat-quantite">×${plat.quantite}</span> | 
                Prix: ${(plat.prix).toFixed(2)}€
            </div>
        </div>
    `).join('');

    const numeroTableHTML = commande.numero_table
        ? `<span class="numero-table">Table ${commande.numero_table}</span>`
        : '';

    return `
        <div class="commande-card">
            <div class="commande-header">
                <span class="commande-id">Commande #${commande.id_com}</span>
                ${numeroTableHTML}
            </div>
            
            <div class="commande-meta">
                <span class="time">🕐 ${heureCommande}</span>
                <span class="prix-total">${(commande.prix_total).toFixed(2)}€</span>
            </div>
            
            <div class="plats-list">
                <strong>Plats:</strong>
                ${platsHTML}
            </div>
            
            <button class="btn btn-success btn-prete" data-commande-id="${commande.id_com}">
                ✅ Marquer comme prête
            </button>
        </div>
    `;
}

/**
 * Marque une commande comme prête
 */
async function marquerCommePreet(commandeId, button) {
    button.disabled = true;
    button.textContent = '⏳ Envoi...';

    try {
        const response = await fetch(`${API_BASE_URL}/commandes/${commandeId}/prete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Erreur ${response.status}`);
        }

        const result = await response.json();

        // Animation de succès
        button.textContent = '✅ Prête !';
        button.style.background = '#52b788';

        // Refresh après 1 seconde
        setTimeout(fetchCommandes, 1000);

        // Toast/notification
        showNotification(result.message, 'success');

    } catch (error) {
        console.error('Erreur:', error);
        button.disabled = false;
        button.textContent = '❌ Erreur - Réessayer';
        showNotification('Erreur: ' + error.message, 'error');

        setTimeout(() => {
            button.disabled = false;
            button.textContent = '✅ Marquer comme prête';
        }, 3000);
    }
}

/**
 * Affiche un message d'erreur
 */
function displayError(message) {
    const container = document.getElementById('commandes-container');
    container.innerHTML = `
        <div class="empty-state">
            <h2>⚠️ Erreur de connexion</h2>
            <p>Impossible de se connecter à l'API: ${message}</p>
            <p style="margin-top: 10px; font-size: 0.9em;">
                Assurez-vous que l'API tourne à http://localhost:8000
            </p>
        </div>
    `;
}

/**
 * Met à jour le statut de connexion dans l'interface
 */
function updateStatusUI(connected, text) {
    const statusDot = document.getElementById('api-status');
    const statusText = document.getElementById('status-text');

    if (connected) {
        statusDot.classList.remove('disconnected');
        statusDot.classList.add('connected');
    } else {
        statusDot.classList.remove('connected');
        statusDot.classList.add('disconnected');
    }

    statusText.textContent = text;
}

/**
 * Démarre l'actualisation automatique
 */
function startAutoRefresh() {
    // Actualiser tous les 5 secondes
    autoRefreshInterval = setInterval(fetchCommandes, 5000);

    // Minuteur visuel
    let seconds = 5;
    timerInterval = setInterval(() => {
        seconds--;
        document.getElementById('timer').textContent = seconds;

        if (seconds <= 0) {
            seconds = 5;
        }
    }, 1000);
}

/**
 * Affiche une notification temporaire
 */
function showNotification(message, type = 'info') {
    // Créer l'élément notification
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#52b788' : type === 'error' ? '#d62828' : '#004e89'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease;
        max-width: 300px;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Supprimer après 4 secondes
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Ajouter l'animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
