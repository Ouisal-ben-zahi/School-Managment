<?php
require_once "connexion.php";

if (isset($_GET['Ajouter']) && isset($_GET['prof_id'])) {
    $id_prof = intval($_GET['prof_id']); // Sécurisation de la donnée
    header('Location: classe_prof.php?id_prof=' . $id_prof);
    exit(); // Ajout d'un exit après une redirection
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
        <span class="menu-toggle" onclick="toggleMenu()">☰ Menu</span>
        <h1>Paiements Des Professeurs</h1>

        <!-- Formulaire -->
        <form method="GET" action="">
        <div class="form-row">
        <div class="form-group">
            <!-- Sélection du professeur -->
            <label for="prof_id">Sélectionner un professeur :</label>
            <select name="prof_id" id="prof_id" required>
                <option value="" disabled selected>Choisir un professeur</option>
                <?php
                // Requête pour récupérer les professeurs
                $profQuery = "SELECT DISTINCT prof_id, nom, prenom FROM professeurs";
                $resultProf = $conn->query($profQuery);

                if ($resultProf && $resultProf->num_rows > 0) {
                    while ($prof = $resultProf->fetch_assoc()) {
                        echo "<option value='" . intval($prof['prof_id']) . "'>" . htmlspecialchars($prof['nom'] . " " . $prof['prenom']) . "</option>";
                    }
                } else {
                    echo "<option value='' disabled>Aucun professeur disponible</option>";
                }
                ?>
            </select>

            </div></div>
            <!-- Bouton de soumission -->
            <button type="submit" name="Ajouter">Recherche</button>
        </form>
    </div>
</body>
</html>
