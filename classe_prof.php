<?php
require_once "connexion.php";

// Vérifier si 'id_prof' est fourni dans l'URL
if (isset($_GET['id_prof'])) {
    $prof_id = (int)$_GET['id_prof'];

    // Vérifier si le formulaire 'Ajouter' a été soumis
    if (isset($_POST['aajouter'])) {
        // Récupérer et assainir les données du formulaire
        $classe_id = (int)$_POST['nom_class']; // Assurez-vous que cette donnée provient du formulaire
        $percentage = (int)$_POST['percentage'];
        $newdate = date("Y-m-d");

        // Vérifier si la classe est déjà assignée au professeur
        $checkSQL = "SELECT * FROM prof_classe WHERE prof_id = ? AND classe_id = ?";
        if ($stmt = $conn->prepare($checkSQL)) {
            $stmt->bind_param("ii", $prof_id, $classe_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // La classe est déjà assignée au professeur
                echo "<script>
                        if (confirm('Cette classe est déjà assignée à ce professeur. Voulez-vous modifier les informations ?')) {
                            window.location.href = 'mod_classe_prof.php?id_prof=$prof_id&classe_id=$classe_id&percentage=$percentage';
                        }
                      </script>";
            } else {
                // Ajouter la classe au professeur
                $insertSQL = "INSERT INTO prof_classe (prof_id, classe_id, date_assignation, percentage) 
                              VALUES (?, ?, ?, ?)";
                if ($insertStmt = $conn->prepare($insertSQL)) {
                    $insertStmt->bind_param("iisi", $prof_id, $classe_id, $newdate, $percentage);
                    if ($insertStmt->execute()) {
                        //echo "Classe assignée avec succès.";
                    } else {
                        echo "Erreur lors de l'ajout : " . $insertStmt->error;
                    }
                    $insertStmt->close();
                } else {
                    echo "Erreur lors de la préparation de la requête d'insertion.";
                }
            }
            $stmt->close();
        } else {
            echo "Erreur lors de la préparation de la requête de vérification.";
        }
    }

    // Vérifier si une suppression est demandée
    if (isset($_GET["sup"])) {
        $classe_id = (int)$_GET["sup"];
        $deleteSQL = "DELETE FROM prof_classe WHERE prof_id = ? AND classe_id = ?";
        if ($stmt = $conn->prepare($deleteSQL)) {
            $stmt->bind_param("ii", $prof_id, $classe_id);
            if ($stmt->execute()) {
                echo "Association supprimée avec succès.";
            } else {
                echo "Erreur lors de la suppression : " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erreur lors de la préparation de la requête de suppression.";
        }
    }
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
                <li><a href="etudiants">Tableau de Bord</a></li>
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
        <h1>Gestion des Classes et Professeurs</h1>

        <!-- Formulaire d'ajout ou modification -->
        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                <label for="ref">Nom de la Classe :</label>
                <select id="nom_class" name="nom_class" required>
                    <option value="">Sélectionner une classe</option>
                    <?php
                    $sql = "SELECT * FROM classes group by nom_class";
                    $result_niv = $conn->query($sql);

                    // Vérifier si la requête s'est exécutée correctement
                    if ($result_niv) {
                        if ($result_niv->num_rows > 0) {
                            // Parcourir les résultats et afficher les options
                            while ($row_niv = $result_niv->fetch_assoc()) {
                                echo "<option value=" . htmlspecialchars($row_niv["class_id"]) . ">" . htmlspecialchars($row_niv["nom_class"]) . "</option>";
                            }
                        } else {
                            // Si aucune classe n'est trouvée, afficher un message
                            echo "<option value=''>Aucune classe disponible</option>";
                        }
                    } else {
                        // Gérer l'erreur si la requête échoue
                        echo "<option value=''>Erreur de connexion à la base de données</option>";
                    }
                    ?>
                </select>

                </div>
                <div class="form-group">
                    <label for="montant">Pourcentage :</label>
                    <input type="number" min="0" max="100" id="montant" name="percentage" required>
                </div>

            </div>
            <button type="submit" name="aajouter">Afficher</button>

        </form>

        <!-- Tableau des classes associées -->
        <table>
            <thead>
                <tr>
                    <th>Nom de Classe</th>
                    <th>Nombre d'Étudiants</th>
                    <th>Pourcentage</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
$req = "SELECT pc.*, COUNT(c.etudiant_id) AS nombre_etudiants 
        FROM prof_classe pc 
        INNER JOIN classes c ON pc.classe_id = c.class_id 
        WHERE pc.prof_id = ? 
        GROUP BY pc.classe_id"; // Ajouté GROUP BY pour éviter des erreurs de comptage de lignes répétées
$stmt = $conn->prepare($req);
$stmt->bind_param("i", $_GET['id_prof']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";

        // Préparer une requête sécurisée pour récupérer le nom de la classe
        $class_id = $row['classe_id'];
        $sql = "SELECT nom_class FROM classes WHERE class_id = ?";
        $stmt_class = $conn->prepare($sql);
        $stmt_class->bind_param("i", $class_id);
        $stmt_class->execute();
        $resultclasse = $stmt_class->get_result();

        if ($rowc = $resultclasse->fetch_assoc()) {
            echo "<td>" . htmlspecialchars($rowc['nom_class']) . "</td>";
            $nom_class = $rowc['nom_class']; // Assigner la variable ici pour qu'elle soit utilisée plus tard
        } else {
            echo "<td>Classe non trouvée</td>";
            $nom_class = ''; // En cas d'erreur, on définit $nom_class pour ne pas l'utiliser sans valeur
        }

        $stmt_class->close();

        // Préparer une requête sécurisée pour récupérer le nombre d'étudiants
        $sql = "SELECT COUNT(etudiant_id) AS nombre_etd FROM classes WHERE nom_class = ?";
        $stmt_students = $conn->prepare($sql);
        if ($stmt_students) {
            $stmt_students->bind_param("s", $nom_class);
            $stmt_students->execute();
            $resultclassen = $stmt_students->get_result();

            if ($rowcnom = $resultclassen->fetch_assoc()) {
                echo "<td>" . htmlspecialchars($rowcnom['nombre_etd']) . "</td>";
            } else {
                echo "<td>Classe non trouvée</td>";
            }
            $stmt_students->close();
        }

        // Affichage des autres informations
        echo "<td>" . htmlspecialchars($row['percentage']) . " %</td>";

        // Modification et suppression avec des liens
        echo "<td>
                <a href=commision.php?id_prof=$prof_id&classe=" . $row['classe_id'] . "'><i class='fa-regular fa-eye' style='color:green;'></i></a>
                <a href='?id_prof=$prof_id&sup=" . $row['classe_id']. "'><i class='fa fa-trash' style='color:red;'></i></a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>Aucune classe trouvée.</td></tr>";
}
?>


            </tbody>
        </table>        

    </div>

   
</body>
</html>






