<?php
// Inclusion de la connexion à la base de données
include('connexion.php');

// Vérification si un ID est passé dans l'URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Supprimer la réservation
    $stmt = $pdo->prepare("DELETE FROM reservation WHERE IdReservation = ?");
    $stmt->execute([$id]);
}

// Redirection vers la page principale après suppression
header("Location: index.php");
exit;
?>
