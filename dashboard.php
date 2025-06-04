<?php
session_start();

require_once('database/db.php');
$conn = connectDB();

$result = $conn->query("SELECT COUNT(*) AS total_clients FROM clients");
$row = $result->fetch_assoc();
$totalClients = $row['total_clients'];

$result = $conn->query("SELECT COUNT(*) AS total_vehicules FROM vehicules");
$row = $result->fetch_assoc();
$totalVehicules = $row['total_vehicules'];

$result = $conn->query("SELECT COUNT(*) AS total_rendezvous FROM rendezvous");
$row = $result->fetch_assoc();
$totalRendezvous = $row['total_rendezvous'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Garage Train</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-info mb-4"><i class="fa fa-car"></i> Tableau de Bord Garage Train</h1>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Clients</h2>
                        <p class="card-text">Total Clients: <?= $totalClients ?></p>
                        <a href="#" class="btn btn-outline-info">Gérer les clients</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Véhicules</h2>
                        <p class="card-text">Total Véhicules: <?= $totalVehicules ?></p>
                        <a href="vehicules/index.php" class="btn btn-info">Gérer les véhicules</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Rendez-vous</h2>
                        <p class="card-text">Total Rendez-vous: <?= $totalRendezvous ?></p>
                        <a href="#" class="btn btn-outline-info">Gérer les rendez-vous</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
