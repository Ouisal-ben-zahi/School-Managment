<?php
require_once "connexion.php";

// Récupération des statistiques avec MySQLi
$total_etudiants = 0;
$total_absences_jour = 0;
$total_paiement_jour = 0.0;
$total_paiement_mois = 0.0;
$total_inscriptions_mois = 0;

// Nombre total d'étudiants
$result = $conn->query("SELECT COUNT(*) AS total FROM etudiants");
if ($result && $row = $result->fetch_assoc()) {
    $total_etudiants = $row['total'] ?? 0;
}

// Nombre d'absences du jour
$result = $conn->query("SELECT COUNT(*) AS total FROM absences WHERE absence_date = CURDATE() AND status = 'absent'");
if ($result && $row = $result->fetch_assoc()) {
    $total_absences_jour = $row['total'] ?? 0;
}

// Total des paiements du jour
$result = $conn->query("SELECT SUM(montant) AS total 
        FROM paiements 
        WHERE DATE(date_paiement) = CURDATE()");
if ($result && $row = $result->fetch_assoc()) {
    $total_paiement_jour = $row['total'] ?? 0.0;
}

// Total des paiements du mois
$result = $conn->query("SELECT SUM(montant) AS total FROM paiements WHERE MONTH(date_paiement) = MONTH(CURDATE())");
if ($result && $row = $result->fetch_assoc()) {
    $total_paiement_mois = $row['total'] ?? 0.0;
}

// Total des inscriptions du mois
$result = $conn->query("SELECT COUNT(*) AS total FROM etudiants WHERE MONTH(date_inscription) = MONTH(CURDATE())");
if ($result && $row = $result->fetch_assoc()) {
    $total_inscriptions_mois = $row['total'] ?? 0;
}

// Récupération des données pour les graphiques
$sql = "SELECT 
    c.nom_class, 
    COUNT(e.etudiant_id) AS total_etudiants, 
    MONTH(e.date_inscription) AS month
FROM 
    etudiants e
JOIN 
    classes c ON e.etudiant_id = c.etudiant_id
WHERE 
    MONTH(e.date_inscription) = MONTH(CURDATE()) 
    OR MONTH(e.date_inscription) = MONTH(CURDATE()) - 1
GROUP BY 
    c.nom_class, MONTH(e.date_inscription)";
    
$stmt = $conn->prepare($sql);
$stmt->execute();

$classes = [];
$total_etudiants_mois_actuel = [];
$total_etudiants_mois_precedent = [];

$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $classes[] = $row['nom_class'];
    if ($row['month'] == date('m')) {
        $total_etudiants_mois_actuel[] = (int)$row['total_etudiants'];
    } else {
        $total_etudiants_mois_precedent[] = (int)$row['total_etudiants'];
    }
}

// Encode les données en JSON
$classes_json = json_encode($classes);
$total_etudiants_mois_actuel_json = json_encode($total_etudiants_mois_actuel);
$total_etudiants_mois_precedent_json = json_encode($total_etudiants_mois_precedent);

// Requête SQL pour obtenir le nombre d'étudiants par niveau
$sql = "SELECT nom_niveau, COUNT(etudiant_id) AS total_etudiants FROM etudiants e join niveau n on n.id_niveau =e.niveau GROUP BY niveau";
$result = $conn->query($sql);

// Récupérer les résultats dans un tableau
$niveau_data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $niveau_data[] = $row;
    }
}

// Requête pour obtenir le nombre d'étudiants ayant payé ce mois
$result = $conn->query("SELECT COUNT(DISTINCT etudiant_id) AS total_paye
                        FROM paiements 
                        WHERE MONTH(date_paiement) = MONTH(CURDATE())");
if ($result && $row = $result->fetch_assoc()) {
    $total_paye_mois = $row['total_paye'] ?? 0;
}

// Calcul du pourcentage des étudiants ayant payé
if ($total_etudiants > 0) {
    $pourcentage_paye = ($total_paye_mois / $total_etudiants) * 100;
} else {
    $pourcentage_paye = 0;
}

// Convertir les données en JSON pour les passer à JavaScript
echo '<script>';
echo 'var niveauData = ' . json_encode($niveau_data) . ';';
echo '</script>';

// Récupérer les données pour les inscriptions mensuelles
$sql = "SELECT MONTH(date_inscription) AS month, COUNT(*) AS total_inscriptions
        FROM etudiants
        GROUP BY MONTH(date_inscription)";
$result = $conn->query($sql);

$mois_data = [];
$inscriptions_par_mois = [];
while ($row = $result->fetch_assoc()) {
    $mois_data[] = $row['month'];
    $inscriptions_par_mois[] = (int)$row['total_inscriptions'];
}

// Convertir les données en JSON pour JavaScript
$mois_data_json = json_encode($mois_data);
$inscriptions_par_mois_json = json_encode($inscriptions_par_mois);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professeurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/bord.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleMenu() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <img src="./img/logo.png" width="30%" alt="logo">
        <h2 style="color:white;">CENTRE ALPHA BRIDGE</h2>
        <nav>
            <ul>
                <li><a href="Tableau.php">Tableau de Bord</a></li>
                <li><a href="etudiant.php">Les Étudiants</a></li>
                <li><a href="professeurs.php">Professeurs</a></li>
                <li><a href="classe.php">Classes</a></li>
                <li><a href="absente.php">Absences</a></li>
                <li><a href="paiement-etudiants.php">Paiement Des Étudiants</a></li>
                <li><a href="paiement_prof.php">Salaire Des Professeurs</a></li>
                <li><a href="depence.php">Dépenses</a></li>
            </ul>
        </nav>
    </div>

    <div class="content">
        <span class="menu-toggle" onclick="toggleMenu()">☰ Menu</span>
        <h1>Tableau de Bord - Centre</h1>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Nombre Total d'Étudiants</div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($total_etudiants) ?></h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Nombre d'Absences (Jour)</div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($total_absences_jour) ?></h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Inscriptions du Mois</div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($total_inscriptions_mois) ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Total des Paiements (Jour)</div>
                        <div class="card-body">
                            <h5 class="card-title"><?= number_format($total_paiement_jour, 2) ?> MAD</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Pourcentage des Étudiants Ayant Payé (Mois Actuel)</div>
                        <div class="card-body">
                            <h5 class="card-title"><?= number_format($pourcentage_paye, 2) ?>%</h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-header">Total des Paiements (Mois)</div>
                        <div class="card-body">
                            <h5 class="card-title"><?= number_format($total_paiement_mois, 2) ?> MAD</h5>
                        </div>
                    </div>
                </div>
            </div>


            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-6">
                        <h2>Graphique des Étudiants par Classe (Mois Actuel vs Mois Précédent)</h2>
                        <canvas id="barChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h2>Répartition des Étudiants par Niveau</h2>
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-12">
                    <h2>Inscriptions Mensuelles</h2>
                    <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Bar chart for students by class for current month vs previous month
        var ctx = document.getElementById('barChart').getContext('2d');
        var barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $classes_json; ?>,
                datasets: [{
                    label: 'Mois Actuel',
                    data: <?php echo $total_etudiants_mois_actuel_json; ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Mois Précédent',
                    data: <?php echo $total_etudiants_mois_precedent_json; ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pie chart for students distribution by level
        var pieCtx = document.getElementById('pieChart').getContext('2d');
        var pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: niveauData.map(item => item.nom_niveau),
                datasets: [{
                    data: niveauData.map(item => item.total_etudiants),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            }
        });


        // Graphique des Inscriptions Mensuelles
var ctxIns = document.getElementById('lineChart').getContext('2d');
var lineChart = new Chart(ctxIns, {
    type: 'line',
    data: {
        labels: <?php echo $mois_data_json; ?>,
        datasets: [{
            label: 'Inscriptions Mensuelles',
            data: <?php echo $inscriptions_par_mois_json; ?>,
            borderColor: '#FF5733',
            fill: false,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

    </script>
</body>
</html>
