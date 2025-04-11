<?php
$host = 'localhost'; 
$dbname = 'projettrombinoscope';
$username = 'root'; 
$password = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    //echo "Connexion réussie à la base de données." . "<br>";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}

$stmt = $pdo->query("SELECT * FROM users");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //echo $row['prenom'] . ' ' . $row['nom'] . '<br>';
}

?>