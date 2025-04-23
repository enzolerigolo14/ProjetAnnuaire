// Définition de la fonction
function rechercher() {
    const terme = document.getElementById('site-search').value.trim();
    if (terme) {
        window.location.href = '/projetannuaire/client/src/recherche.php?q=' + encodeURIComponent(terme);
    } else {
        alert("Veuillez saisir un terme de recherche.");
    }
}

// Alternative avec écouteur d'événement
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.bouton-search').addEventListener('click', rechercher);
});