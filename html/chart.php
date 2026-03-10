<?php
require_once '../php/bdd.php';

// Récupérer tous les produits avec leur prix
$sql = "SELECT nom, prix FROM produits ORDER BY id_produit";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour le graphique
$nomsProduits = [];
$prixProduits = [];

foreach ($produits as $produit) {
    $nomsProduits[] = $produit['nom'];
    $prixProduits[] = $produit['prix'];
}

// Convertir en JSON pour JavaScript
$nomsJSON = json_encode($nomsProduits);
$prixJSON = json_encode($prixProduits);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lido Selena - chart</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

</body>

</html>

<body>

    <h1 class="dashboard-title">Statistiques Live</h1>
    <nav>
        <a href="administration.php" class="btn-back">Retour à l'administration</a>
    </nav>

    <div class="dashboard-wrapper">
        <div class="chart-container">
            <canvas id="myChart"></canvas>
        </div>

        <div class="chart-container1">
            <canvas id="myPie"></canvas>
        </div>

        <div class="chart-container2">
            <canvas id="myLine"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Premier graphique avec les produits et leurs prix
        const ctx = document.getElementById('myChart');
        const nomsProduits = <?php echo $nomsJSON; ?>;
        const prixProduits = <?php echo $prixJSON; ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: nomsProduits,
                datasets: [{
                    label: 'Prix des produits (€)',
                    data: prixProduits,
                    backgroundColor: 'rgba(102, 126, 234, 0.6)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Prix (€)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Prix des produits'
                    }
                }
            }
        });

        //Deuxieme graphique avec le pie chart
        const data = {
            labels: [
                'Red',
                'Blue',
                'Yellow'
            ],
            datasets: [{
                label: 'My First dataset',
                data: [300, 50, 100],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ],
                hoverOffset: 4
            }]
        };
        const config = {
            type: 'pie',
            data: data,
        };
        const pie = document.getElementById('myPie');
        new Chart(pie, config);

        //Troisieme graphique avec le line chart
        const labels_line = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet'];

        const datas_line = {
            labels: labels_line,
            datasets: [
                {
                    label: 'Dataset 1',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    tension: 0.1
                },
                {
                    label: 'Dataset 2',
                    data: [28, 48, 40, 19, 86, 27, 90],
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    tension: 0.1
                }
            ]
        };

        const configu = {
            type: 'line',
            data: datas_line,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Graphique Linéaire (Données Fixes)'
                    }
                }
            },
        };

        const ctxLine = document.getElementById("myLine");
        new Chart(ctxLine, configu);
    </script>

</body>

</html>