<?php
require_once "connexion.php";
// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Fonction pour échapper les entrées
function escape($value, $conn) {
    return $conn->real_escape_string($value);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire et échapper les entrées
    $nom = escape($_POST['nom'], $conn);
    $prenom = escape($_POST['prenom'], $conn);
    $email = escape($_POST['email'], $conn);
    $telephone = escape($_POST['telephone'], $conn);
    $nom_pere = escape($_POST['nom_pere'], $conn);
    $nom_mere = escape($_POST['nom_mere'], $conn);
    $tel_pere = escape($_POST['tel_pere'], $conn);
    $tel_mere = escape($_POST['tel_mere'], $conn);
    $niveau = escape($_POST['niveau'], $conn);
    $subject = escape($_POST['matiere1'], $conn);
    $prix = escape($_POST['prix_matiere1'], $conn);
    $currentTimestamp = date('Y-m-d H:i:s'); // format standard pour MySQL
    $status = "ajoute";

    // Vérification si le couple (nom, prénom) existe déjà
    $sql_check_nom_prenom = "SELECT * FROM etudiants WHERE nom = ? AND prenom = ?";
    $stmt_check = $conn->prepare($sql_check_nom_prenom);
    $stmt_check->bind_param("ss", $nom, $prenom);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Cet étudiant (nom et prénom) est déjà enregistré. Veuillez vérifier les informations.');</script>";
    } else {
        // Insertion des données dans la table 'etudiants'
        $sql = "INSERT INTO etudiants (date_inscription, nom, prenom, email, telephone, nom_pere, nom_mere, tel_pere, tel_mere, niveau) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $currentTimestamp, $nom, $prenom, $email, $telephone, $nom_pere, $nom_mere, $tel_pere, $tel_mere, $niveau);
        
        if ($stmt->execute()) {
            $code = $conn->insert_id;

            // Insertion dans la table 'etudiant_matiere'
            $sql_insert = "INSERT INTO etudiant_matiere (etudiant_id, subject_id, date_modification, status, prix, id_niveau) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssss", $code, $subject, $currentTimestamp, $status, $prix, $niveau);

            // Insertion dans la table 'paiements'
            $sql_paiement = "INSERT INTO paiements (etudiant_id, subject_id, montant, date_paiement) 
                             VALUES (?, ?, ?, ?)";
            $stmt_paiement = $conn->prepare($sql_paiement);
            $stmt_paiement->bind_param("ssss", $code, $subject, $prix, $currentTimestamp);
            
            // Récupération du nom de la matière
            $sql_nom_sub = "SELECT nom_matiere FROM matières WHERE subject_id = ?";
            $stmt_nom_sub = $conn->prepare($sql_nom_sub);
            $stmt_nom_sub->bind_param("s", $subject);
            $stmt_nom_sub->execute();
            $result_nom_sub = $stmt_nom_sub->get_result();
            $row_nom_sub = $result_nom_sub->fetch_assoc();

            // Vérifier si le nom de la matière a été récupéré
            $nom_matiere = $row_nom_sub ? $row_nom_sub["nom_matiere"] : "Inconnu"; 

            // Récupération du nom du niveau
            $sql_nom_niv = "SELECT nom_niveau FROM niveau WHERE id_niveau = ?";
            $stmt_nom_niv = $conn->prepare($sql_nom_niv);
            $stmt_nom_niv->bind_param("s", $niveau);
            $stmt_nom_niv->execute();
            $result_nom_niv = $stmt_nom_niv->get_result();
            $row_nom_niv = $result_nom_niv->fetch_assoc();

            // Vérifier si le nom du niveau a été récupéré
            $nom_niveau = $row_nom_niv ? $row_nom_niv["nom_niveau"] : "Inconnu"; 

            // Insertion dans la table 'classes'
            $nom_class = $nom_matiere . "/" . $nom_niveau; // Définir nom_class
            $sql_classe = "INSERT INTO classes (etudiant_id, subject_id, niveau, nom_class) 
                           VALUES (?, ?, ?, ?)";
            $stmt_classe = $conn->prepare($sql_classe);
            $stmt_classe->bind_param("ssss", $code, $subject, $niveau, $nom_class);
            
            // Exécution des requêtes
            if ($stmt_insert->execute() && $stmt_paiement->execute() && $stmt_classe->execute()) {
                echo "<script>alert('L\'étudiant a été ajouté avec succès!');</script>";
            } else {
                echo "<script>alert('Erreur lors de l\'ajout des données.');</script>";
            }
        } else {
            echo "<script>alert('Erreur lors de l\'ajout de l\'étudiant.');</script>";
        }
    }
}

// Requête pour récupérer les niveaux
$sql_niv = "SELECT id_niveau, nom_niveau FROM niveau";
$result_niv = $conn->query($sql_niv);

// Requête pour récupérer les matières
$sql_m = "SELECT * FROM matières";
$result_m = $conn->query($sql_m);

// Requête pour récupérer les étudiants avec filtrage
$filter_query = "SELECT * FROM etudiants WHERE 1";

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['filter'])) {
    $nom_filter = escape($_GET['nom'] ?? '', $conn);
    $prenom_filter = escape($_GET['prenom'] ?? '', $conn);
    $niveau_filter = escape($_GET['niveau'] ?? '', $conn);
    $code_filter = escape($_GET['code'] ?? '', $conn);

    if ($nom_filter) $filter_query .= " AND nom LIKE '%$nom_filter%'";
    if ($prenom_filter) $filter_query .= " AND prenom LIKE '%$prenom_filter%'";
    if ($niveau_filter) $filter_query .= " AND niveau = '$niveau_filter'";
    if ($code_filter) $filter_query .= " AND etudiant_id = '$code_filter'";
}

$result_etud = $conn->query($filter_query);

$conn->close(); // Fermer la connexion
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
        <h1>Liste Des Etudiants</h1>
        <form action="" method="POST" id="form_insertion" style="display: none;">
    <div class="form-row">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="niveau">Niveau:</label>
            <select id="niveau" name="niveau" required>
                <option value="">Sélectionner un Niveau</option>
                <?php
                if ($result_niv->num_rows > 0) {
                    while ($row_niv = $result_niv->fetch_assoc()) {
                        echo "<option value='" . $row_niv["id_niveau"] . "'" . ($row_niv["id_niveau"] == $niveau ? ' selected' : '') . ">" . htmlspecialchars($row_niv["nom_niveau"]) . "</option>";
                    }
                } else {
                    echo "<option value=''>Aucun niveau disponible</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone:</label>
            <input type="text" id="telephone" name="telephone" value="<?php echo htmlspecialchars($telephone ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="nom_pere">Nom du Père:</label>
            <input type="text" id="nom_pere" name="nom_pere" value="<?php echo htmlspecialchars($nom_pere ?? ''); ?>">
        </div>
    </div>


    <div class="form-row">
        
        <div class="form-group">
            <label for="nom_mere">Nom de la Mère:</label>
            <input type="text" id="nom_mere" name="nom_mere" value="<?php echo htmlspecialchars($nom_mere ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="tel_pere">Téléphone du Père:</label>
            <input type="text" id="tel_pere" name="tel_pere" value="<?php echo htmlspecialchars($tel_pere ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="tel_mere">Téléphone de la Mère:</label>
            <input type="text" id="tel_mere" name="tel_mere" value="<?php echo htmlspecialchars($tel_mere ?? ''); ?>">
        </div>
    </div>

    

    <div class="form-row">
        <div class="form-group">
            <label for="matiere1">Matière 1:</label>
            <select id="matiere1" name="matiere1" required>
                <option value="">Sélectionner une Matière</option>
                <?php
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
            <label for="prix_matiere1">Prix Matière 1:</label>
            <input type="number" id="prix_matiere1" name="prix_matiere1" min="0" required>
        </div>
    </div>

    <input type="submit" value="Ajouter Étudiant">
</form>


    <button onclick="form_insertion()" class="button_action" >Ajouter / Masquer</button>
    <form action="etudiant.php" method="get"    >
         <div class="form-row">
         <div class="form-group">
         <input type="text" name="nom" placeholder="Nom">
         </div>
         <div class="form-group">
         <input type="text" name="prenom" placeholder="Prénom">
         </div>
         <div class="form-group">
         <input type="text" name="code" placeholder="Code">
         </div>


                <button type="submit" name="filter">Filtrer</button>
                <button type="submit" name="quiter" formaction="etudiant.php" formmethod="get">Quitter le filtrage</button>

            </div>

            </form>

        
            <?php
// Paramètres de connexion
$host = 'localhost';    // Hôte de la base de données
$dbname = 'alphabridge'; // Nom de la base de données
$username = 'root';  // Nom d'utilisateur MySQL
$password = ''; // Mot de passe MySQL

// Créer la connexion
$conn = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Initialiser les conditions de filtrage
$filter_conditions = [];

// Vérifier si les filtres sont définis et ajouter aux conditions
if (isset($_GET['nom']) && !empty($_GET['nom'])) {
    $nom = $_GET['nom'];
    $filter_conditions[] = "nom LIKE '%$nom%'";
}

if (isset($_GET['prenom']) && !empty($_GET['prenom'])) {
    $prenom = $_GET['prenom'];
    $filter_conditions[] = "prenom LIKE '%$prenom%'";
}

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $code = $_GET['code'];
    $filter_conditions[] = "etudiant_id LIKE '%$code%'";  // Filtre par le code (ID de l'étudiant)
}

if (isset($_GET['niveau']) && !empty($_GET['niveau'])) {
    $niveau = $_GET['niveau'];
    $filter_conditions[] = "niveau = '$niveau'";  // Filtre par niveau
}

// Combiner les conditions de filtrage si elles existent
$filter_query = "";
if (!empty($filter_conditions)) {
    $filter_query = "WHERE " . implode(" AND ", $filter_conditions);
}

// Requête pour récupérer les étudiants avec le filtrage
$sql_etud = "SELECT * FROM etudiants $filter_query ORDER BY etudiant_id DESC";
$result_etud = $conn->query($sql_etud);

if ($result_etud->num_rows > 0) {
    echo '<table class="table table-bordered">';
    echo '<tr>
            <th>Code </th>
            <th>Date d\'Inscription</th>
            <th>Nom et Prénom</th>
            <th>Niveau</th>
            <th>Matieres</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Action</th>
          </tr>';

    while ($row = $result_etud->fetch_assoc()) {
        // Requête pour récupérer le nom du niveau
        $niv = "SELECT nom_niveau FROM niveau WHERE id_niveau =" . $row["niveau"];
        $result_niv = $conn->query($niv);

        // Requête pour récupérer les matières associées à l'étudiant
        $sql_etud_m = "SELECT nom_matiere 
                       FROM matières 
                       INNER JOIN etudiant_matiere ON matières.subject_id = etudiant_matiere.subject_id 
                       WHERE etudiant_matiere.etudiant_id = " . $row["etudiant_id"] . " 
                       AND etudiant_matiere.status = 'ajoute'";
        $result_etud_m = $conn->query($sql_etud_m);

        $matieres = [];
        if ($result_etud_m && $result_etud_m->num_rows > 0) {
            while ($row_m = $result_etud_m->fetch_assoc()) {
                $matieres[] = $row_m['nom_matiere'];
            }
        }

        $nom_niveau = "Inconnu";
        if ($result_niv && $result_niv->num_rows > 0) {
            $row_niv = $result_niv->fetch_assoc();
            $nom_niveau = $row_niv["nom_niveau"];
        }

        echo "<tr>";
        echo "<td>" . $row["etudiant_id"] . "</td>";
        echo "<td>" . $row["date_inscription"] . "</td>";
        echo "<td>" . $row["nom"] . " " . $row["prenom"] . "</td>";
        echo "<td>" . $nom_niveau . "</td>";
        echo "<td>" . implode(", ", $matieres) . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["telephone"] . "</td>";
        echo "<td>
                <!-- Lien de suppression avec l'ID de l'étudiant -->
                <a href='supprimer_etud.php?id=" . $row["etudiant_id"]  . "' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce Etudiant ?\");'>
                    <i class='fa-solid fa-trash' style='color:red;'></i>
                </a>
                <a href='modifier_etudiant.php?id=" . $row["etudiant_id"] . "'>
                    <i class='fa-solid fa-eye' style='color:green;'></i>
                </a>
                <a href='update_etudiant.php?id=" . $row["etudiant_id"] . "'>
                    <i class='fa-solid fa-pen' style='color:black;'></i>
                </a>
              </td>";
        echo "</tr>";
    }

    echo '</table>';
} else {
    echo "<p>Aucun étudiant trouvé.</p>";
}

$conn->close();
?>



    </div>

   
</body>
</html>

