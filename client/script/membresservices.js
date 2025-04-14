
function rechercher() {
    const terme = document.getElementById('site-search').value.trim();
    
    if (terme) {
        // D'abord vérifier si c'est un service (vous devrez adapter cette partie)
        fetch('/projetannuaire/client/src/check-service.php?q=' + encodeURIComponent(terme))
            .then(response => response.json())
            .then(data => {
                if (data.isService) {
                    // Redirection vers membresservices.php si c'est un service
                    window.location.href = `membresservices.php?service=${encodeURIComponent(terme)}`;
                } else {
                    // Sinon faire une recherche normale
                    window.location.href = `recherche.php?q=${encodeURIComponent(terme)}`;
                }
            })
            .catch(() => {
                // En cas d'erreur, faire une recherche normale
                window.location.href = `recherche.php?q=${encodeURIComponent(terme)}`;
            });
    }
}

function validerRecherche(input) {
    // Validation du champ de recherche si nécessaire
    input.value = input.value.replace(/[^a-zA-Z0-9 éèêëàâäôöûüç'-]/g, '');
}
