<?php
// Inclusion de la connexion à la base de données
include('connexion.php');

// Initialisation des variables
$id = $date_reservation = $heure_debut = $duree = $capacite = $num_salle = "";
$error_message = "";
$types_salle_disponibles = [];

// Fonction pour calculer l'heure de fin
function calculerHeureFin($heureDebut, $duree) {
    $timestampDebut = strtotime($heureDebut);
    $timestampFin = strtotime("+$duree hours", $timestampDebut);
    return date("H:i", $timestampFin);
}

// Vérification si l'on modifie une réservation
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Récupérer la réservation existante
    $stmt = $pdo->prepare("SELECT * FROM reservation WHERE IdReservation = ?");
    $stmt->execute([$id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reservation) {
        $date_reservation = $reservation['DateReservation'];
        $heure_debut = $reservation['HeureDebut'];
        $duree = $reservation['Duree'];
        $capacite = $reservation['Capacite'];
        $num_salle = $reservation['NumSalle'];
    }
}

// Traitement du formulaire d'ajout ou modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_reservation = $_POST['date_reservation'];
    $heure_debut = $_POST['heure_debut'];
    $duree = $_POST['duree'];
    $capacite = $_POST['capacite'];
    $num_salle = $_POST['type_salle']; // Type de salle correspond à num_salle

    // Calcul de l'heure de fin de la nouvelle réservation
    $heure_fin = calculerHeureFin($heure_debut, $duree);

    // Vérification du chevauchement des réservations
    $stmt = $pdo->prepare("SELECT * FROM reservation WHERE DateReservation = ? AND NumSalle = ? AND IdReservation != ?");
    $stmt->execute([$date_reservation, $num_salle, $id]);
    $reservations_existantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chevauchement = false;
    $heure_libre = null; // Variable pour l'heure de disponibilité de la salle

    foreach ($reservations_existantes as $reservation_existante) {
        // Calcul de l'heure de fin de la réservation existante
        $heure_fin_existante = calculerHeureFin($reservation_existante['HeureDebut'], $reservation_existante['Duree']);
        
        // Vérification si les heures se chevauchent
        if (($heure_debut >= $reservation_existante['HeureDebut'] && $heure_debut < $heure_fin_existante) || 
            ($heure_fin > $reservation_existante['HeureDebut'] && $heure_fin <= $heure_fin_existante)) {
            $chevauchement = true;

            // Calcul de l'heure de fin de la réservation existante pour indiquer quand la salle sera libre
            $heure_libre = $heure_fin_existante;
            break;
        }
    }

    // Si un chevauchement est détecté, afficher un message d'erreur
    if ($chevauchement) {
        // Formatage de l'heure de libération (H:i)
        $heure_libre_formattee = date("H:i", strtotime($heure_libre));
        $error_message = "La réservation chevauche une autre réservation existante. La salle sera libre à partir de " . $heure_libre_formattee . ".";
    } else {
        // Si pas de chevauchement, ajouter ou mettre à jour la réservation
        if ($id) {
            // Mise à jour de la réservation
            $stmt = $pdo->prepare("UPDATE reservation SET DateReservation = ?, HeureDebut = ?, Duree = ?, Capacite = ?, NumSalle = ? WHERE IdReservation = ?");
            $stmt->execute([$date_reservation, $heure_debut, $duree, $capacite, $num_salle, $id]);
        } else {
            // Ajout d'une nouvelle réservation
            $stmt = $pdo->prepare("INSERT INTO reservation (DateReservation, HeureDebut, Duree, Capacite, NumSalle) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$date_reservation, $heure_debut, $duree, $capacite, $num_salle]);
        }
        

        // S'assurer que l'en-tête est envoyé avant toute sortie HTML
        ob_end_clean(); // Vide le tampon de sortie si nécessaire
        header("Location: index.php");
        exit; // Arrêter l'exécution du script après la redirection
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? "Modifier" : "Ajouter" ?> une réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center"><?= $id ? "Modifier" : "Ajouter" ?> une réservation</h2>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Formulaire de réservation -->
    <form method="POST">
        <div class="mb-3">
            <label for="date_reservation" class="form-label">Date de réservation</label>
            <input type="date" class="form-control" id="date_reservation" name="date_reservation" value="<?= $date_reservation ?>" min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="mb-3">
            <label for="heure_debut" class="form-label">Heure de début</label>
            <input type="time" class="form-control" id="heure_debut" name="heure_debut" value="<?= $heure_debut ?>" required>
        </div>
        <div class="mb-3">
            <label for="duree" class="form-label">Durée (en heures)</label>
            <input type="number" class="form-control" id="duree" name="duree" value="<?= $duree ?>" required>
        </div>
        <div class="mb-3">
            <label for="capacite" class="form-label">Capacité</label>
            <input type="number" class="form-control" id="capacite" name="capacite" value="<?= $capacite ?>" required>
        </div>
        <div class="mb-3">
            <label for="type_salle" class="form-label">Numéro de salle</label>
            <input type="number" class="form-control" id="type_salle" name="type_salle" value="<?= $num_salle ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><?= $id ? "Mettre à jour" : "Ajouter" ?></button>
    </form>
</div>
</body>
</html>
