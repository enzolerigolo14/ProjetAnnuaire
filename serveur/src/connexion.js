const modal = document.getElementById("connexion-modal");
const closeButton = document.querySelector(".close-button");
const loginForm = document.getElementById("login-form");


closeButton.addEventListener("click", function () {
  modal.style.display = "none";
});


loginForm.addEventListener("submit", function (event) {
  event.preventDefault(); 
  window.location.href = "/client/src/pageaccueil.html";
});
const username = document.getElementById("username").value;
  localStorage.setItem("isLoggedIn", "true");
  localStorage.setItem("username", username);