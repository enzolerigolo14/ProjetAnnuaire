$(document).ready(function() {
    const searchInput = $('#document-search');
    const searchResults = $('#search-results');
    
    searchInput.on('input', function() {
        const term = $(this).val().trim();
        
        if (term.length >= 2) {
            $.get('search_documents.php', { q: term }, function(data) {
                searchResults.empty();
                
                if (data.length > 0) {
                    data.forEach(item => {
                        const resultItem = $('<div class="search-result-item"></div>');
                        const name = $('<div class="result-name"></div>').text(item.name);
                        const service = $('<div class="result-service"></div>').text('Service: ' + item.service);
                        
                        // Ajouter une icône selon le type de fichier
                        let icon = '';
                        if (item.file_type === 'pdf') {
                            icon = '📄';
                        } else if (item.file_type === 'image') {
                            icon = '🖼️';
                        } else {
                            icon = '📁';
                        }
                        name.prepend(`<span class="file-icon">${icon}</span> `);
                        
                        resultItem.append(name).append(service);
                        resultItem.click(function(e) {
                            if (item.file_type === 'pdf') {
                                // PDF: ouverture dans la même fenêtre
                                window.location.href = item.url;
                            } else if (item.file_type === 'image') {
                                // Image: création d'un lien de téléchargement forcé
                                const downloadLink = document.createElement('a');
                                downloadLink.href = item.url + '&download=1';
                                downloadLink.download = item.name + '.' + item.extension;
                                document.body.appendChild(downloadLink);
                                downloadLink.click();
                                document.body.removeChild(downloadLink);
                            } else {
                                // Autres fichiers: comportement normal
                                window.location.href = item.url;
                            }
                        });
                        
                        searchResults.append(resultItem);
                    });
                    searchResults.show();
                } else {
                    searchResults.html('<div class="no-results">Aucun document trouvé</div>').show();
                }
            }, 'json');
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