document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('site-search');
    const resultsContainer = document.getElementById('custom-results');
    
    if (!input) return;

    // Masquer les résultats quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    input.addEventListener('input', async function() {
        const term = input.value.trim();
        
        if (term.length >= 2) {
            try {
                const response = await fetch(`/projetannuaire/client/src/autocomplete.php?q=${encodeURIComponent(term)}`);
                const results = await response.json();
                
                resultsContainer.innerHTML = '';
                
                if (results.length > 0) {
                    results.forEach(item => {
    const div = document.createElement('div');
    div.className = 'search-result-item';
    
    // Ajoutez plus d'informations pour différencier les résultats
    let displayText = item.name;
    if (item.type === 'user') {
        displayText = `${item.prenom} ${item.nom}`;
        if (item.service) {
            displayText += ` (${item.service})`;
        }
    }
    
    div.innerHTML = `
        <span class="result-icon">${ item.type === 'service' ? '🏢' : '👤'}</span>
        <span class="result-text">${displayText}</span>
    `;
                        
                        div.addEventListener('click', () => {
                            if (item.type === 'actualite') {
                                // URL directe vers l'actualité avec son ID
                                window.location.href = `/projetannuaire/client/src/actualite.php?id=${item.actualite_id}`;
                            } else {
                                window.location.href = `/projetannuaire/client/src/${item.url}`;
                            }
                        });
                        
                        resultsContainer.appendChild(div);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.style.display = 'none';
                }
            } catch (error) {
                console.error("Erreur recherche:", error);
                resultsContainer.style.display = 'none';
            }
        } else {
            resultsContainer.style.display = 'none';
        }
    });

    document.querySelector('.bouton-search').addEventListener('click', function() {
        const term = input.value.trim();
        if (term) {
            window.location.href = `/projetannuaire/client/src/recherche-global.php?q=${encodeURIComponent(term)}`;
        } else {
            alert("Veuillez saisir un terme de recherche.");
        }
    });
});