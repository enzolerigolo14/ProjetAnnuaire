$(document).ready(function() {
    const searchInput = $('#actualite-search');
    const searchResults = $('#search-results');
    
    searchInput.on('input', function() {
        const term = $(this).val().trim().toLowerCase();
        searchResults.empty();
        
        if (term.length >= 2) {
            const matchedActualites = allActualites.filter(actualite => 
                actualite.titre.toLowerCase().includes(term)
            ).slice(0, 10);
            
            if (matchedActualites.length > 0) {
                matchedActualites.forEach(actualite => {
                    const resultItem = $('<div class="search-result-item"></div>');
                    const title = $('<div class="result-title"></div>').text(actualite.titre);
                    const service = $('<div class="result-service"></div>').text('Service: ' + actualite.service_nom);
                    const pdfIcon = $('<span class="pdf-icon">📄</span>');
                    
                    resultItem.append(pdfIcon).append(title).append(service);
                    resultItem.click(function() {
                       
                        window.open(actualite.pdf_url, '_blank');
                    });
                    
                    searchResults.append(resultItem);
                });
                searchResults.show();
            } else {
                searchResults.html('<div class="no-results">Aucune actualité avec PDF trouvée</div>').show();
            }
        } else {
            searchResults.hide();
        }
    });
    
    // Cacher les résultats quand on clique ailleurs
    $(document).click(function(e) {
        if (!$(e.target).closest('.search-container').length) {
            searchResults.hide();
        }
    });
    
    // Navigation au clavier
    searchInput.on('keydown', function(e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter') {
            const items = $('.search-result-item');
            const currentFocus = $('.search-result-item:focus');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentFocus.length === 0) {
                    items.first().focus();
                } else if (currentFocus.next().length) {
                    currentFocus.next().focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentFocus.prev().length) {
                    currentFocus.prev().focus();
                } else {
                    searchInput.focus();
                }
            } else if (e.key === 'Enter' && currentFocus.length) {
                e.preventDefault();
                currentFocus.click();
            }
        }
    });
});