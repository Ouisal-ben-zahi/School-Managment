<?php
require_once "connexion.php";

// Fonction pour récupérer les paiements par étudiant
function getPayments($conn, $etudiant_id, $month, $subject) {
    $sql_payment = "SELECT montant FROM paiements 
                    WHERE etudiant_id = ? AND subject_id = ? 
                    AND DATE_FORMAT(date_paiement, '%Y-%m') = ?";
    $stmt_payment = $conn->prepare($sql_payment);
    $stmt_payment->bind_param("iis", $etudiant_id, $subject, $month);
    $stmt_payment->execute();
    $result_payment = $stmt_payment->get_result();
    $montant = $result_payment->fetch_assoc()['montant'] ?? 0;
    $stmt_payment->close();
    return floatval($montant);
}

// Fonction pour afficher les étudiants et leurs paiements
function displayStudentPayments($conn, $subject, $niveau, $month) {
    $sql = "SELECT etudiant_id FROM etudiant_matiere WHERE subject_id = ? AND id_niveau = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $subject, $niveau);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_paiement = 0;

    echo "<table id='tableauPaiements'>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Nom Complet</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Montant Payé</th>
                </tr>
            </thead>
            <tbody>";

    while ($row = $result->fetch_assoc()) {
        $etudiant_id = intval($row['etudiant_id']);
        $sql_student = "SELECT nom, prenom, telephone, email FROM etudiants WHERE etudiant_id = ?";
        $stmt_student = $conn->prepare($sql_student);
        $stmt_student->bind_param("i", $etudiant_id);
        $stmt_student->execute();
        $result_student = $stmt_student->get_result();

        if ($result_student->num_rows > 0) {
            $student = $result_student->fetch_assoc();
            $montant = getPayments($conn, $etudiant_id, $month, $subject);
            $total_paiement += $montant;

            echo "<tr>
                    <td>" . htmlspecialchars($etudiant_id) . "</td>
                    <td>" . htmlspecialchars($student['nom']) . " " . htmlspecialchars($student['prenom']) . "</td>
                    <td>" . htmlspecialchars($student['telephone']) . "</td>
                    <td>" . htmlspecialchars($student['email']) . "</td>
                    <td>" . ($montant > 0 ? htmlspecialchars($montant) . " DH" : "Non payé") . "</td>
                  </tr>";
        }
        $stmt_student->close();
    }
    echo "</tbody></table>";
    $stmt->close();

    echo "<h4>Total des paiements pour " . date('F Y', strtotime($month)) . ": " . number_format($total_paiement, 2) . " DH</h4>";
    return $total_paiement;
}

// Récupérer les données de la classe et du professeur
try {
    if (isset($_GET["classe"]) && isset($_GET["id_prof"])) {
        $id_prof = intval($_GET["id_prof"]);
        $classe_id = intval($_GET["classe"]);

        // Récupérer le pourcentage pour le professeur
        $sql = "SELECT percentage FROM prof_classe WHERE prof_id = ? AND classe_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_prof, $classe_id);
        $stmt->execute();
        $percentage = floatval($stmt->get_result()->fetch_assoc()['percentage'] ?? 0) / 100;
        $stmt->close();

        // Récupérer les informations de la classe
        $sql_class = "SELECT nom_class, subject_id, niveau FROM classes WHERE class_id = ?";
        $stmt_class = $conn->prepare($sql_class);
        $stmt_class->bind_param("i", $classe_id);
        $stmt_class->execute();
        $class_info = $stmt_class->get_result()->fetch_assoc();
        $stmt_class->close();

        if (!$class_info) {
            die("Classe non trouvée.");
        }

        $subject = intval($class_info['subject_id']);
        $niveau = intval($class_info['niveau']);
        $nom_class = htmlspecialchars($class_info['nom_class']);
    } else {
        die("Paramètres invalides.");
    }
} catch (Exception $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Paiements - Centre Alpha Bridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">
    <script>
        function toggleMenu() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        }
    function imprimerTableau() {
        // Récupérer uniquement les lignes de tableau avec le nom et le montant
        var rows = document.querySelectorAll("#tableauPaiements tr");
        var contenuTableau = "<table style='width: 100%; border-collapse: collapse;'>";
        contenuTableau += "<tr><th>Nom Étudiant</th><th>Montant</th></tr>"; // En-têtes pour nom et montant

        rows.forEach(function(row, index) {
            // Ignorer l'en-tête du tableau (index 0)
            if (index > 0) {
                var columns = row.querySelectorAll("td");
                var nomEtudiant = columns[1].textContent;  // Nom complet de l'étudiant
                var montant = columns[4].textContent;      // Montant payé
                contenuTableau += "<tr><td>" + nomEtudiant + "</td><td>" + montant + "</td></tr>";
            }
        });

        // Ouvrir la fenêtre d'impression
        var fenetreImpression = window.open('', '', 'height=600,width=800');
        
        // Récupérer la date actuelle formatée
        var dateActuelle = new Date();
        var options = { year: 'numeric', month: 'long', day: 'numeric' };
        var dateFormattee = dateActuelle.toLocaleDateString('fr-FR', options);

        fenetreImpression.document.write(`
            <html>
            <head>
                <title>Impression du Tableau des Paiements</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
                    th { background-color: #4A628A; color: white; }
                    img { width: 100%; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <!-- Affichage du logo dans l'entête -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="http://votre-domaine.com/img/titre.png" alt="Logo Centre Alpha Bridge" onerror="this.onerror=null; this.src='./img/titre.png';"/>
                </div>
                
                <!-- Affichage de la date et du tableau -->
                <h3>Date de la Journée : ${dateFormattee}</h3>
                <h4>Tableau des Paiements des Étudiants</h4>
                <hr>
                ${contenuTableau}
            </body>
            </html>
        `);

        fenetreImpression.document.close();

        fenetreImpression.onload = function () {
            fenetreImpression.print();
        };
    }


    </script>
</head>
<body>
    <div class="sidebar">
        <img src="./img/logo.png" width="30%" alt="logo">
        <h2>CENTRE ALPHA BRIDGE</h2>
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
        <h2>Classe : <?php echo $nom_class; ?></h2>
        <h1>Suivi des Paiements</h1>

        <form method="GET" action="">
            <input type="hidden" name="classe" value="<?php echo $classe_id; ?>">
            <input type="hidden" name="id_prof" value="<?php echo $id_prof; ?>">
            <label for="month">Choisir un mois :</label>
            <div class="form-row">
         <div class="form-group">
            <select id="month" name="month">
    <?php
    // Nombre total de mois affichés (par exemple, 12 mois au total : 6 avant, actuel, et 5 après)
    $totalMonths = 12;
    $currentMonthIndex = 10; // L'index du mois actuel dans la liste

    for ($i = -$currentMonthIndex; $i < $totalMonths - $currentMonthIndex; $i++) {
        $time = strtotime("$i months", strtotime(date('Y-m-01'))); // Calcule le mois
        $value = date('Y-m', $time); // Format pour la valeur
        $label = date('F Y', $time); // Texte affiché
        // Détermine si le mois est sélectionné
        $selected = (isset($_GET['month']) && $_GET['month'] === $value) || $value === date('Y-m') ? ' selected' : '';
        echo "<option value='$value'$selected>$label</option>";
    }
    ?>
</select>
</div></div>

            <button type="submit">Afficher</button>
        </form>

        <?php
        $selected_month = $_GET['month'] ?? date('Y-m');
        $total_paiement = displayStudentPayments($conn, $subject, $niveau, $selected_month);
        echo "<h4>Total pour le professeur : " . number_format($total_paiement * $percentage, 2) . " DH</h4>";
        ?>

        <button onclick="imprimerTableau()">Imprimer le tableau</button>
    </div>
</body>
</html>
