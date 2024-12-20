<?php 
if (isset($_GET['classe_id'], $_GET['id_prof'], $_GET['percentage'])) {
    // Récupérer les valeurs des paramètres de l'URL
    $classe_id = (int)$_GET['classe_id'];
    $prof_id = (int)$_GET['id_prof'];
    $percentage = (int)$_GET['percentage'];

    // Connexion à la base de données
    require_once "connexion.php";  // Assurez-vous que le fichier de connexion est correct

    // Préparer la requête de mise à jour
    $updateSQL = "UPDATE prof_classe SET percentage = ? WHERE classe_id = ? AND prof_id = ?";

    if ($stmt = $conn->prepare($updateSQL)) {
        // Lier les paramètres
        $stmt->bind_param("iii", $percentage, $classe_id, $prof_id);

        // Exécuter la requête de mise à jour
        if ($stmt->execute()) {
            echo "Les informations ont été mises à jour avec succès.";
            // Correction de la redirection : utilisation de guillemets doubles et fermeture correcte de la variable
            header('Location: classe_prof.php?id_prof=' . $prof_id);
            exit;
        } else {
            echo "Erreur lors de la mise à jour : " . $stmt->error;
        }

        // Fermer la déclaration
        $stmt->close();
    } else {
        echo "Erreur lors de la préparation de la requête : " . $conn->error;
    }

    // Fermer la connexion à la base de données
    $conn->close();
} else {
    echo "Paramètres manquants dans l'URL.";
}
?>
