document.addEventListener("DOMContentLoaded", () => {
  const btnAjout = document.getElementById("modifier-actualite");
  const formulaire = document.getElementById("actualite-form");
  const btnAnnuler = document.getElementById("annuler-actualite");
  const overlay = document.getElementById("overlay");

  if (btnAjout && formulaire && btnAnnuler && overlay) {
      btnAjout.addEventListener("click", () => {
          formulaire.classList.remove("hidden");
          overlay.style.display = "block";
      });

      btnAnnuler.addEventListener("click", () => {
          formulaire.classList.add("hidden");
          overlay.style.display = "none";
      });

      overlay.addEventListener("click", () => {
          formulaire.classList.add("hidden");
          overlay.style.display = "none";
      });
  }
});
