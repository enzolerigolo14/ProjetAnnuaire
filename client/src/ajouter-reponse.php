<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Erreur : méthode non autorisée');
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die('Erreur : ID invalide');
}

if (empty($_POST['reponse'])) {
    die('Erreur : réponse vide');
}

try {
    $stmt = $pdo->prepare("UPDATE faq SET reponse = ? WHERE id = ?");
    $stmt->execute([$_POST['reponse'], $_POST['id']]);
    echo 'success';
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}