<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.html');
    exit();
}
require_once '../php/bdd.php';

// Récupérer les catégories dynamiquement depuis la base de données
$sqlCat = "SELECT nom FROM categories ORDER BY id_category";
$stmtCat = $pdo->query($sqlCat);
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$nomsCategories = [];
foreach ($categories as $cat) {
    $nomsCategories[] = $cat['nom'];
}

// On transforme les catégories en JSON pour le JS
$categoriesJSON = json_encode($nomsCategories);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lido Selena - Statistiques</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <h1 class="dashboard-title" style="background-color: white;">Statistiques Live</h1>
    <nav>
        <span class="welcome-user" style="background-color: white;">Bienvenue, <?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></span>
        <a href="administration.php" class="btn-back" style="background-color: white;">Retour à l'administration</a>
        <a href="../php/logout.php" class="btn-logout" style="background-color: white;">Déconnexion</a>
    </nav>

    <div class="dashboard-wrapper">
        <div class="chart-container" style="background-color: white;">
            <canvas id="myChart"></canvas>
        </div>

        <div class="chart-container1" style="background-color: white;">
            <canvas id="myPie"></canvas>
        </div>

        <div class="chart-container2" style="background-color: white;">
            <canvas id="myLine"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        Chart.defaults.color = '#000000';
        Chart.defaults.font.weight = '600';

        // --- 1. GRAPHIQUE BARRES : JOURS DE LA SEMAINE ---
        const ctxBar = document.getElementById('myChart');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
                datasets: [{
                    label: 'Revenus journaliers (€)',
                    data: [1200, 1900, 1500, 2100, 2800, 4500, 3800],
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: { title: { display: true, text: 'Chiffre d\'affaires de la semaine' } }
            }
        });

        // --- 2. GRAPHIQUE CAMEMBERT : RAPPORTS PAR CATÉGORIE (AVEC €) ---
        const ctxPie = document.getElementById('myPie');
        const labelsCategories = <?php echo $categoriesJSON; ?>; 
        
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labelsCategories, 
                datasets: [{
                    data: [450, 1200, 800, 600], 
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0'],
                    hoverOffset: 15
                }]
            },
            options: {
                plugins: { 
                    title: { display: true, text: 'Part des revenus par catégorie' },
                    legend: { position: 'bottom' },
                    // C'est ici qu'on ajoute le symbole € au survol
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed !== null) {
                                    label += new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(context.parsed);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // --- 3. GRAPHIQUE LINÉAIRE : CA VS BÉNÉFICES (COULEURS DISTINCTES) ---
        const ctxLine = document.getElementById("myLine");
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet'],
                datasets: [
                    {
                        label: 'Chiffre d\'Affaires (€)',
                        data: [5000, 4800, 7000, 7500, 9000, 12000, 15000],
                        borderColor: '#2e59d9', // Bleu foncé
                        backgroundColor: 'rgba(46, 89, 217, 0.2)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5
                    },
                    {
                        label: 'Bénéfices (€)',
                        data: [2000, 1800, 3500, 3800, 4500, 6500, 8500],
                        borderColor: '#1cc88a', // Vert émeraude
                        backgroundColor: 'rgba(28, 200, 138, 0.2)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                plugins: { 
                    title: { display: true, text: 'Performance Financière Mensuelle' }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return value + ' €'; }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>