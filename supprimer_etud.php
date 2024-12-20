<?php
require_once "connexion.php";

if (isset($_GET["id"])) {
    // Sanitize input
    $code = intval($_GET["id"]); // Cast to int for safety

    // Prepare and execute the statement
    $stmt = $conn->prepare("DELETE FROM etudiant_matiere WHERE etudiant_id = ?");
    $stmt->bind_param("i", $code); // Bind the parameter as an integer
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM classes WHERE etudiant_id = ?");
    $stmt->bind_param("i", $code); // Bind the parameter as an integer
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM paiements WHERE etudiant_id = ?");
    $stmt->bind_param("i", $code); // Bind the parameter as an integer
    $stmt->execute();



    $stmtt = $conn->prepare("DELETE FROM etudiants WHERE etudiant_id = ?");
    $stmtt->bind_param("i", $code); // Bind the parameter as an integer
    $stmtt->execute(); // Execute the statement to delete the record from the 'etudiants' table
    
        header("Location: etudiant.php");
        exit(); // Always use exit after a header redirect
    } else {
        // Optionally, handle the error (log it, show a message, etc.)
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close(); // Close the statement

$conn->close(); // Close the database connection
?>
