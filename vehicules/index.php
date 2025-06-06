<?php
session_start();
require_once(__DIR__ . '/../database/db.php');
require_once(__DIR__ . '/../security/connexion.php');

// Vérification de l'authentification
if(!isset($_SESSION['token']) || !isTokenValid($_SESSION['token'])){
    header("Location: /index.php");
    exit;
}

$conn = connectDB();

// Suppression d'un véhicule avec vérification CSRF
if(isset($_POST['delete']) && is_numeric($_POST['delete']) && isset($_POST['csrf_token'])) {
    // Vérification du token CSRF
    if(verifyCSRFToken($_POST['csrf_token'])) {
        $id = (int)$_POST['delete'];
        $stmt = $conn->prepare("DELETE FROM vehicules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: index.php?success=1");
        exit;
    } else {
        header("Location: index.php?error=csrf");
        exit;
    }
}

// Récupération des véhicules avec les noms des clients
$query = "SELECT v.*, c.nom as client_nom 
          FROM vehicules v 
          LEFT JOIN clients c ON v.client_id = c.id 
          ORDER BY v.id DESC";
$result = $conn->query($query);
$vehicules = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules - Garage Train</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Ajout de l'en-tête CSP pour limiter les sources de contenu -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdnjs.cloudflare.com; font-src https://cdnjs.cloudflare.com;">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-info"><i class="fa fa-car"></i> Gestion des Véhicules</h1>
            <a href="../dashboard.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Retour au tableau de bord</a>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            Opération réalisée avec succès !
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
        <div class="alert alert-danger" role="alert">
            Erreur de sécurité : action non autorisée.
        </div>
        <?php endif; ?>
        
        <div class="mb-4">
            <a href="ajouter.php" class="btn btn-info"><i class="fa fa-plus"></i> Ajouter un véhicule</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Marque</th>
                                <th>Modèle</th>
                                <th>Année</th>
                                <th>Client</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($vehicules)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucun véhicule trouvé</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($vehicules as $vehicule): ?>
                                <tr>
                                    <td><?= $vehicule['id'] ?></td>
                                    <td><?= htmlspecialchars($vehicule['marque']) ?></td>
                                    <td><?= htmlspecialchars($vehicule['modele']) ?></td>
                                    <td><?= $vehicule['annee'] ?></td>
                                    <td><?= htmlspecialchars($vehicule['client_nom'] ?? '') ?></td>
                                    <td>
                                        <a href="modifier.php?id=<?= $vehicule['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                        <!-- Formulaire pour la suppression avec token CSRF -->
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                            <input type="hidden" name="delete" value="<?= $vehicule['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>