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

document.addEventListener("DOMContentLoaded", function () {
    const featuredNews = document.querySelector(".featured-news");
    const newsSidebar = document.querySelector(".news-sidebar");

    newsSidebar.querySelectorAll(".news-item").forEach(item => {
        item.addEventListener("click", function () {
            const clickedClone = item.cloneNode(true);
            const currentFeatured = featuredNews.cloneNode(true);

            // Récupérer l'élément parent de la colonne principale
            const mainContainer = document.querySelector(".main-container");

            // Remplacer l'ancien featured par le news-item
            featuredNews.replaceWith(clickedClone);
            clickedClone.classList.remove("news-item");
            clickedClone.classList.add("featured-news");

            // Supprimer l'ancien item de droite
            item.remove();

            // Adapter le HTML de l'ancien featured pour qu’il devienne un "news-item"
            const newSidebarItem = currentFeatured;
            newSidebarItem.classList.remove("featured-news");
            newSidebarItem.classList.add("news-item");

            // Enlever les éléments qui ne sont pas nécessaires dans le sidebar (comme les boutons supprimer)
            const deleteBtn = newSidebarItem.querySelector('.actualite-actions');
            if (deleteBtn) deleteBtn.remove();

            // Ajouter le nouvel item à droite
            newsSidebar.prepend(newSidebarItem);

            // Réattacher les events au nouvel item ajouté
            newSidebarItem.addEventListener("click", function () {
                // Reappel récursif pour pouvoir continuer les échanges
                location.reload(); // ou récursivement refaire ce code, mais ici reload est plus simple pour gérer les conflits
            });
        });
    });
});
