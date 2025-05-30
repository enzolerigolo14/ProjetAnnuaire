<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();

require_once __DIR__ . '/../config/database.php';


}
?>

<div class="header-container">
  <div class="header-logo">
    <a href="https://www.ville-lisieux.fr/fr/" target="_blank">
      <img src="/projetannuaire/client/src/assets/images/logo-ville-lisieux.jpg" alt="Logo de la ville de Lisieux" class="logo">
    </a>
  </div>

  <div class="search-container">
    <img src="/projetannuaire/client/src/assets/images/search-icon.png" alt="Search Icon" class="search-icon">
    <input type="text" id="site-search" placeholder="Nom, prénom ou service" maxlength="32" autocomplete="off" />
    <button class="bouton-search" type="button">Rechercher</button>
    <!-- On ajoute notre liste personnalisée -->
    <div id="custom-results" class="search-results"></div>
</div>

  <div class="header-profile">
    <div id="user-profile">
      <span id="username-display"></span>

      <a href="/projetannuaire/client/src/profile.php" id="profil-link" class="header-button">
        <img src="/projetannuaire/client/src/assets/images/profile-icon.png" alt="Profil" class="profile-icon">
      </a>

      <?php
if (isset($_SESSION['user']['role'])) {
    $role = strtoupper($_SESSION['user']['role']);
    if ($role === 'SVC-INFORMATIQUE' || $role === 'ADMIN-INTRA' || $role === 'ADMIN-RH') {
        echo '<a href="/projetannuaire/client/src/parametre.php" class="header-button inscription-link" title="Paramètres">
                <img src="/projetannuaire/client/src/assets/images/settings.png" alt="Icône paramètres" class="settings-icon"> 
              </a>';
    }
}
?>
    </div>
  </div>
</div>

<script src="/projetannuaire/client/script/rechercher.js" defer></script>
<link rel="stylesheet" href="/projetannuaire/client/src/assets/styles/header.css">