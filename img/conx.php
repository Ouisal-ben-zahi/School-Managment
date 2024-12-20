<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'alphabridge';

// Création de la connexion
$conn = new mysqli($host, $user, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
?>
