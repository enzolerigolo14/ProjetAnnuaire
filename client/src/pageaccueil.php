<?php
session_start();
require_once __DIR__ . '/config/database.php';




?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trombinoscope ville de Lisieux</title>
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/pageaccueil.css">
    <link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/connexion.css">
    <script src="/projetannuaire/client/script/pageaccueil.js" defer></script>
    <script src="/projetannuaire/client/script/connexion.js"></script>
</head>

<body>
  <header>
    <div class="header-container">
      <div class="header-logo">
        <a href="https://www.ville-lisieux.fr/fr/" target="_blank">
          <img src="/projetannuaire/client/src/assets/images/logo-lisieux.png" alt="Logo de la ville de Lisieux" class="logo">
        </a>
      </div>
      
      <div class="search-container">
        <img src="/projetannuaire/client/src/assets/images/search-icon.png" alt="Search Icon" class="search-icon">
        <input type="text" id="site-search" placeholder="Nom, prénom, téléphone ou service" maxlength="32" oninput="validerRecherche(this)" />
        <button class="bouton-search" type="button" onclick="window.location.reload(false)" value="Rafraichir">Rechercher</button>
      </div>
      
      <div class="header-profile">
        <div id="user-profile">
          <span id="username-display"></span>
          <a href="/projetannuaire/client/src/profile.php" id="profil-link" class="header-button">
            <img src="/projetannuaire/client/src/assets/images/profile-icon.png" alt="Profil" class="profile-icon">
          </a>
          <a href="logout.php" id="deconnexion-button" class="header-button">Déconnexion</a>
        </div>
      </div>
    </div>
  </header>

  <nav class="navbar">
    <ul class="nav-list">
      <li>
        <a href="#services">Services</a>
        <ul class="dropdown">
          <li><a href="#service1">Nom</a></li>
          <li><a href="#service2">Prenom</a></li>
          <li><a href="#service3">Numero de téléphone</a></li>
          <li><a href="#service3">Service associé</a></li>
        </ul>
      </li>
      <li>
        <a href="#services">Services</a>
        <ul class="dropdown">
          <li><a href="#service1">Service 1</a></li>
          <li><a href="#service2">Service 2</a></li>
          <li><a href="#service3">Service 3</a></li>
        </ul>
      </li>
      <li>
        <a href="#services">Services</a>
        <ul class="dropdown">
          <li><a href="#service1">Service 1</a></li>
          <li><a href="#service2">Service 2</a></li>
          <li><a href="#service3">Service 3</a></li>
        </ul>
      </li>
    </ul>
  </nav>

  <div class="actualite">
    <h1>Actualités</h1>
    <div class="actualite-container">
      <div class="actualite-item">
        <img src="/projetannuaire/client/src/assets/images/Avis-denquete-publique.jpg" alt="Actualité 1" class="actualite-image">
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
    <div class="footer-container">
      <div class="footer-logo">
        <a href="https://www.ville-lisieux.fr/fr/" target="_blank">
          <img src="/projetannuaire/client/src/assets/images/logo-lisieux.png" alt="Logo de la ville de Lisieux" class="logo">
        </a>
      </div>
      <p>
        Tous droits réservés &copy; 2025 Ville de Lisieux<br>
      </p>
      <div class="footer-text">
        <a href="https://www.facebook.com/ville.lisieux" target="_blank">
          <img src="/projetannuaire/client/src/assets/images/icone-facebook.png" alt="Facebook" class="logo">
        </a>
        <a href="https://www.instagram.com/villedelisieux/" target="_blank">
          <img src="/projetannuaire/client/src/assets/images/icone-instagram.png" alt="Instagram" class="logo">
        </a>
        <a href="https://www.youtube.com/channel/UCNfbFMukFnEf9_eFVGtrR-w" target="_blank">
          <img src="/projetannuaire/client/src/assets/images/icone-youtube.png" alt="YouTube" class="logo">
        </a>
      </div>
    </div>
  </footer>
</body>
</html>