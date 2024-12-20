
<?php

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etudiants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">

    <script >
        function toggleMenu() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden'); // Ajout/suppression de la classe "hidden"
        }
    </script>
</head>
<body>
    
    <div class="sidebar" >
        <img src="./img/logo.png" width="30%" alt="logo">
        <h2 style="color:white;">CENTRE ALPHA BRIDGE</h2>
        <nav>
        <ul>
                <li><a href="Tableau.php">Tableau de Bord</a></li>
                <li><a href="etudiant.php">Les Étudiants</a></li>
                <li><a href="professeurs.php">Professeurs</a></li>
                <li><a href="classe.php">Classes</a></li>
                <li><a href="absente.php">Absences </a></li>
                <li><a href="paiement-etudiants.php">Paiement Des Étudiants</a></li>
                <li><a href="paiement_prof.php">Salaire Des Professeurs</a></li>
                <li><a href="depence.php">Dépenses</a></li>
            </ul>
        </nav>
    </div>

    
<div class="content">
        <span class="menu-toggle" onclick="toggleMenu()">☰ Menu</span> <!-- Bouton pour ouvrir/fermer le menu -->
        
        <table>
    <tr> <th>Code</th>
        <th>Date D'inscription</th>
        <th>Nom Complet</th>
        <th>Téléphone</th>
        <th>Email</th>
        <th>Statut Paiement</th>
    </tr>
    <?php
require_once "connexion.php";

if (isset($_GET['id']) && isset($_GET['matiere'])) {
    $classname = $_GET['id'];
    $subject = $_GET['matiere'];
    echo "<h1>Classe : " . htmlspecialchars($classname) . "</h1>";

    // Préparation de la requête pour éviter les injections SQL
    $stmt = $conn->prepare("SELECT * FROM classes WHERE nom_class = ?");
    $stmt->bind_param("s", $classname);
    $stmt->execute();
    $result_etud = $stmt->get_result();

    if ($result_etud->num_rows > 0) {
        while ($row = $result_etud->fetch_assoc()) {
            echo "<tr>";
            $rq = "SELECT * FROM etudiants WHERE etudiant_id = " . intval($row['etudiant_id']);
            $result_etudiant = $conn->query($rq);

            // Vérification si la requête pour les étudiants a retourné des résultats
            if ($rowm = $result_etudiant->fetch_assoc()) {
                echo "<td>" . htmlspecialchars($rowm['etudiant_id']) . "</td>";
                echo "<td>" . htmlspecialchars($rowm['date_inscription']) . "</td>";
                echo "<td>" . htmlspecialchars($rowm['nom']) . " " . htmlspecialchars($rowm['prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($rowm['telephone']) . "</td>";
                echo "<td>" . htmlspecialchars($rowm['email']) . "</td>";
            } else {
                echo "<td colspan='5'>Aucun étudiant trouvé</td>";
            }

            // Récupération de la dernière date de paiement pour l'étudiant et la matière
            $rq_paiement = "SELECT MAX(date_paiement) AS last_payment FROM paiements WHERE etudiant_id = " . intval($row['etudiant_id']) . " AND subject_id = " . intval($subject);
            $result_paiement = $conn->query($rq_paiement);
            $paiement_trouve = false;

            if ($row_paiement = $result_paiement->fetch_assoc()) {
                $paiement_trouve = true;
                $date_paiement = new DateTime($row_paiement['last_payment']);
                $date_actuelle = new DateTime();

                // Vérification du changement de mois
                $dernier_mois_paye = $date_paiement->format('m');
                $mois_actuel = $date_actuelle->format('m');
                $annee_paye = $date_paiement->format('Y');
                $annee_actuelle = $date_actuelle->format('Y');

                // Si le mois ou l'année a changé et que le paiement n'a pas été effectué pour le nouveau mois
                if ($mois_actuel != $dernier_mois_paye || $annee_actuelle != $annee_paye) {
                    echo "<td style='color:red;'>Retard de paiement</td>";
                } else {
                    echo "<td style='color:green;'>À jour</td>";
                }
            }

            if (!$paiement_trouve) {
                echo "<td>Aucun paiement trouvé</td>";
            }

            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Aucune classe trouvée</td></tr>";
    }

    $stmt->close();
}
?>

</table>

        
</div>

   
</body>
</html>






