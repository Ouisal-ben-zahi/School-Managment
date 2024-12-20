<?php
require_once "connexion.php";

// Fonction pour échapper les entrées
function escape($value, $conn) {
    return $conn->real_escape_string($value);
}

$row = "";
$montant = "";
$obs = "";

// Modifier une dépense
if (isset($_GET["mod"])) {
    $req = "SELECT * FROM depence WHERE depence_id=" . intval($_GET["mod"]);
    $result = $conn->query($req);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reference = $row["reference"];
        $montant = $row["montant"];
        $obs = $row["observation"];
    } else {
        echo "Aucune dépense trouvée.";
    }
}

// Ajouter ou mettre à jour une dépense
if (isset($_POST['Ajouter'])) {
    $reference = escape($_POST["ref"], $conn);
    $montant = escape($_POST["montant"], $conn);
    $obs = escape($_POST["obs"], $conn);

    if (isset($_GET["mod"])) {
        $id = escape($_GET["mod"], $conn);
        $sql = "UPDATE depence SET reference='$reference', montant='$montant', observation='$obs' WHERE depence_id=$id";
    } else {
        $sql = "INSERT INTO depence (`reference`, `montant`, `observation`, `date_time`) VALUES ('$reference', '$montant', '$obs', CURRENT_TIMESTAMP())";
    }

    if ($conn->query($sql)) {
        header("Location: depence.php");
    }
}

// Supprimer une dépense
if (isset($_GET["sup"])) {
    $code = intval($_GET["sup"]);
    $sql = "DELETE FROM depence WHERE depence_id=$code";
    $conn->query($sql);
}

// Supprimer plusieurs dépenses
if (isset($_POST['delete_selected'])) {
    $idsToDelete = $_POST['selected_ids'];
    if (!empty($idsToDelete)) {
        $ids = implode(',', array_map('intval', $idsToDelete));
        $sql = "DELETE FROM depence WHERE depence_id IN ($ids)";
        $conn->query($sql);
    }
}

// Gestion du filtre de date
$dateFilter = isset($_POST['search_date']) && $_POST['search_date'] !== '' ? escape($_POST['search_date'], $conn) : date('Y-m');
$sql = "SELECT * FROM depence WHERE DATE_FORMAT(date_time, '%Y-%m') = '$dateFilter' ORDER BY depence_id DESC";
$result_depence = $conn->query($sql);

// Calcul du total des dépenses pour le mois
$total_depenses = 0;
if ($result_depence) {
    $result_depence->data_seek(0);
    while ($row = $result_depence->fetch_assoc()) {
        $total_depenses += $row['montant'];
    }
    $result_depence->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépenses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/etudiants.css">
    <script>
        function toggleMenu() {
            var sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('hidden');
        }

        // Coche ou décoche toutes les cases
        function toggleAllCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        // Vérifie si toutes les cases sont cochées
        function checkIfAllChecked() {
            const allCheckboxes = document.querySelectorAll('.row-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');
            selectAllCheckbox.checked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
        }

        // Fonction pour imprimer le tableau des dépenses de manière professionnelle
    // Fonction pour imprimer le tableau des dépenses de manière professionnelle
    function printTable() {
        var printWindow = window.open('', '', 'height=500,width=800');
        printWindow.document.write('<html><head><title>Dépenses</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; margin: 20px; }');
        printWindow.document.write('h1 { text-align: center; }');
        printWindow.document.write('h3 { text-align: center; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }');
        printWindow.document.write('th { background-color: #f2f2f2; font-weight: bold; }');
        printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        
        // Affichage du titre et informations du mois
        printWindow.document.write('<h1>Centre Alpha Bridge</h1>');
        printWindow.document.write('<h3>Date des Dépenses: ' + "<?php echo date('F Y', strtotime($dateFilter . '-01')); ?>" + '</h3>');

        // Table des dépenses
        printWindow.document.write('<table>');
        printWindow.document.write('<thead><tr><th>Référence</th><th>Montant (MAD)</th><th>Observation</th></tr></thead><tbody>');

        // Ajout des lignes de tableau
        var rows = document.querySelectorAll('table tbody tr');
        rows.forEach(function(row) {
            printWindow.document.write('<tr>');
            row.querySelectorAll('td').forEach(function(td, index) {
                if (index === 1 || index === 3 || index === 4) { // Show only Reference, Amount, and Observation
                    printWindow.document.write('<td>' + td.innerHTML + '</td>');
                }
            });
            printWindow.document.write('</tr>');
        });

        printWindow.document.write('</tbody></table>');

        // Total des dépenses
        printWindow.document.write('<h4 style="margin-top: 20px;">Total des Dépenses: <?php echo number_format($total_depenses, 2, ",", " "); ?> MAD</h4>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
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
        <span class="menu-toggle" onclick="toggleMenu()">☰ Menu</span>
        <h1>Dépenses du Centre</h1>

        <!-- Formulaire de recherche -->
        <form method="POST" action="">
        <div class="form-row">
        <div class="form-group">
            <label for="search_date">Filtrer par mois :</label>
            <input type="month" id="search_date" name="search_date" value="<?php echo isset($_POST['search_date']) ? $_POST['search_date'] : date('Y-m'); ?>">
            <br><br>
            <button type="submit">Rechercher</button>
        </div></div>
        </form>

        <!-- Formulaire d'ajout/modification -->
        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="ref">Référence :</label>
                    <input type="text" id="ref" name="ref" value="<?php echo @$reference; ?>" required>
                </div>
                <div class="form-group">
                    <label for="montant">Montant :</label>
                    <input type="number" min="0" id="montant" name="montant" value="<?php echo @$montant; ?>" required>
                </div>
                <div class="form-group">
                    <label for="obs">Observation :</label>
                    <input type="text" id="obs" name="obs" value="<?php echo @$obs; ?>" required>
                </div>
            </div>
            <button type="submit" name="Ajouter">Ajouter / Modifier</button>
        </form>


        <!-- Tableau des dépenses -->
        <form method="POST" action="">
            <div id="depense-table">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all" onclick="toggleAllCheckboxes(this)"></th>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Montant</th>
                            <th>Observation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_depence->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" class="row-checkbox" name="selected_ids[]" value="<?php echo $row['depence_id']; ?>" onclick="checkIfAllChecked()"></td>
                                <td><?php echo $row['depence_id']; ?></td>
                                <td><?php echo $row['date_time']; ?></td>
                                <td><?php echo $row['reference']; ?></td>
                                <td><?php echo $row['montant']; ?></td>
                                <td><?php echo $row['observation']; ?></td>
                                <td>
                                    <a href="depence.php?mod=<?php echo $row['depence_id']; ?>"><i class="fa-solid fa-pen"></i></a> | 
                                    <a href="depence.php?sup=<?php echo $row['depence_id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette dépense ?')"><Src><i class="fa-solid fa-delete-left"></i></Src></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Bouton de suppression multiple -->
            <button type="submit" name="delete_selected">Supprimer les sélectionnées</button>
        </form>

        <div>
            <h2>Total des Dépenses: <?php echo number_format($total_depenses, 2, ",", " "); ?> MAD</h2>
        </div>
                <!-- Bouton d'impression -->
                <button onclick="printTable()">Imprimer le tableau</button>

    </div>
</body>
</html>
