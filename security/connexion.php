<?php

require_once(__DIR__ . '/../database/db.php');

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier un token CSRF
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

function loginWithToken($email, $password) {
    $conn = connectDB();
    $query = "SELECT id, email, password_hash FROM administrateurs WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $adminId = $row['id'];
        $hashedPassword = $row['password_hash'];

        if (password_verify($password, $hashedPassword)) {
            $token = generateToken();

            // Supprimer les anciens tokens de cet utilisateur
            $deleteQuery = "DELETE FROM tokens WHERE user_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $adminId);
            $deleteStmt->execute();

            $expirationDate = date('Y-m-d H:i:s', strtotime('+1 day'));
            $insertQuery = "INSERT INTO tokens (user_id, token, expiration_date) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iss", $adminId, $token, $expirationDate);
            $insertStmt->execute();
            return $token;
        }
    }
    // Délai pour prévenir les attaques par timing
    sleep(1);
    return false;
}

function isTokenInDatabase($token) {
    $conn = connectDB();

    $query = "SELECT COUNT(*) AS token_count FROM tokens WHERE token = ? AND expiration_date > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $tokenCount = $row['token_count'];

        return $tokenCount > 0;
    }
    return false;
}

function isTokenValid($token) {
    if(isset($token) && !empty($token)){
        return isTokenInDatabase($token);
    }
    else{
        return false;
    }
}

// Fonction pour nettoyer les entrées utilisateur
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}