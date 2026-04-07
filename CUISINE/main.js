// Configuration API
const API_BASE_URL = 'http://localhost:8000';
const REFRESH_INTERVAL = 5000; // 5 secondes

// État
let commandes = [];
let autoRefresh = true;
let refreshInterval;

// Éléments du DOM
const commandesContainer = document.getElementById('commandes-container');
const refreshBtn = document.getElementById('refresh-btn');
const statusConnection = document.getElementById('status-connection');
const lastUpdateEl = document.getElementById('last-update');
const countEl = document.getElementById('count');
const modal = document.getElementById('confirmation-modal');
const confirmBtn = document.getElementById('confirm-btn');
const cancelBtn = document.getElementById('cancel-btn');
const modalMessage = document.getElementById('modal-message');

let pendingAction = null;

// ============ INITIALISATION ============
document.addEventListener('DOMContentLoaded', () => {
    refreshBtn.addEventListener('click', refreshCommandes);
    confirmBtn.addEventListener('click', confirmAction);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // Premier chargement
    refreshCommandes();

    // Rafraîchissement automatique
    startAutoRefresh();

    // Arrêt du rafraîchissement si l'onglet est en arrière-plan
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
            refreshCommandes();
        }
    });
});

// ============ RÉCUPÉRATION DES COMMANDES ============
async function refreshCommandes() {
    try {
        refreshBtn.disabled = true;
        const response = await axios.get(`${API_BASE_URL}/commandes/cuisine`);
        commandes = response.data;

        updateUI();
        updateStatus(true);
        updateLastUpdate();
        showNotification(`✅ ${commandes.length} commande(s) chargée(s)`, 'success');
    } catch (error) {
        console.error('Erreur lors du chargement:', error);
        updateStatus(false);
        if (commandes.length === 0) {
            showErrorState();
        }
        showNotification(
            `❌ Erreur de connexion à l'API: ${error.response?.status || 'Réseau'}`,
            'error'
        );
    } finally {
        refreshBtn.disabled = false;
    }
}

// ============ AFFICHAGE DES COMMANDES ============
function updateUI() {
    countEl.textContent = commandes.length;

    if (commandes.length === 0) {
        showEmptyState();
        return;
    }

    commandesContainer.innerHTML = commandes
        .map((commande) => createCommandeCard(commande))
        .join('');

    // Ajouter les event listeners
    document.querySelectorAll('.btn-prete').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const id = parseInt(e.target.dataset.commandeId);
            showConfirmModal(id);
        });
    });
}

function createCommandeCard(commande) {
    const table = commande.numero_table || 'À emporter';
    const icon = commande.numero_table ? `🚪 Table ${table}` : `📦 ${table}`;
    const date = new Date(commande.date_commande);
    const timeStr = date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

    const platsHTML = commande.plats
        .map(
            (plat) => `
        <div class="plat-item">
            <div class="plat-details">
                <div class="plat-nom">🍽️ ${plat.nom}</div>
                <div class="plat-quantite">Quantité: <strong>${plat.quantite}</strong></div>
            </div>
            <div class="plat-prix">${(plat.prix * plat.quantite).toFixed(2)}€</div>
        </div>
    `
        )
        .join('');

    const isReady = commande.statut_commande === 'prête';
    const cardClass = isReady ? 'commande-card statut-prete' : 'commande-card';
    const statutClass = isReady ? 'commande-statut prete' : 'commande-statut en-cuisine';
    const statutText = isReady ? '✅ Prête' : '⏳ En cuisine';

    return `
        <div class="${cardClass}" data-commande-id="${commande.id_com}">
            <div class="commande-header">
                <div class="commande-numero">
                    Commande #${commande.id_com}
                    <span>${icon}</span>
                </div>
                <div class="commande-statut ${statutClass}">${statutText}</div>
            </div>

            <div class="commande-body">
                <div class="commande-info">
                    <div class="info-item">
                        <div class="info-label">Heure</div>
                        <div class="info-value">${timeStr}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Plats</div>
                        <div class="info-value">${commande.plats.length}</div>
                    </div>
                </div>

                <div class="plats-section">
                    <div class="plats-title">Plats à préparer:</div>
                    ${platsHTML}
                </div>
            </div>

            <div class="commande-footer">
                <div class="prix-total">
                    <span class="prix-total-label">Total:</span>
                    <span class="prix-total-value">${commande.prix_total.toFixed(2)}€</span>
                </div>
                <div class="actions-commande">
                    <button class="btn btn-success btn-prete" data-commande-id="${commande.id_com}">
                        ✅ Marquer comme prête
                    </button>
                </div>
            </div>
        </div>
    `;
}

// ============ GESTION DU STATUT ============
async function markCommandeReady(commandeId) {
    try {
        const response = await axios.post(
            `${API_BASE_URL}/commandes/${commandeId}/prete`
        );

        // Mettre à jour le statut localement
        const commande = commandes.find((c) => c.id_com === commandeId);
        if (commande) {
            commande.statut_commande = 'prête';
        }

        updateUI();
        showNotification(`✅ Commande #${commandeId} marquée comme prête!`, 'success');
    } catch (error) {
        console.error('Erreur:', error);
        showNotification(
            `❌ Erreur: ${error.response?.data?.detail || 'Impossible de marquer comme prête'}`,
            'error'
        );
    }
}

// ============ MODAL DE CONFIRMATION ============
function showConfirmModal(commandeId) {
    const commande = commandes.find((c) => c.id_com === commandeId);
    if (!commande) return;

    const table = commande.numero_table || 'À emporter';
    modalMessage.textContent = `Marquer la commande #${commandeId} (Table ${table}) comme prête?`;
    pendingAction = commandeId;
    modal.classList.add('show');
}

function confirmAction() {
    if (pendingAction !== null) {
        markCommandeReady(pendingAction);
        closeModal();
    }
}

function closeModal() {
    modal.classList.remove('show');
    pendingAction = null;
}

// ============ RAFRAÎCHISSEMENT AUTOMATIQUE ============
function startAutoRefresh() {
    if (refreshInterval) return; // Ne pas créer plusieurs intervals

    refreshInterval = setInterval(() => {
        if (autoRefresh && document.visibilityState === 'visible') {
            refreshCommandes();
        }
    }, REFRESH_INTERVAL);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
}

// ============ MISES À JOUR UI ============
function updateStatus(online) {
    statusConnection.textContent = online ? '🟢 Connecté' : '🔴 Déconnecté';
    statusConnection.classList.remove('online', 'offline');
    statusConnection.classList.add(online ? 'online' : 'offline');
}

function updateLastUpdate() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    lastUpdateEl.textContent = `Dernier update: ${timeStr}`;
}

function showEmptyState() {
    commandesContainer.innerHTML = `
        <div class="empty-state">
            <div class="empty-state-icon">🍽️</div>
            <p>Aucune commande en cuisine pour le moment</p>
            <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.7;">
                Le rafraîchissement automatique se fera toutes les 5 secondes
            </p>
        </div>
    `;
}

function showErrorState() {
    commandesContainer.innerHTML = `
        <div class="empty-state" style="background: #ffe5e5; border-left: 4px solid #E63946;">
            <div class="empty-state-icon">❌</div>
            <p>Impossible de se connecter à l'API</p>
            <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.7;">
                Vérifiez que le serveur FastAPI est démarré sur http://localhost:8000
            </p>
        </div>
    `;
}

// ============ NOTIFICATIONS ============
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// ============ CLAVIER RACCOURCIS ============
document.addEventListener('keydown', (e) => {
    // F5 ou Ctrl+R pour rafraîchir
    if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
        e.preventDefault();
        refreshCommandes();
    }

    // Echap pour fermer la modal
    if (e.key === 'Escape') {
        closeModal();
    }
});
