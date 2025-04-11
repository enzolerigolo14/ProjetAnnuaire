<?php
session_start();
header('Content-Type: application/json');

// CONNEXION BDD (à adapter)
$host = 'localhost';
$dbname = 'projettrombinoscope';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Connexion BDD échouée']);
    exit;
}

// RÉCUPÉRATION DONNÉES
$data = json_decode(file_get_contents('php://input'), true);

// VÉRIFICATION SIMPLIFIÉE (pour test)
if (!isset($_SESSION['user_id'])) {
    // En mode test, on fixe un user_id manuellement
    $_SESSION['user_id'] = 1; // À ENLEVER EN PRODUCTION !
}

$userId = $_SESSION['user_id'];
$oldPassword = $data['old_password'];
$newPassword = $data['new_password'];

// REQUÊTE SIMPLE
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt->execute([$hashedPassword, $userId]);

echo json_encode(['success' => true]);