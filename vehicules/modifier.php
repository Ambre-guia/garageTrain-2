<?php
session_start();
require_once('../database/db.php');
require_once('../security/connexion.php');

// Vérification de la sécurité de la session
if(!isset($_SESSION['token']) || !isTokenValid($_SESSION['token'])){
    header("Location: /index.php");
    exit;
}

$conn = connectDB();
$error = false;

// Vérification de l'ID du véhicule
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Récupération des données d'un véhicule
$stmt = $conn->prepare("SELECT * FROM vehicules WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$vehicule = $result->fetch_assoc();

// Récupération de la liste des clients
$result = $conn->query("SELECT id, nom FROM clients ORDER BY nom");
$clients = $result->fetch_all(MYSQLI_ASSOC);

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marque = trim($_POST['marque']);
    $modele = trim($_POST['modele']);
    $annee = $_POST['annee'] ? (int)$_POST['annee'] : null;
    $client_id = $_POST['client_id'] ? (int)$_POST['client_id'] : null;
    
    if(empty($marque) || empty($modele)) {
        $error = "Les champs Marque et Modèle sont obligatoires";
    } else {
        $stmt = $conn->prepare("UPDATE vehicules SET marque = ?, modele = ?, annee = ?, client_id = ? WHERE id = ?");
        $stmt->bind_param("ssiii", $marque, $modele, $annee, $client_id, $id);
        
        if($stmt->execute()) {
            header("Location: index.php?success=1");
            exit;
        } else {
            $error = "Erreur lors de la modification du véhicule: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Véhicule - Garage Train</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-info"><i class="fa fa-car"></i> Modifier un Véhicule</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Retour à la liste</a>
        </div>
        
        <?php if($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="marque" class="form-label">Marque *</label>
                        <input type="text" class="form-control" id="marque" name="marque" value="<?= htmlspecialchars($vehicule['marque']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modele" class="form-label">Modèle *</label>
                        <input type="text" class="form-control" id="modele" name="modele" value="<?= htmlspecialchars($vehicule['modele']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="annee" class="form-label">Année</label>
                        <input type="number" class="form-control" id="annee" name="annee" min="1900" max="<?= date('Y') + 1 ?>" value="<?= $vehicule['annee'] ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">-- Sélectionner un client --</option>
                            <?php foreach($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= ($client['id'] == $vehicule['client_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning">Modifier le véhicule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>