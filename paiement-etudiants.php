
<?php
require_once "connexion.php";
$code="";
if (isset($_POST['Recherche'])) {
    $code = $_POST['code'];

    // Vérifiez si le code est un entier
    if (!is_numeric($code)) {
        echo "ID d'étudiant invalide.";
        exit;
    }

    // Utiliser une requête préparée pour éviter les injections SQL
    $stmt = $conn->prepare('SELECT nom, prenom, niveau FROM etudiants WHERE etudiant_id = ?');
    $stmt->bind_param("i", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifiez si un étudiant a été trouvé
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $nom = $row['nom'];
            $prenom = $row['prenom'];
            $message = $nom . " " . $prenom ;
        }
    } else {
        $message= "Aucun étudiant trouvé avec ce code :".$code;
    }

    $stmt->close();
}

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

        function form_insertion() {
    // Sélectionner l'élément avec l'ID 'about'
    var aboutElement = document.getElementById('form_insertion');
    
    // Alterner l'affichage entre 'block' et 'none'
    if (aboutElement.style.display === 'none' || aboutElement.style.display === '') {
        aboutElement.style.display = 'block';
    } else {
        aboutElement.style.display = 'none';
    }
    
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
        <h1>Liste Des Paiements</h1>
        <form action="" method="POST"  >    

        <div class="form-row">
            <div class="form-group">
                <label for="code">Code D'Etudiant :</label>
                <input type="number" id="code" name="code" class="input-code" min="0" required>
                </div>
                
        </div>

        <button type="submit" value="Recherche" name="Recherche">Recherche</button>
        </form>
        <form action="" method="POST"  >    

            <div class="form-row">
                <div class="form-group">
                    <label for="code">Code D'Etudiant :</label>
                    <input type="number" id="code" name="code" min="0" required>
                </div>
                <div class="form-group">
                    <label for="matiere1">Matière :</label>
                    <select id="matiere1" name="matiere1" required>
                        <option value="">Sélectionner une Matière</option>
                        <?php
                        // Requête pour récupérer les matières
                        $sql_m = "SELECT * FROM matières";
                        $result_m = $conn->query($sql_m);

                        if ($result_m->num_rows > 0) {
                            while ($row_m = $result_m->fetch_assoc()) {
                                echo "<option value='" . $row_m["subject_id"] . "'>" . htmlspecialchars($row_m["nom_matiere"]) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Aucune matière disponible</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="prix_matiere1">Prix Matière :</label>
                    <input type="number" id="prix_matiere1" name="prix_matiere1" min="0" required>
                </div>
            </div>

        <button type="submit" value="enregistrer">Enregistrer</button>
</form>

        <h4><?php echo @$message;?></h4>
        
        <?php 
// Connexion à la base de données (assurez-vous que $conn est correctement initialisé)
if (!$conn) {
    die("Erreur de connexion : " . $conn->connect_error);
}

echo "<table  style='border-collapse: collapse; width: 100%;'>"; // Bordures pour le tableau
echo "<tr >"; // En-tête avec couleur de fond
echo "<th>Code</th>";
 echo "<th>Nom Etudiant</th>";
echo "<th>Code Paiement</th>";
echo "<th>Date De Paiement</th>";
echo "<th>Matières</th>";
echo "<th>Montant</th>";
echo "<th>Action</th>";
echo "</tr>";

// Requête pour récupérer les paiements de chaque matière par étudiant
$sql = "SELECT etudiant_id, DATE(date_paiement) AS date_jour, nom_matiere, montant, paiement_id 
        FROM paiements
        INNER JOIN matières ON paiements.subject_id = matières.subject_id
        ORDER BY  paiement_id DESC"; // Tri par date de paiement puis paiement_id

$stmt_paiement = $conn->query($sql);

if ($stmt_paiement) {
    $currentStudent = null; // Étudiant actuel
    $currentDate = null; // Date actuelle de paiement
    $totalStudentAmount = 0; // Total du montant pour l'étudiant actuel à une même date
    $totalDailyAmount = 0; // Total général de tous les paiements pour la journée

    while ($row_paiement = $stmt_paiement->fetch_assoc()) {
        // Changement d'étudiant
        if ($row_paiement["etudiant_id"] != $currentStudent) {
            // Afficher le total pour l'étudiant précédent
            if ($currentStudent !== null) {
                echo "<tr>";
                echo "<td colspan='5' style='text-align: right;'><strong>Total pour l'étudiant " . htmlspecialchars($rowm['nom'])." " .htmlspecialchars($rowm['prenom']) . ":</strong></td>";
                echo "<td><strong>" . htmlspecialchars($totalStudentAmount) . "</strong></td>";
                echo "<td style='text-align: center;'><a href='recu.php?etudiant_id=" . urlencode($currentStudent) . "&journee=" . urlencode($currentDate) . "' target='_blank'><i class='fa-solid fa-print'></i></a></td>";
                echo "</tr>";
            }

            $totalStudentAmount = 0; // Réinitialiser le total pour le nouvel étudiant
            $currentStudent = $row_paiement["etudiant_id"];
        }

        // Changement de date de paiement
        if ($row_paiement["date_jour"] != $currentDate) {
            // Afficher le total de la journée précédente
            if ($currentDate !== null) {
                echo "<tr style='background-color: #e0e0e0;'>";
                echo "<td colspan='6' style='text-align: center;background-color:#C5D3E8;color:#000B58'><strong><h3>Total de la journée (" . htmlspecialchars($currentDate) . "): " . htmlspecialchars($totalDailyAmount) . " Dhs</h3></strong></td>";
                echo "<td style='text-align: center;background-color:#C5D3E8;'><a style='color:#000B58' href='recu_total.php?journee=" . urlencode($currentDate) . "' target='_blank'><i class='fa-solid fa-print'></i>Totale de la journée</a></td>";
                echo "</tr>";
            }

            // Afficher un titre pour chaque nouvelle date
            echo "<tr style='background-color: #d3d3d3;'><td colspan='7' style='text-align: center;'><h2><strong>Date: " . htmlspecialchars($row_paiement["date_jour"]) . "</strong></td></h2></tr>";

            $totalDailyAmount = 0; // Réinitialiser le total pour la nouvelle journée
            $currentDate = $row_paiement["date_jour"];
        }

        // Afficher les informations de paiement pour chaque matière
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row_paiement["etudiant_id"]) . "</td>";
        $sql="SELECT * FROM etudiants WHERE etudiant_id = ".$row_paiement['etudiant_id'];
        $result=$conn->query($sql);
        if ($rowm = $result->fetch_assoc()) {
            echo "<td>". htmlspecialchars($rowm['nom'])." " .htmlspecialchars($rowm['prenom']). "</td>";
        }
        echo "<td>" . htmlspecialchars($row_paiement["paiement_id"]) . "</td>";
        echo "<td>" . htmlspecialchars($row_paiement["date_jour"]) . "</td>";
        echo "<td>" . htmlspecialchars($row_paiement["nom_matiere"]) . "</td>";
        echo "<td>" . htmlspecialchars($row_paiement["montant"]) . "</td>";
        echo "<td style='text-align: center;'>
    <a href='modifier_etudiant.php?id=" . urlencode($row_paiement['etudiant_id']) . "'>
        <i class='fa-solid fa-pen-to-square'></i>
    </a>
</td>";
        echo "</tr>";

        $totalStudentAmount += $row_paiement["montant"];
        $totalDailyAmount += $row_paiement["montant"];
    }

    // Afficher le total pour le dernier étudiant
    if ($currentStudent !== null) {
        echo "<tr>";
        echo "<td colspan='5' style='text-align: right;'><strong>Total pour l'étudiant " . htmlspecialchars($rowm['nom'])." " .htmlspecialchars($rowm['prenom']) . ":</strong></td>";
        echo "<td><strong>" . htmlspecialchars($totalStudentAmount) . "</strong></td>";
        echo "<td style='text-align: center;'><a href='recu_total.php?etudiant_id=" . urlencode($currentStudent) . "' target='_blank'><i class='fa-solid fa-print'></i></a></td>";
        echo "</tr>";
    }

    // Afficher le total général de la dernière date
    if ($currentDate !== null) {
        echo "<tr style='background-color: #e0e0e0;'>";
        echo "<td colspan='6' style='text-align: center; background-color:#C5D3E8;'><strong><h3>Total de la journée (" . htmlspecialchars($currentDate) . "): " . htmlspecialchars($totalDailyAmount) . " Dhs</h3></strong></td>";
        echo "<td style='text-align: center;background-color:#C5D3E8; '><a style='color:#000B58' href='recu_total.php?journee=" . urlencode($currentDate) . "' target='_blank'><i class='fa-solid fa-print'></i> Totale de la journée</a></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align: center;'>Aucun paiement trouvé</td></tr>";
}

echo "</table>";
?>



    
</div>


   

    </div>

   
</body>
</html>






