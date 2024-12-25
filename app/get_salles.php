<?php
include('connexion.php');

if (isset($_GET['capacite'])) {
    $capacite = $_GET['capacite'];
    $stmt = $pdo->prepare("SELECT NumSalle, TypeSalle, Capacite FROM salle WHERE Capacite >= ? ORDER BY Capacite ASC");
    $stmt->execute([$capacite]);
    $salles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($salles) > 0) {
        foreach ($salles as $salle) {
            // Afficher le type de salle, capacité et numéro de la salle
            echo "<option value=\"{$salle['NumSalle']}\">{$salle['TypeSalle']} (Capacité : {$salle['Capacite']}, Numéro Salle : {$salle['NumSalle']})</option>";
        }
    } else {
        echo "<option disabled>Aucune salle disponible</option>";
    }
}
?>
