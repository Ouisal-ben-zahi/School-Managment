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

    $checkEmailQuery = "SELECT * FROM professeurs WHERE email = ?";
    $stmtCheck = $conn->prepare($checkEmailQuery);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "<script>alert('Cet email est déjà utilisé.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO professeurs (nom, prenom, email, telephone, date_embauche, cin, matiere) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nom, $prenom, $email, $tel, $date_embauche, $cin, $matiere);

        if ($stmt->execute()) {
            echo "<script>alert('Ajouté avec succès');</script>";
        } else {
            echo "Erreur lors de l'ajout : " . $stmt->error;
        }

        $stmt->close();
    }

    $stmtCheck->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
            align-items: flex-start;
        }

        .card {
            background: linear-gradient(145deg, #e6e6e6, #ffffff);
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            width: 320px;
            padding: 25px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.25);
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,215,0,0.2), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card h2 {
            margin: 0 0 15px;
            font-size: 1.6rem;
            color: #2a2a2a;
            font-weight: bold;
            text-align: center;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .card h2:hover {
            color: #007BFF;
            transform: scale(1.1) rotate(-1deg);
        }

        .card p {
            margin: 10px 0;
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
            text-align: justify;
        }

        .card a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #ffffff;
            font-weight: bold;
            background-color: #007BFF;
            padding: 12px 25px;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-align: center;
        }

        .card a:hover {
            background-color: #0056b3;
            color: #FFD700;
            transform: scale(1.1) rotate(-2deg);
        }

        .card a i {
            margin-right: 8px;
        }
    </style>
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
                <li><a href="#salaire-professeurs">Salaire Des Professeurs</a></li>
                <li><a href="depence.php">Dépenses</a></li>
            </ul>
        </nav>
    </div>

    <div class="content">
        <span class="menu-toggle" onclick="toggleMenu()">&#9776; Menu</span>
        <h1>Liste Des Classes</h1>
        <div class="card-container">
            <?php
            $sql = "SELECT nom_class, niveau, subject_id, COUNT(etudiant_id) AS nombre_etudiants 
                    FROM classes 
                    WHERE etudiant_id IS NOT NULL 
                    GROUP BY nom_class, niveau, subject_id;";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<div class='card'>";
                echo "<h2>Classe : " . htmlspecialchars($row['nom_class']) . "</h2>";

                $sql_prof = "SELECT p.nom, p.prenom FROM professeurs p
                             INNER JOIN prof_classe pc ON pc.prof_id = p.prof_id
                             INNER JOIN classes c ON c.class_id = pc.classe_id
                             WHERE c.nom_class = '" . $row['nom_class'] . "'";
                $result_prof = $conn->query($sql_prof);

                if ($result_prof->num_rows > 0) {
                    $professeurs = [];
                    while ($prof = $result_prof->fetch_assoc()) {
                        $professeurs[] = $prof['nom'] . ' ' . $prof['prenom'];
                    }
                    echo "<p><strong>Professeur :</strong> " . implode(", ", $professeurs) . "</p>";
                } else {
                    echo "<p><strong>Professeur :</strong> Aucun professeur</p>";
                }

                echo "<p><strong>Nombre d'étudiants :</strong> " . htmlspecialchars($row['nombre_etudiants']) . "</p>";

                $rq_matiere = "SELECT nom_matiere FROM matières WHERE subject_id = " . intval($row['subject_id']);
                $result_matiere = $conn->query($rq_matiere);

                if ($result_matiere && $matiere = $result_matiere->fetch_assoc()) {
                    echo "<p><strong>Matière :</strong> " . htmlspecialchars($matiere['nom_matiere']) . "</p>";
                } else {
                    echo "<p><strong>Matière :</strong> Aucune matière trouvée</p>";
                }

                echo "<a href='classename.php?id=" . urlencode($row["nom_class"]) . "&matiere=" . $row["subject_id"] . "'>
                        <i class='fas fa-eye'></i> Voir Détails
                      </a>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

</body>
</html>
