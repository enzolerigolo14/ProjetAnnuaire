const modal = document.getElementById("connexion-modal");
const closeButton = document.querySelector(".close-button");
const loginForm = document.getElementById("login-form");
const username = document.getElementById("username").value;

localStorage.setItem("isLoggedIn", "true");
localStorage.setItem("username", username);

closeButton.addEventListener("click", function () {
  modal.style.display = "none";
});


loginForm.addEventListener("submit", function (event) {
  event.preventDefault(); 
  window.location.href = "/client/src/pageaccueil.html";
});


function validerNomUtilisateur(input) {
  let cleaned = input.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, '');
  //const onlyDigits = cleaned.replace(/\D/g, ''); 
  input.value = cleaned;
}
  
  