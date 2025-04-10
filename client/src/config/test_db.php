<?php
require_once 'database.php';

try {
    $pdo = Database::getInstance();
    
    // Test 1: Vérification de la connexion
    echo "<h3>1. Test de connexion</h3>";
    echo $pdo->query("SELECT 1") ? "✅ Connexion réussie" : "❌ Échec";
    
    // Test 2: Liste des tables
    echo "<h3>2. Tables existantes</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    print_r($tables);
    
    // Test 3: Contenu de la table users
    echo "<h3>3. Utilisateurs existants</h3>";
    $users = $pdo->query("SELECT id, prenom, nom, email_professionnel, role FROM users")->fetchAll();
    echo "<pre>".print_r($users, true)."</pre>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>Erreur</h3>";
    echo $e->getMessage();
}