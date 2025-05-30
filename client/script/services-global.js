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
                        const resultItem = $('<div class="search-result-item" tabindex="0"></div>');
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
                        
                        // Gestion du clic pour tous les types de fichiers
                        resultItem.on('click keyup', function(e) {
                            if (e.type === 'click' || (e.type === 'keyup' && e.key === 'Enter')) {
                                if (item.file_type === 'image') {
                                    // Pour les images: ouverture dans nouvel onglet + option de téléchargement
                                    const newWindow = window.open();
                                    if (newWindow) {
                                        newWindow.opener = null;
                                        newWindow.document.write(`
                                            <!DOCTYPE html>
                                            <html>
                                            <head>
                                                <title>${item.name}</title>
                                                <style>
                                                    body { margin: 0; padding: 20px; text-align: center; }
                                                    img { max-width: 100%; max-height: 90vh; }
                                                    .download-btn {
                                                        display: inline-block;
                                                        margin-top: 10px;
                                                        padding: 8px 15px;
                                                        background: #2196F3;
                                                        color: white;
                                                        text-decoration: none;
                                                        border-radius: 4px;
                                                    }
                                                </style>
                                            </head>
                                            <body>
                                                <img src="${item.url}" alt="${item.name}">
                                                <div>
                                                    <a href="${item.url}" class="download-btn" download="${item.name}.${item.extension}">
                                                        Télécharger l'image
                                                    </a>
                                                </div>
                                            </body>
                                            </html>
                                        `);
                                    }
                                } else {
                                    // Pour PDF et autres fichiers: ouverture directe dans nouvel onglet
                                    const newWindow = window.open(item.url, '_blank');
                                    if (!newWindow || newWindow.closed) {
                                        // Fallback si le navigateur bloque la popup
                                        window.location.href = item.url;
                                    }
                                }
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