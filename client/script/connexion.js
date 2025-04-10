const modal = document.getElementById("connexion-modal");
const closeButton = document.querySelector(".close-button");
const loginForm = document.getElementById("login-form");

// Gestion de la fermeture de la modale
closeButton.addEventListener("click", function () {
  modal.style.display = "none";
});

// Fonction pour valider le champ "Nom d'utilisateur"
function validerNomUtilisateur(input) {
  let cleaned = input.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, ''); // Supprime les caractères non autorisés
  input.value = cleaned;
}