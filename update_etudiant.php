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

// Vérifier si un étudiant est passé en GET
if (isset($_GET['id'])) {
    $etudiant_id = $_GET['id'];

    // Récupérer les informations de l'étudiant depuis la base de données
    $sql = "SELECT * FROM etudiants WHERE etudiant_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // L'étudiant existe, récupérer ses informations
        $row = $result->fetch_assoc();
        $nom = $row['nom'];
        $prenom = $row['prenom'];
        $niveau = $row['niveau'];
        $email = $row['email'];
        $telephone = $row['telephone'];
        $nom_pere = $row['nom_pere'];
        $tel_pere = $row['tel_pere'];
        $nom_mere = $row['nom_mere'];
        $tel_mere = $row['tel_mere'];
    }
}

// Requête pour récupérer les niveaux
$sql_niv = "SELECT id_niveau, nom_niveau FROM niveau";
$result_niv = $conn->query($sql_niv);

// Modifier les informations de l'étudiant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    $nom = escape($_POST['nom'], $conn);
    $prenom = escape($_POST['prenom'], $conn);
    $niveau = escape($_POST['niveau'], $conn);
    $email = escape($_POST['email'], $conn);
    $telephone = escape($_POST['telephone'], $conn);
    $nom_pere = escape($_POST['nom_pere'], $conn);
    $tel_pere = escape($_POST['tel_pere'], $conn);
    $nom_mere = escape($_POST['nom_mere'], $conn);
    $tel_mere = escape($_POST['tel_mere'], $conn);

    // Requête pour mettre à jour les informations de l'étudiant
    $sql_update = "UPDATE etudiants SET nom = ?, prenom = ?, email = ?, telephone = ?, nom_pere = ?, nom_mere = ?, tel_pere = ?, tel_mere = ?, niveau = ? WHERE etudiant_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssssssi", $nom, $prenom, $email, $telephone, $nom_pere, $nom_mere, $tel_pere, $tel_mere, $niveau, $etudiant_id);

    if ($stmt_update->execute()) {
        header("Location:etudiant.php");
    } else {
        echo "Erreur lors de la mise à jour de l'étudiant.";
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
    <script>
        // Gestion de l'affichage du menu
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
        <h1>Modifier ETUDIANTS</h1>
        <form action="" method="POST">
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

            <button type="submit" name="modifier">Modifier Étudiant</button>
        </form>
    </div>
</body>
</html>
