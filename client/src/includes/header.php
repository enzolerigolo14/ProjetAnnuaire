<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();

require_once __DIR__ . '/../config/database.php';


}
?>

<div class="header-container">
  <div class="header-logo">
    <a href="https://www.ville-lisieux.fr/fr/" target="_blank">
      <img src="/projetannuaire/client/src/assets/images/logo-lisieux.png" alt="Logo de la ville de Lisieux" class="logo">
    </a>
  </div>

  <div class="search-container">
    <img src="/projetannuaire/client/src/assets/images/search-icon.png" alt="Search Icon" class="search-icon">
    <input type="text" id="site-search" placeholder="Nom, prénom, téléphone ou service" maxlength="32" />
    <button class="bouton-search" type="button" onclick="rechercher()">Rechercher</button>
  </div>

  <div class="header-profile">
    <div id="user-profile">
      <span id="username-display"></span>

      <a href="/projetannuaire/client/src/profile.php" id="profil-link" class="header-button">
        <img src="/projetannuaire/client/src/assets/images/profile-icon.png" alt="Profil" class="profile-icon">
      </a>

      <?php
      if (isset($_SESSION['user']['role'])) {
          $role = strtoupper($_SESSION['user']['role']); // en majuscules pour éviter les erreurs de casse
          if ($role === 'SVC-INFORMATIQUE') {
              echo '<a href="/projetannuaire/client/src/inscription.php" class="header-button" title="Paramètres">';
              echo '<img src="/projetannuaire/client/src/assets/images/settings-icon.png" alt="Paramètres" class="profile-icon">';
              echo '</a>';
          }
      }
      ?>
    </div>
  </div>
</div>

<script src="/projetannuaire/client/script/rechercher.js" defer></script>
