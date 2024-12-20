<?php
require_once "connexion.php";

// Fonction pour échapper les entrées
function escape($value, $conn) {
    return $conn->real_escape_string($value);
}

if (isset($_POST['Ajouter'])) {
    $nom = escape($_POST["nom"], $conn);
    $prenom = escape($_POST["prenom"], $conn);
    $tel = escape($_POST["tel"], $conn);
    $email = escape($_POST["email"], $conn);
    $cin = escape($_POST["cin"], $conn);
    $matiere = escape($_POST["matiere"], $conn);
    $date_embauche = date("Y-m-d");

    // Vérification si l'email existe déjà
    $checkEmailQuery = "SELECT * FROM professeurs WHERE email = ?";
    $stmtCheck = $conn->prepare($checkEmailQuery);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // Email déjà utilisé
        echo "<script>alert('Cet email est déjà utilisé.');</script>";
    } else {
        // Préparation de la requête pour éviter l'injection SQL
        $stmt = $conn->prepare("INSERT INTO professeurs (nom, prenom, email, telephone, date_embauche, cin, matiere) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nom, $prenom, $email, $tel, $date_embauche, $cin, $matiere);

        // Exécution et vérification de la requête
        if ($stmt->execute()) {
            echo "<script>alert('Ajouté avec succès'); window.location.href='professeurs.php';</script>"; // Redirection après ajout
        } else {
            echo "Erreur lors de l'ajout : " . $stmt->error;
        }

        // Fermeture de la déclaration
        $stmt->close();
    }

    // Fermeture de la déclaration de vérification d'email
    $stmtCheck->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professeurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">
    <script>
        function toggleMenu() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden'); // Ajout/suppression de la classe "hidden"
        }

        function form_insertion() {
            var aboutElement = document.getElementById('form_insertion');
            aboutElement.style.display = (aboutElement.style.display === 'none' || aboutElement.style.display === '') ? 'block' : 'none';
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
                <li><a href="absente.php">Absences</a></li>
                <li><a href="paiement-etudiants.php">Paiement Des Étudiants</a></li>
                <li><a href="paiement_prof.php">Salaire Des Professeurs</a></li>
                <li><a href="depence.php">Dépenses</a></li>
            </ul>
        </nav>
    </div>

    <div class="content">
        <span class="menu-toggle" onclick="toggleMenu()">☰ Menu</span> <!-- Bouton pour ouvrir/fermer le menu -->
        <h1>Liste Des Professeurs</h1>
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
                    <label for="matiere">Matière:</label>
                    <select id="matiere" name="matiere" required>
                        <option value="">Sélectionner une Matière</option>
                        <?php
                        $sql_niv = "SELECT * FROM matières";
                        $result_niv = $conn->query($sql_niv);

                        if ($result_niv->num_rows > 0) {
                            while ($row_niv = $result_niv->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($row_niv["subject_id"]) . "'>" . htmlspecialchars($row_niv["nom_matiere"]) . "</option>";
                            }
                        } else {
                            echo "<option value=''>Aucune matière disponible</option>";
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
                    <label for="tel">Téléphone:</label>
                    <input type="text" id="tel" name="tel" value="<?php echo htmlspecialchars($tel ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="cin">CIN:</label>
                    <input type="text" id="cin" name="cin" value="<?php echo htmlspecialchars($cin ?? ''); ?>" required>
                </div>
            </div>

            <input type="submit" name="Ajouter" value="Ajouter">
        </form>

        <button onclick="form_insertion()" class="button_action">Ajouter</button>

        <table >
            <tr>
                <th>CIN</th>
                <th>Date D'embauche</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Matière</th>
                <th>Action</th>
            </tr>
            <?php
            $req = "SELECT * FROM professeurs ORDER BY prof_id DESC";
            $result = $conn->query($req);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["cin"] . "</td>";
                echo "<td>" . $row["date_embauche"] . "</td>";
                echo "<td>" . $row["nom"] . "</td>";
                echo "<td>" . $row["prenom"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "<td>" . $row["telephone"] . "</td>";
                // Requête pour obtenir le nom de la matière
                $matiere_id = $row["matiere"];
                $reqMatiere = "SELECT nom_matiere FROM matières WHERE subject_id = ?";
                $stmt = $conn->prepare($reqMatiere);
                $stmt->bind_param("i", $matiere_id);
                $stmt->execute();
                $resultMatiere = $stmt->get_result();

                if ($resultMatiere->num_rows > 0) {
                    $matiere = $resultMatiere->fetch_assoc();
                    echo "<td>" . htmlspecialchars($matiere["nom_matiere"]) . "</td>";
                } else {
                    echo "<td>Aucune matière</td>";
                }
                echo "<td>
                    <a href='supprimer_prof.php?id=" . $row["prof_id"] . "' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce professeur ?\");'>
                        <i class='fa-solid fa-trash' style='color:red;'></i>
                    </a>
                    <a href='classe_prof.php?id_prof=" . $row["prof_id"] . "'>
                        <i class='fa-solid fa-pen' style='color:green;'></i>
                    </a>
                </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
