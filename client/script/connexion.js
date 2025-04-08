const modal = document.getElementById("connexion-modal");
const closeButton = document.querySelector(".close-button");
const loginForm = document.getElementById("login-form");

// Gestion de la fermeture de la modale
closeButton.addEventListener("click", function () {
  modal.style.display = "none";
});

// Gestion de la soumission du formulaire
loginForm.addEventListener("submit", function (event) {
  event.preventDefault();

  // Récupération des valeurs des champs
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  // Validation des champs
  if (!username || !password) {
    alert("Veuillez remplir tous les champs.");
    return;
  }

  // Stockage des informations de connexion dans le localStorage
  localStorage.setItem("isLoggedIn", "true");
  localStorage.setItem("username", username);

  // Redirection vers la page d'accueil
  window.location.href = "/client/src/pageaccueil.html";
});

// Fonction pour valider le champ "Nom d'utilisateur"
function validerNomUtilisateur(input) {
  let cleaned = input.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, ''); // Supprime les caractères non autorisés
  input.value = cleaned;
}