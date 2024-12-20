<?php
require_once "connexion.php";

// Initialisation des variables
$etudiant_id = isset($_GET["etudiant_id"]) ? intval($_GET["etudiant_id"]) : null;
$journee = isset($_GET["journee"]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET["journee"]) ? htmlspecialchars($_GET["journee"]) : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre Alpha Bridge - Paiements Journée</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">
    <script>
        function imprimerTableau() {
            var contenuTableau = document.getElementById("tableauPaiements").outerHTML;
            var fenetreImpression = window.open('', '', 'height=600,width=800');
            // Récupérer la date actuelle formatée
            var dateActuelle = new Date();
            var options = { year: 'numeric', month: 'long', day: 'numeric' };
            var dateFormattee = dateActuelle.toLocaleDateString('fr-FR', options);



            fenetreImpression.document.write(`
                <html>
                <head>
                <title></title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
                        th { background-color: #4A628A; color: white; }
                        img { width: 100%; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="http://votre-domaine.com/img/titre.png" alt="Logo Centre Alpha Bridge" onerror="this.onerror=null; this.src='./img/titre.png';"/>
                    </div>
                    <h3>Date de la Journée : ${dateFormattee}</h3>
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
        <?php
        if ($journee) {
            echo "<h1>Centre Alpha Bridge</h1>";
            echo "<h3>Date de la Journée : " . htmlspecialchars($journee) . "</h3>";

            $sql = "
                SELECT 
                    p.paiement_id, 
                    p.etudiant_id, 
                    p.subject_id, 
                    p.montant, 
                    e.nom AS etudiant_nom, 
                    e.prenom AS etudiant_prenom, 
                    m.nom_matiere 
                FROM paiements p
                LEFT JOIN etudiants e ON p.etudiant_id = e.etudiant_id
                LEFT JOIN matières m ON p.subject_id = m.subject_id
                WHERE DATE(p.date_paiement) = ? AND (? IS NULL OR p.etudiant_id = ?)
            ";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssi", $journee, $etudiant_id, $etudiant_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $totalMontant = 0;

                echo "<table id='tableauPaiements'>";
                echo "<tr><th>Code Paiement</th><th>Code Étudiant</th><th>Nom Étudiant</th><th>Matières</th><th>Montant</th></tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["paiement_id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["etudiant_id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["etudiant_nom"]) . " " . htmlspecialchars($row["etudiant_prenom"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nom_matiere"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["montant"]) . " DH</td>";
                    echo "</tr>";
                    $totalMontant += $row["montant"];
                }

                echo "<tr style='font-weight: bold;'><td colspan='4' style='text-align: right;'>Montant Total :</td><td>" . htmlspecialchars($totalMontant) . " DH</td></tr>";
                echo "</table>";
            } else {
                echo "<p>Erreur de préparation de la requête : " . $conn->error . "</p>";
            }
        }
        ?>
        <button onclick="imprimerTableau()" class="button_action">Imprimer le tableau</button>
    </div>
</body>
</html>
