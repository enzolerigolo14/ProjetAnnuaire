function rechercher() {
    const terme = document.getElementById('site-search').value.trim();
    if (terme) {
        window.location.href = '/projetannuaire/client/src/recherche-membre.php?q=' + encodeURIComponent(terme);
    } else {
        alert("Veuillez saisir un terme de recherche.");
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('site-search');
    const datalist = document.getElementById('suggestions');

    input.addEventListener('input', async function () {
        const value = input.value.trim();
        if (value.length >= 2) {
            try {
                const response = await fetch(`/projetannuaire/client/src/autocomplete.php?q=${encodeURIComponent(value)}`);
                const suggestions = await response.json();

                datalist.innerHTML = '';
                suggestions.forEach(name => {
                    const option = document.createElement('option');
                    option.value = name;
                    datalist.appendChild(option);
                });
            } catch (e) {
                console.error("Erreur lors du chargement des suggestions", e);
            }
        }
    });

    document.querySelector('.bouton-search').addEventListener('click', rechercher);
});
