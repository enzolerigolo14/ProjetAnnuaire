<?php
session_start();

if (!isset($_SESSION['user'])) {
  header('Location: connexion.php');
  exit;
}

require_once __DIR__ . '/config/database.php';



$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();
error_reporting(E_ALL);
ini_set('display_errors', 1);


$stmt = $pdo->prepare("SELECT * FROM users where role = ? ");
$role = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/pageaccueil.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/footer.css">
    

    <script src="/projetannuaire/client/script/pageaccueil.js" defer></script>
    <script src="/projetannuaire/client/script/connexion.js"></script>
    <script src="/projetannuaire/client/script/membresservices.js"></script>
</head>

<body>
  <header>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  </header>



  <nav class="navbar">
    <ul class="nav-list">
      <li>
        <a href="">Agents</a>
        <ul class="dropdown">
          <li><a href="membreglobal.php">Nom , Prenom , role</a></li>
          <!--<li><a href="#service2">Prenom</a></li>
          <li><a href="#service3">Numero de téléphone</a></li>
          <li><a href="#service3">Service associé</a></li>-->
        </ul>
      </li>
      <li>
    <a href="">Services</a>
    <ul class="dropdown services-dropdown">
        <?php foreach ($services as $service): ?>
        <li><a href="membresservices.php?id=<?= $service['id'] ?>"><?= htmlspecialchars($service['nom']) ?></a></li>
        <?php endforeach; ?>
    </ul>
  </li>
  <li>
    <a href="services-global.php">Documents</a>
  </li>
      <li>
        <a href="faq.php">FAQ</a>
        <!--<ul class="dropdown">
          <li><a href="#service1">Service 1</a></li>
          <li><a href="#service2">Service 2</a></li>
          <li><a href="#service3">Service 3</a></li>
        </ul>-->
      </li>
    </ul>
  </nav>

  <div class="actualite">
    <h1>Actualités</h1>
    <div class="actualite-container">
      <div class="actualite-item">
        <img  src="/projetannuaire/client/src/assets/images/Avis-denquete-publique.jpg" alt="Actualité 1" class="actualite-image">
        <h3 class="actualite-title">AVIS D'ENQUÊTE PUBLIQUE - Route d'Orbec</h3>
        <p class="actualite-text">Du 7 avril au 7 mai, participez à l'enquete publique sur l'operation d'aménagement de la Route d'Orbec à Lisieux</p>
      </div>
      <div class="actualite-item">
        <img src="/projetannuaire/client/src/assets/images/Grand_Orgue_Cavaille_Coll.jpg" alt="Actualité 2" class="actualite-image">
        <h3 class="actualite-title">Rénovation de l'orgue Cavaillé-Coll</h3>
        <p class="actualite-text">Suite à des problèmes mécaniques et faiblesses harmoniques, l'orgue est renové dans son entirèté pour la première fois. Une restauration de grande 
          envergure qui permettra de valoriser ce patrimoine exceptionnel!</p> 
      </div>
      <div class="actualite-item">
        <img src="/projetannuaire/client/src/assets/images/Angela.jpg" alt="Actualité 3" class="actualite-image">
        <h3 class="actualite-title">Ici, demandez Angela</h3>
        <p class="actualite-text">Découvrez en plus sur ce dispositif, mis en place à Lisieux chez les commerçants</p>
      </div>
    </div>
  </div>

  <footer>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</footer>
</body>
</html>