<?php
if (isset($_GET["id"])) {
    $code = $_GET["id"];
    
    require_once "connexion.php";  

    // Préparer et exécuter la requête pour l'étudiant
    $stmt = $conn->prepare("SELECT * FROM etudiant_matiere WHERE etudiant_id = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $resultat_etud = $stmt->get_result();


    $reqe="select nom,prenom,niveau from etudiants where etudiant_id ="."$code";
    $req_etud=$conn->query($reqe);
    $row_e=$req_etud->fetch_assoc();


} 
// Vérification si des résultats sont obtenus
if (isset($resultat_etud) && $resultat_etud->num_rows > 0) {

    // Fermer la requête préparée
    $stmt->close();

}






if (isset($_POST["ajouter"])) {
    // Assurez-vous que les entrées de l'utilisateur sont sécurisées
    $subject = $_POST["subject"];
    $prix = $_POST["prix"];
    $code = $_GET["id"]; // Assurez-vous que l'ID de l'étudiant est passé via un formulaire
    $status = 'ajoute';
    $currentTimestamp = date('Y-m-d H:i:s'); // format standard pour MySQL

    // Connexion à la base de données
    $conn = new mysqli('localhost', 'root', '', 'alphabridge');

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("Connexion échouée : " . $conn->connect_error);
    }

    // Fonction pour vérifier si la matière est déjà attribuée à l'étudiant
    function verifierDoublon($conn, $code, $subject) {
        $sql = "SELECT * FROM etudiant_matiere WHERE etudiant_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $code, $subject);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Récupération du niveau de l'étudiant
    $req = "SELECT niveau FROM etudiants WHERE etudiant_id = ?";
    $stmt_niveau = $conn->prepare($req);
    $stmt_niveau->bind_param("s", $code);
    $stmt_niveau->execute();
    $result_niveau = $stmt_niveau->get_result();
    $row_niveau = $result_niveau->fetch_assoc();
    $id_niveau = $row_niveau["niveau"]; // Niveau de l'étudiant

    // Insertion ou mise à jour de la matière
    function ajouterOuModifierMatiere($conn, $code, $subject, $prix, $status, $id_niveau, $currentTimestamp) {
        $message_ajouter = "";

        // Vérifier si la matière existe déjà
        $result = verifierDoublon($conn, $code, $subject);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Vérifier si le statut est "supprimé"
            if ($row['status'] === 'supprime') {
                // Mettre à jour la matière
                $sql_update = "UPDATE etudiant_matiere SET date_modification = ?, status = ?, prix = ? WHERE etudiant_id = ? AND subject_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssss", $currentTimestamp, $status, $prix, $code, $subject);

                // Exécuter la mise à jour
                if ($stmt_update->execute()) {
                    // Préparer la requête pour insérer dans paiements
                    $sql_paiement = "INSERT INTO paiements (etudiant_id, subject_id, montant, date_paiement) 
                                     VALUES (?, ?, ?, ?)";
                    $stmt_paiement = $conn->prepare($sql_paiement);
                    $stmt_paiement->bind_param("ssss", $code, $subject, $prix, $currentTimestamp);

                    if ($stmt_paiement->execute()) {
                        $message_ajouter = "La matière a été mise à jour avec succès.";
                    } else {
                        $message_ajouter = "Erreur lors de l'ajout du paiement.";
                    }
                    $stmt_paiement->close();
                } else {
                    $message_ajouter = "Erreur lors de la mise à jour de la matière.";
                }
                $stmt_update->close();
            } else {
                $message_ajouter = "La matière existe déjà avec le statut : " . $row['status'] . ".";
            }
        } else {
            // Ajouter la matière si elle n'existe pas
            $sql_insert = "INSERT INTO etudiant_matiere (etudiant_id, subject_id, date_modification, status, prix, id_niveau) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssss", $code, $subject, $currentTimestamp, $status, $prix, $id_niveau);

            // Exécuter la requête d'insertion
            if ($stmt_insert->execute()) {
                // Préparer la requête pour insérer dans paiements
                $sql_paiement = "INSERT INTO paiements (etudiant_id, subject_id, montant, date_paiement) 
                                 VALUES (?, ?, ?, ?)";
                $stmt_paiement = $conn->prepare($sql_paiement);
                $stmt_paiement->bind_param("ssss", $code, $subject, $prix, $currentTimestamp);

                if ($stmt_paiement->execute()) {
                    $message_ajouter = "La matière a été ajoutée avec succès.";
                } else {
                    $message_ajouter = "Erreur lors de l'ajout du paiement.";
                }
                $stmt_paiement->close();
            } else {
                $message_ajouter = "Erreur lors de l'ajout de la matière.";
            }
            $stmt_insert->close();
        }

        // Récupération du nom de la matière
        $sql_nom_sub = "SELECT nom_matiere FROM matières WHERE subject_id = ?";
        $stmt_nom_sub = $conn->prepare($sql_nom_sub);
        $stmt_nom_sub->bind_param("s", $subject);
        $stmt_nom_sub->execute();
        $result_nom_sub = $stmt_nom_sub->get_result();
        $row_nom_sub = $result_nom_sub->fetch_assoc();

        // Vérifier si le nom de la matière a été récupéré
        if ($row_nom_sub) {
            $nom_matiere = $row_nom_sub["nom_matiere"];
        } else {
            $nom_matiere = "Inconnu"; // Ou gérer l'erreur comme vous le souhaitez
        }

        // Récupération du nom du niveau
        $sql_nom_niv = "SELECT nom_niveau FROM niveau WHERE id_niveau = ?";
        $stmt_nom_niv = $conn->prepare($sql_nom_niv);
        $stmt_nom_niv->bind_param("s", $id_niveau);
        $stmt_nom_niv->execute();
        $result_nom_niv = $stmt_nom_niv->get_result();
        $row_nom_niv = $result_nom_niv->fetch_assoc();

        // Vérifier si le nom du niveau a été récupéré
        if ($row_nom_niv) {
            $nom_niveau = $row_nom_niv["nom_niveau"];
        } else {
            $nom_niveau = "Inconnu"; // Ou gérer l'erreur comme vous le souhaitez
        }

        // Insertion dans la table classes
        $nom_class = $nom_matiere . "/" . $nom_niveau; // Définir nom_class

        $sql_classe = "INSERT INTO classes (etudiant_id, subject_id, niveau, nom_class) 
                       VALUES (?, ?, ?, ?)";
        $stmt_classe = $conn->prepare($sql_classe);
        $stmt_classe->bind_param("ssss", $code, $subject, $id_niveau, $nom_class);
        
        if ($stmt_classe->execute()) {
            $message_ajouter .= " Classe ajoutée avec succès.";
        } else {
            $message_ajouter .= " Erreur lors de l'ajout de la classe.";
        }
        $stmt_classe->close();

        return $message_ajouter;
    }

    // Appel de la fonction pour ajouter ou mettre à jour la matière
    $message_ajouter = ajouterOuModifierMatiere($conn, $code, $subject, $prix, $status, $id_niveau, $currentTimestamp);

    // Redirection vers la page de l'étudiant avec message
    header("Location: modifier_etudiant.php?id=" . $code . "&message=" . urlencode($message_ajouter));
    exit();

    // Fermer la connexion
    $conn->close();
}

function supprimerMatiere($conn, $etudiant_id, $subject_id, $currentTimestamp) {
    // Correction de la requête SQL pour mettre à jour le statut
    $sql = "UPDATE etudiant_matiere SET date_modification = ?, status = 'supprime' WHERE etudiant_id = ? AND subject_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $currentTimestamp, $etudiant_id, $subject_id); // Correct binding types
    
    if ($stmt->execute()) {
        // Suppression dans la table classes
        $sql_classe = "DELETE FROM classes WHERE etudiant_id = ? AND subject_id = ?"; // Utilisation de DELETE au lieu de DROP
        $stmt_classe = $conn->prepare($sql_classe);
        $stmt_classe->bind_param("ss", $etudiant_id, $subject_id); // Correct binding types
        
        if ($stmt_classe->execute()) {

            echo "<script>alert('La matière a été supprimée avec succès.');</script>";
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$code");

            echo "<script>alert('Erreur lors de la suppression de la matière dans les classes.');</script>";
        }
        $stmt_classe->close(); // Ferme le statement après exécution
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$code");

        echo "<script>alert('Erreur lors de la suppression de la matière.');</script>";
    }
    
    $stmt->close(); // Ferme le statement après exécution
}












// Fonction pour supprimer une matière
if (isset($_POST["supprimer"])) {
    $subject_id = $_POST["subject"];
    $currentTimestamp = date('Y-m-d H:i:s'); // Format standard pour MySQL
    supprimerMatiere($conn, $code, $subject_id, $currentTimestamp); // Passe le timestamp à la fonction
    header("Location: modifier_etudiant.php?id=" . $code); // Redirection vers la page d'information de l'étudiant
    exit(); // Assurez-vous d'ajouter exit après une redirection
}



if (isset($_POST["paye"])) {
    // Vérification des paramètres GET
    if (
        isset($_GET['id']) && 
        isset($_GET['date_paiement']) && 
        isset($_GET['montant']) && 
        isset($_GET['subject_id'])&&(isset($_POST["paye"]))
    ) {
        // Récupérer les données en GET et les échapper pour éviter les injections SQL
        $etudiant_id_p = htmlspecialchars($_GET['id']);
        $date_paiement_p = htmlspecialchars($_GET['date_paiement']);
        $montant_p = htmlspecialchars($_GET['montant']);
        $subject_id_ = htmlspecialchars($_GET['subject_id']);
        $prix=$_POST["prix"];
        $subje=$_POST["subject"];
        // Préparer la requête de mise à jour
        $sql_mp = "UPDATE paiements SET montant = ?, subject_id = ? WHERE etudiant_id = ? AND date_paiement = ? AND subject_id = ?";

        // Préparer la requête
        if ($stmt_mp = $conn->prepare($sql_mp)) {
            // Lier les variables aux paramètres
            $stmt_mp->bind_param("iissi", $prix, $subje, $etudiant_id_p, $date_paiement_p, $subject_id_);

            // Exécuter la requête
            if ($stmt_mp->execute()) {
                // Redirection après succès
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=$etudiant_id_p");
                exit(); // Terminer le script après la redirection
            } else {
                // Redirection en cas d'erreur
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=$etudiant_id_p");
                exit(); // Terminer le script après la redirection
            }

            // Fermer la requête préparée
            $stmt_mp->close();
        } else {
            // Redirection en cas d'erreur de préparation
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$etudiant_id_p");
            exit(); // Terminer le script après la redirection
        }
    } else {
        // Si les paramètres GET ne sont pas définis, effectuer un nouvel enregistrement
        $subject = $_POST["subject"];
        $prix = $_POST["prix"];
        $currentTimestamp = date('Y-m-d H:i:s'); // format standard pour MySQL

        // Préparer la requête d'insertion
        $sql_paiement = "INSERT INTO paiements (etudiant_id, subject_id, montant, date_paiement) VALUES (?, ?, ?, ?)";
        $stmt_paiement = $conn->prepare($sql_paiement);

        if ($stmt_paiement) { // Vérifier si la préparation de la requête a réussi
            $stmt_paiement->bind_param("ssss", $code, $subject, $prix, $currentTimestamp);

            // Exécuter la requête
            if ($stmt_paiement->execute()) {
                // Redirection après succès
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=$code");
                exit(); // Terminer le script après la redirection
            } else {
                // Redirection en cas d'erreur
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=$code");
                exit(); // Terminer le script après la redirection
            }

            $stmt_paiement->close(); // Fermer le statement après utilisation
        } else {
            // Redirection en cas d'erreur de préparation
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$code");
            exit(); // Terminer le script après la redirection
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
    
    <div class="sidebar"  >
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
        <h1> Bienvenue Chez <span  style="color:#FC4100;"><?php echo $row_e["prenom"]." ".$row_e["nom"] ?></span></h1>
        
<form action="" method="POST" id="form1" class="p-4 border rounded bg-light" style="display:none;">
    <!-- Affichage du message d'ajout -->
    <?php if (!empty($message_ajouter)) : ?>
        <div id="message" class="alert alert-info"><?php echo htmlspecialchars($message_ajouter); ?></div>
    <?php endif; ?>

    <h3>Ajouter Une Matière</h3>
    
    <!-- Sélection de la matière -->
    <div class="form-row">
        <div class="form-group">        
            <label for="matiere" class="form-label">Sélectionner une matière :</label>
            <select id="matiere4" name="subject" class="form-select" required>
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

    <!-- Champ pour le prix -->
    <div class="form-group">
        <label for="prix" class="form-label">Prix :</label>
        <input type="number" id="prix" name="prix" class="form-control" placeholder="Entrez le prix" required>
    </div>
            </div>
    <!-- Bouton Valider -->
    <button type="submit" name="ajouter" class=" button btn btn-primary">Valider</button>
    <div style="font-weight:bold;color:red;"><?php  
    if (isset($_GET["message"])) {
        echo htmlspecialchars($_GET["message"]);
    }
    ?></div>
</form>






<!-- Formulaire pour supprimer une matière -->
<form action="" method="POST" id="form2" style="display:none;">
    <h3>Supprimer Une Matière</h3>
    <div class="form-row">
        <div class="form-group">
    <label for="matiere_supprimer">Sélectionner une matière à supprimer :</label>
    <select id="matiere4" name="subject">
    <option value="">Sélectionner une Matière</option>
    <?php
        // Requête pour récupérer les matières
        $sql_mt = "SELECT M.subject_id, M.nom_matiere 
                    FROM etudiant_matiere EM 
                    INNER JOIN matières M ON EM.subject_id = M.subject_id 
                    WHERE EM.etudiant_id = ? AND EM.status = 'ajoute'";
        
        // Préparer la requête
        $stmt_mt = $conn->prepare($sql_mt);
        $stmt_mt->bind_param("s", $code); // Liaison de l'ID de l'étudiant
        $stmt_mt->execute();
        $result_mt = $stmt_mt->get_result(); // Récupérer le résultat de la requête

        if ($result_mt->num_rows > 0) {
            while ($row_m = $result_mt->fetch_assoc()) {
                echo "<option value='" . $row_m["subject_id"] . "'>" . $row_m["nom_matiere"] . "</option>";
            }
        } else {
            echo "<option value=''>Aucune matière disponible</option>";
        }

        $stmt_mt->close(); // Fermer le statement
    ?>
</select>
    </div>
    </div>
    <!-- Bouton Supprimer -->
    <button type="submit" class="button" name="supprimer">Supprimer</button>
</form>







<form action="" method="POST" id="form3" class="p-4 border rounded bg-light" style="display:none;">
    <h3>Paiement D'une Matière</h3>
    
    <!-- Sélection de la matière -->
    <div class="form-row">
    <div class="form-group">
        <label for="matiere" class="form-label">Sélectionner une matière :</label>
        <select id="matiere4" name="subject" required>
    <option value="">Sélectionner une Matière</option>
    <?php
        // Requête pour récupérer les matières
        $sql_mt = "SELECT M.subject_id, M.nom_matiere 
                    FROM etudiant_matiere EM 
                    INNER JOIN matières M ON EM.subject_id = M.subject_id 
                    WHERE EM.etudiant_id = ? AND EM.status = 'ajoute'";
        
        // Préparer la requête
        $stmt_mt = $conn->prepare($sql_mt);
        $stmt_mt->bind_param("s", $code); // Liaison de l'ID de l'étudiant
        $stmt_mt->execute();
        $result_mt = $stmt_mt->get_result(); // Récupérer le résultat de la requête

        if ($result_mt->num_rows > 0) {
            while ($row_m = $result_mt->fetch_assoc()) {
                echo "<option value='" . $row_m["subject_id"] . "'>" . $row_m["nom_matiere"] . "</option>";
            }
        } else {
            echo "<option value=''>Aucune matière disponible</option>";
        }

        $stmt_mt->close(); // Fermer le statement
    ?>
</select>

    </div>

    <!-- Champ pour le prix -->
    <div class="form-group">
        <label for="prix" class="form-label">Prix :</label>
        <input type="number" id="prix" name="prix" class="form-control"value="<?php echo isset($_GET['montant']) ? htmlspecialchars($_GET['montant']) : ''; ?>" value="<?php if(isset($_GET['montant'])) echo $_GET['montant'];?>" min=0 placeholder="Entrez le prix" required>
    </div>
</div>
    <!-- Bouton Valider -->
    <button type="submit" name="paye" class=" button btn btn-primary">Payé</button>
    
</form>








<div class="all-button">
<div><button onclick="form_supprimer()" class="button_action">Supprimer</button></div>
<div><button onclick="form_insertion()" class="button_action">Ajouter</button></div>
<div><button onclick="form_paiement()" class="button_action">Payé</button></div>
<div><button onclick="classe()"  class="button_action">Classe</button> </div>
<div><button class="button_action">Absente</button></div>
<div><button onclick="parent()" class="button_action">Parent</button></div>

</div>


<div id="tablePaiement" style="display:none;">
    <?php
    echo "<table >"; // Ajout de la bordure pour le tableau
    echo "<tr>";
    echo "<th>Code</th>";
    echo "<th>Code Paiement</th>";
    echo "<th>Date De Paiement</th>";
    echo "<th>Matières</th>";
    echo "<th>Montant</th>";
    echo "<th>Action</th>";
    echo "</tr>";

    // Requête pour récupérer les paiements
    $sql = "SELECT * FROM paiements WHERE etudiant_id = ? ORDER BY date_paiement desc"; // Ajout d'un ordre par date
    $stmt_paiement = $conn->prepare($sql);
    $stmt_paiement->bind_param("s", $code); // Liaison de l'ID de l'étudiant
    $stmt_paiement->execute();
    $result_paiement = $stmt_paiement->get_result(); // Récupérer le résultat

    // Vérifier s'il y a des paiements
    if ($result_paiement->num_rows > 0) {
        $current_date = ""; // Variable pour stocker la date actuelle
        $daily_total = 0; // Variable pour le total quotidien

        while ($row_paiement = $result_paiement->fetch_assoc()) {
            // Récupérer la date du paiement
            $payment_date = date('Y-m-d', strtotime($row_paiement["date_paiement"])); // Format de date

            // Vérifier si la date a changé
            if ($payment_date !== $current_date) {
                // Si c'est une nouvelle date, afficher une ligne de séparation et le titre de la date
                if ($current_date !== "") {
                    // Afficher le total des paiements pour la journée précédente
                    echo "<tr><td colspan='3' style='text-align:right; font-weight:bold;'>Total pour le " . htmlspecialchars($current_date) . " :</td>";
                    echo "<td style='font-weight:bold;'>" . htmlspecialchars($daily_total) . "</td></tr>";
                    //echo "<tr><td colspan='4'><hr></td></tr>"; // Ligne de séparation
                }
                // Afficher le titre de la date
                echo "<tr><td colspan='6' style='text-align:center; font-weight:bold;'>Date de Paiement : " . htmlspecialchars($payment_date) . "</td></tr>";
                
                // Réinitialiser le total quotidien pour la nouvelle date
                $daily_total = 0;
                $current_date = $payment_date; // Mettre à jour la date actuelle
            }
            // Préparer et exécuter la requête pour obtenir le nom de la matière
$sqlmatiere = "SELECT nom_matiere FROM matières WHERE subject_id = ?";
$resultmatiere = $conn->prepare($sqlmatiere);
$resultmatiere->bind_param("s", $row_paiement['subject_id']); // Liaison de l'ID de la matière

// Vérifier si la requête s'exécute correctement
if ($resultmatiere->execute()) {
    $result = $resultmatiere->get_result(); // Obtenir le résultat
    $rowmatiere = $result->fetch_assoc(); // Récupérer le nom de la matière

    // Afficher les détails du paiement
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row_paiement["etudiant_id"]) . "</td>";
    echo "<td>" . htmlspecialchars($row_paiement["paiement_id"]) . "</td>";
    echo "<td>" . htmlspecialchars($row_paiement["date_paiement"]) . "</td>";
    echo "<td>" . htmlspecialchars($rowmatiere["nom_matiere"]) . "</td>";
    echo "<td>" . htmlspecialchars($row_paiement["montant"]) . "</td>";
    echo "<td style='text-align: center; display: flex; align-items: center; justify-content: center;'>";
    echo "<a href='modifier_etudiant.php?id={$code}&date_paiement={$row_paiement['date_paiement']}&montant={$row_paiement['montant']}&subject_id={$row_paiement['subject_id']}'><i class='fa-solid fa-pen'></i></a>";
    echo "</td>";
        echo "</tr>";
} else {
    echo "<tr><td colspan='4'>Erreur lors de la récupération de la matière.</td></tr>";
}

// Fermer le statement
$resultmatiere->close();


            // Ajouter le montant au total quotidien
            $daily_total += $row_paiement["montant"];
        }

        // Afficher le total des paiements pour le dernier jour
        echo "<tr><td colspan='3' style='text-align:right; font-weight:bold;'>Total pour le " . htmlspecialchars($current_date) . " :</td>";
        echo "<td style='font-weight:bold;'>" . htmlspecialchars($daily_total) . "</td></tr>";

    } else {
        echo "<tr><td colspan='4'>Aucun paiement trouvé.</td></tr>"; // Message si aucun paiement n'est trouvé
    }

    echo "</table>";

    // Fermer le statement
    $stmt_paiement->close();
    ?>
</div>

<h2>Les Matières</h2>
<table cellpadding="10" id="table_matiere">
    <tr style="background: linear-gradient(to right, #508C9B, #B4D6CD);">
        <th>Code</th>
        <th>Date De Modification</th>
        <th>Matières</th>
        <th>Montant</th>
        <th>Status</th>
    </tr>
    <?php
    while ($row = $resultat_etud->fetch_assoc()) {
        // Préparer et exécuter la requête pour récupérer la matière et le dernier paiement
        $stmt = $conn->prepare("
            SELECT m.nom_matiere, p.montant, p.date_paiement
            FROM matières m
            LEFT JOIN paiements p ON m.subject_id = p.subject_id AND p.etudiant_id = ? 
            WHERE m.subject_id = ? 
            ORDER BY p.date_paiement DESC
            LIMIT 1
        ");
        $stmt->bind_param("is", $row['etudiant_id'], $row['subject_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Vérification si une matière et un paiement existent
        $row_matiere = $result->fetch_assoc();
        
        // Générer le tableau HTML
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["etudiant_id"]) . "</td>";
        echo "<td>" . ($row_matiere ? htmlspecialchars($row_matiere["date_paiement"]) : "Aucune date") . "</td>"; // Dernière date de paiement
        echo "<td>" . htmlspecialchars($row_matiere["nom_matiere"]) . "</td>"; // Nom de la matière
        echo "<td>" . ($row_matiere ? htmlspecialchars($row_matiere["montant"]) . " DH" : "Non payé") . "</td>"; // Dernier montant payé
        echo "<td>" . htmlspecialchars($row["status"]) . "</td>"; // Statut
        echo "</tr>";
        
        // Fermer la requête préparée
        $stmt->close();
    }
    ?>
</table>

    <table  cellpadding="10" id="classe"  style="display:none;">

    <tr>
        <th>Code</th>
        <th>Nom Classe</th>
        <th>Matières</th>
        <th>Prof</th>
    </tr>
    <?php
    // Préparer la requête pour récupérer les classes de l'étudiant
    $classe = "SELECT nom_class, subject_id FROM classes WHERE etudiant_id = ?";
    $result_classe = $conn->prepare($classe);
    $result_classe->bind_param("s", $code);
    $result_classe->execute();
    $resultat_classe = $result_classe->get_result();

    // Parcourir les résultats des classes
    while ($row_classe = $resultat_classe->fetch_assoc()) {
        // Préparer et exécuter la requête pour la matière
        $stmt_matiere = $conn->prepare("SELECT nom_matiere FROM matières WHERE subject_id = ?");
        $stmt_matiere->bind_param("s", $row_classe['subject_id']);
        $stmt_matiere->execute();
        $result_matiere = $stmt_matiere->get_result();
        
        // Récupérer le nom de la matière
        $row_matiere = $result_matiere->fetch_assoc();
        $nom_matiere = $row_matiere ? htmlspecialchars($row_matiere["nom_matiere"]) : "Inconnu"; // Gérer les matières manquantes

        // Générer le tableau HTML
        echo "<tr>";
        echo "<td>" . htmlspecialchars($code) . "</td>"; // Assurez-vous d'ajouter 'etudiant_id' dans la requête 'classes'
        echo "<td>" . htmlspecialchars($row_classe["nom_class"]) . "</td>"; // Nom de la classe
        echo "<td>" . $nom_matiere . "</td>"; // Nom de la matière
          // Requête pour récupérer le nom du professeur
            $sql_prof = "SELECT p.nom, p.prenom FROM professeurs p
            INNER JOIN prof_classe pc ON pc.prof_id = p.prof_id
            INNER JOIN classes c ON c.class_id = pc.classe_id
            WHERE c.nom_class = '" . $row_classe["nom_class"]. "'";
        $result_prof = $conn->query($sql_prof);
        if ($result_prof->num_rows > 0) {
        $professeurs = [];
        while ($prof = $result_prof->fetch_assoc()) {
        $professeurs[] = $prof['nom'] . ' ' . $prof['prenom'];
        }
        echo "<td>" . implode("   /   ", $professeurs) . "</td>";
        } else {
        echo "<td>Aucun professeur</td>";
        }

        echo "</tr>";
        
        // Fermer la requête pour la matière
        $stmt_matiere->close();
    }

    // Fermer la requête pour les classes
    $result_classe->close();
    ?>
</table>



<table  cellpadding="10" id="parent"  style="display:none;">
    <tr>
        <th>Code</th>
        <th>Nom Pere</th>
        <th>Tel Pere</th>
        <th>Nom Mere</th>
        <th>Tel Mere</th>
    </tr>
    <?php
    // Préparer la requête pour récupérer les classes de l'étudiant
    $parent = "SELECT * FROM etudiants WHERE etudiant_id = ?";
    $result_parent = $conn->prepare($parent);
    $result_parent->bind_param("s", $code);
    $result_parent->execute();
    $resultat_parent = $result_parent->get_result();

    // Parcourir les résultats des parents
    while ($row_parent = $resultat_parent->fetch_assoc()) {

        // Générer le tableau HTML
        echo "<tr>";
        echo "<td>" . htmlspecialchars($code) . "</td>"; // Assurez-vous d'ajouter 'etudiant_id' dans la requête 'classes'
        echo "<td>" . htmlspecialchars($row_parent["nom_pere"]) . "</td>"; // Nom de la classe
        echo "<td>" .  htmlspecialchars($row_parent["tel_pere"])  . "</td>"; // Nom de la matière
        echo "<td>". htmlspecialchars($row_parent["nom_mere"]) ."</td>"; 
        echo "<td>". htmlspecialchars($row_parent["tel_mere"]) ."</td>"; // Remplacer par la vraie valeur si disponible
        echo "</tr>";
    }

    // Fermer la requête pour les parents
    $result_parent->close();
    ?>
</table>

    <script>
         function form_insertion() {
            // Sélectionner l'élément avec l'ID 'about'
            var aboutElement = document.getElementById('form1');
            
            // Alterner l'affichage entre 'block' et 'none'
            if (aboutElement.style.display === 'none' || aboutElement.style.display === '') {
                aboutElement.style.display = 'block';
            } else {
                aboutElement.style.display = 'none';
            }
    
}
function form_supprimer() {
            // Sélectionner l'élément avec l'ID 'about'
            var aboutElement = document.getElementById('form2');
            
            // Alterner l'affichage entre 'block' et 'none'
            if (aboutElement.style.display === 'none' || aboutElement.style.display === '') {
                aboutElement.style.display = 'block';
            } else {
                aboutElement.style.display = 'none';
            }
    
}
function form_paiement() {
            // Sélectionner l'élément avec l'ID 'about'
            var aboutElement = document.getElementById('form3');
            var tablePaiement = document.getElementById('tablePaiement');
            
            // Alterner l'affichage entre 'block' et 'none'
            if (aboutElement.style.display === 'none' || aboutElement.style.display === '') {
                aboutElement.style.display = 'block';
                tablePaiement.style.display = 'block'; // Afficher le tableau des paiements
            } else {
                aboutElement.style.display = 'none';
                tablePaiement.style.display = 'none'; // Cacher le tableau des paiements
            }
    
}
// Fonction pour afficher/masquer le tableau des informations des parents
function parent() {
        var parentElement = document.getElementById('parent');
        if (parentElement.style.display === 'none' || parentElement.style.display === '') {
            parentElement.style.display = 'table';
        } else {
            parentElement.style.display = 'none';
        }
    }

    function classe() {
        var classeElement = document.getElementById('classe');
        if (classeElement.style.display === 'none' || classeElement.style.display === '') {
            classeElement.style.display = 'table';
        } else {
            classeElement.style.display = 'none';
        }
    }


    </script>

    </div>

   
</body>
</html>






