<?php
session_start();
include('../connexion.php'); // Inclure la connexion à la base de données

// Initialiser les étapes du formulaire
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
    $_SESSION['reservation'] = [];
}

// Sauvegarder les données du formulaire dans la session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer les options "Nouvelle réservation" ou "Annuler réservation"
    if (isset($_POST['new_reservation'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['cancel_reservation'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Gérer les étapes du formulaire
    switch ($_SESSION['step']) {
        case 1:
            if (isset($_POST['reserve'])) {
                $_SESSION['reservation']['reserve'] = $_POST['reserve'];
                if ($_POST['reserve'] === 'oui') {
                    $_SESSION['step']++;
                }
            } else {
                echo "<p class='text-danger'>Veuillez répondre à la question.</p>";
            }
            break;

        case 2:
            if (isset($_POST['date'])) {
                $_SESSION['reservation']['date'] = $_POST['date'];
                $_SESSION['step']++;
            } else {
                echo "<p class='text-danger'>Veuillez sélectionner une date.</p>";
            }
            break;

        case 3:
            if (isset($_POST['heure'])) {
                $_SESSION['reservation']['heure'] = $_POST['heure'];
                $_SESSION['step']++;
            } else {
                echo "<p class='text-danger'>Veuillez choisir une heure.</p>";
            }
            break;

        case 4:
            if (isset($_POST['duree'])) {
                $_SESSION['reservation']['duree'] = $_POST['duree'];
                $_SESSION['step']++;
            } else {
                echo "<p class='text-danger'>Veuillez indiquer la durée.</p>";
            }
            break;

        case 5:
            if (isset($_POST['capacite'])) {
                $_SESSION['reservation']['capacite'] = $_POST['capacite'];
                $_SESSION['step']++;
            } else {
                echo "<p class='text-danger'>Veuillez indiquer la capacité.</p>";
            }
            break;

        case 6:
            // Enregistrer la réservation dans la base de données
            $reservation = $_SESSION['reservation'];
            $conn = new mysqli('localhost', 'root', '', 'res_db');
            if ($conn->connect_error) {
                die("Erreur de connexion : " . $conn->connect_error);
            }
            $stmt = $conn->prepare("INSERT INTO reservation (DateReservation, HeureDebut, Duree, Capacite, NumEmploye) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssiii', $reservation['date'], $reservation['heure'], $reservation['duree'], $reservation['capacite'], 10); // Remplacez 10 par l'ID de l'utilisateur connecté
            $stmt->execute();
            $stmt->close();
            $conn->close();
            $_SESSION['step']++;
            break;

        default:
            session_destroy();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Réservation</title>
    <style>
        .card {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="container py-5">
    <div class="card">
        <h1 class="mb-4 text-center">Formulaire de Réservation</h1>

        <!-- Bouton pour commencer une nouvelle réservation -->
        <form method="POST" action="" class="mb-3 text-center">
            <button type="submit" name="new_reservation" class="btn btn-secondary">Nouvelle Réservation</button>
        </form>

        <!-- Formulaire de réservation -->
        <form method="POST" action="">
            <?php if ($_SESSION['step'] === 1): ?>
                <div class="mb-3">
                    <label for="reserve" class="form-label">Voulez-vous réserver ?</label>
                    <select class="form-select" name="reserve" id="reserve" required>
                        <option value="">Choisissez...</option>
                        <option value="oui">Oui</option>
                        <option value="non">Non</option>
                    </select>
                </div>
            <?php elseif ($_SESSION['step'] === 2): ?>
                <div class="mb-3">
                    <label for="date" class="form-label">Choisissez la Date de réservation :</label>
                    <input type="date" class="form-control" name="date" id="date" required>
                </div>
            <?php elseif ($_SESSION['step'] === 3): ?>
                <div class="mb-3">
                    <label for="heure" class="form-label">Choisissez l'Heure de début :</label>
                    <input type="time" class="form-control" name="heure" id="heure" required>
                </div>
            <?php elseif ($_SESSION['step'] === 4): ?>
                <div class="mb-3">
                    <label for="duree" class="form-label">Durée (en heures) :</label>
                    <input type="number" class="form-control" name="duree" id="duree" min="1" required>
                </div>
            <?php elseif ($_SESSION['step'] === 5): ?>
                <div class="mb-3">
                    <label for="capacite" class="form-label">Capacité :</label>
                    <input type="number" class="form-control" name="capacite" id="capacite" min="1" required>
                </div>
            <?php elseif ($_SESSION['step'] === 6): ?>
                <div class="mb-3">
                    <h4>Votre réservation a été bien effectuée :</h4>
                    <p>Date : <?= $_SESSION['reservation']['date'] ?></p>
                    <p>Heure : <?= $_SESSION['reservation']['heure'] ?></p>
                    <p>Durée : <?= $_SESSION['reservation']['duree'] ?> heures</p>
                    <p>Capacité : <?= $_SESSION['reservation']['capacite'] ?></p>
                </div>
                <button type="submit" class="btn btn-primary">Terminer</button>
            <?php endif; ?>

            <?php if ($_SESSION['step'] < 6): ?>
                <button type="submit" class="btn btn-primary">Suivant</button>
            <?php endif; ?>

            <!-- Bouton pour annuler la réservation -->
            <button type="submit" name="cancel_reservation" class="btn btn-danger">Annuler</button>
        </form>
    </div>
</body>
</html>
