<?php
require_once "connexion.php";

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération des niveaux
$sql_niv = "SELECT id_niveau, nom_niveau FROM niveau";
$result_niv = $conn->query($sql_niv);

// Récupération des matières
$sql_m = "SELECT subject_id, nom_matiere FROM matières";
$result_m = $conn->query($sql_m);

// Récupération des 8 dernières dates d'absences
$sql_dates = "SELECT DISTINCT absence_date FROM absences ORDER BY absence_date DESC LIMIT 8";
$result_dates = $conn->query($sql_dates);
$dates = [];
while ($row = $result_dates->fetch_assoc()) {
    $dates[] = $row['absence_date'];
}

// Traitement du formulaire POST pour mise à jour ou insertion des absences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absences'])) {
    $niveau_id = (int)$_POST['niveau'];
    $matiere_id = (int)$_POST['matiere'];
    $absences = $_POST['absences'];

    foreach ($absences as $etudiant_id => $status) {
        if (in_array($status, ['absent', 'present'])) {
            $current_date = date('Y-m-d');
            
            // Vérifier si une absence existe déjà
            $check_sql = "SELECT COUNT(*) as count FROM absences 
                          WHERE etudiant_id = ? AND subject_id = ? AND niveau_id = ? AND absence_date = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iiis", $etudiant_id, $matiere_id, $niveau_id, $current_date);
            $check_stmt->execute();
            $result_check = $check_stmt->get_result();
            $row_check = $result_check->fetch_assoc();

            if ($row_check['count'] > 0) {
                // Mettre à jour l'absence existante
                $update_sql = "UPDATE absences 
                               SET status = ? 
                               WHERE etudiant_id = ? AND subject_id = ? AND niveau_id = ? AND absence_date = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("siiis", $status, $etudiant_id, $matiere_id, $niveau_id, $current_date);
                $update_stmt->execute();
            } else {
                // Insérer une nouvelle absence
                $insert_sql = "INSERT INTO absences (etudiant_id, subject_id, niveau_id, absence_date, status) 
                               VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iiiss", $etudiant_id, $matiere_id, $niveau_id, $current_date, $status);
                $insert_stmt->execute();
            }
        }
    }
    echo "<p>Mise à jour ou insertion réussie des absences.</p>";
}

// Récupération des étudiants en fonction des filtres
$niveau_id = isset($_GET['niveau']) ? (int)$_GET['niveau'] : null;
$matiere_id = isset($_GET['matiere']) ? (int)$_GET['matiere'] : null;

$etudiants = [];
if ($niveau_id && $matiere_id) {
    $sql = "SELECT e.etudiant_id, e.nom, e.telephone
            FROM etudiants e
            JOIN classes c ON e.etudiant_id = c.etudiant_id
            WHERE c.niveau = ? AND c.subject_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $niveau_id, $matiere_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $etudiants[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absences des Étudiants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">
    <script>
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
    <h1>Absences des Étudiants</h1>
    <form method="GET">
        <div class="form-row">
        <div class="form-group">
        <label for="niveau">Niveau :</label>
        <select id="niveau" name="niveau" required>
            <option value="">Sélectionner un niveau</option>
            <?php while ($row_niv = $result_niv->fetch_assoc()): ?>
                <option value="<?= $row_niv['id_niveau'] ?>" <?= $niveau_id == $row_niv['id_niveau'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row_niv['nom_niveau']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        </div>
        <div class="form-group">

        <label for="matiere">Matière :</label>
        <select id="matiere" name="matiere" required>
            <option value="">Sélectionner une matière</option>
            <?php while ($row_m = $result_m->fetch_assoc()): ?>
                <option value="<?= $row_m['subject_id'] ?>" <?= $matiere_id == $row_m['subject_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row_m['nom_matiere']) ?>
                </option>
            <?php endwhile; ?>
        </select>
            </div>
        </div>

        <button type="submit">Filtrer</button>
    </form>

    <?php if (!empty($etudiants)): ?>
        <form method="POST">
            <input type="hidden" name="niveau" value="<?= htmlspecialchars($niveau_id) ?>">
            <input type="hidden" name="matiere" value="<?= htmlspecialchars($matiere_id) ?>">

            <table>
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <?php foreach ($dates as $date): ?>
                        <th><?= date('d-m', strtotime($date)) ?></th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= htmlspecialchars($etudiant['etudiant_id']) ?></td>
                        <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                        <td><?= htmlspecialchars($etudiant['telephone']) ?></td>
                        <?php foreach ($dates as $date): ?>
                            <?php
                            $sql_absence = "SELECT status FROM absences 
                                            WHERE etudiant_id = ? AND subject_id = ? AND niveau_id = ? AND absence_date = ?";
                            $stmt = $conn->prepare($sql_absence);
                            $stmt->bind_param("iiis", $etudiant['etudiant_id'], $matiere_id, $niveau_id, $date);
                            $stmt->execute();
                            $result_absence = $stmt->get_result();
                            $absence = $result_absence->fetch_assoc();
                            ?>
                            <td><?= htmlspecialchars($absence['status'] ?? '-') ?></td>
                        <?php endforeach; ?>
                        <td>
                            <select name="absences[<?= $etudiant['etudiant_id'] ?>]">
                                <option value="">--</option>
                                <option value="present">Présent</option>
                                <option value="absent">Absent</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit">Enregistrer les absences</button>
        </form>
    <?php else: ?>
        <p>Aucun étudiant trouvé pour les critères sélectionnés.</p>
    <?php endif; ?>
</div>

</body>
</html>
