<?php
require_once __DIR__ . '/../client/src/config/database.php';

session_start();
header('Content-Type: application/json');

if (!isset($_GET['userId'])) {
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
    exit;
}

$userId = $_GET['userId'];

try {
    $pdo = $pdo ?? null;
    if (!$pdo) {
        // Connexion manuelle si getPDO() n'existe pas
        $host = 'localhost';
        $dbname = 'projettrombinoscope';
        $username = 'root';
        $password = '';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

$stmt = $pdo->prepare("SELECT telephone, telephone_interne FROM users WHERE telephone = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'success' => true,
            'telephone' => $user['telephone'] ?? '',
            'telephone_internal' => $user['telephone_interne'] ?? '' // ici on renvoie "telephone_internal" mais lu depuis "telephone_interne"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
