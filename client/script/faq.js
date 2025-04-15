$(document).ready(function() {
    // Fonction pour afficher les messages
    function showMessage(type, message) {
        const msgDiv = $('<div class="message"></div>').addClass(type).text(message);
        $('body').prepend(msgDiv);
        setTimeout(() => msgDiv.fadeOut(), 3000);
    }

    // =============================================
    // PARTIE 1 : AJOUTER UNE QUESTION (CORRIGÉ)
    // =============================================
    $('#envoyer-question').click(function() {
        const question = $('#nouvelle-question').val().trim();
        
        if (!question) {
            showMessage('error', 'Veuillez écrire une question');
            return;
        }

        $.post('/projetannuaire/client/src/ajouter-question.php', 
            { question: question },
            function(response) {
                if (response.startsWith('success|')) {
                    const newId = response.split('|')[1];
                    const newQuestionHtml = `
                        <div class="question-item" data-id="${newId}">
                            <div class="question-header">
                                <div class="question">${escapeHtml(question)}</div>
                                <div class="date">${new Date().toLocaleString('fr-FR')}</div>
                            </div>
                            <div class="reponse-container">
                                <div class="reponse">En attente de réponse...</div>
                                ${isUserLoggedIn() ? generateResponseForm() : ''}
                            </div>
                        </div>
                    `;
                    $('#questions-list').prepend(newQuestionHtml);
                    $('#nouvelle-question').val('');
                    showMessage('success', 'Question envoyée !');
                } else {
                    showMessage('error', response);
                }
            }
        ).fail(function() {
            showMessage('error', 'Erreur de connexion');
        });
    });

    // =============================================
    // FONCTIONS UTILITAIRES
    // =============================================
    function escapeHtml(text) {
        return text.replace(/&/g, "&amp;")
                  .replace(/</g, "&lt;")
                  .replace(/>/g, "&gt;")
                  .replace(/"/g, "&quot;")
                  .replace(/'/g, "&#039;");
    }

    function isUserLoggedIn() {
        return $('.btn-repondre').length > 0; // Vérifie si un bouton existe déjà
    }

    function generateResponseForm() {
        return `
            <button class="btn-repondre">Répondre</button>
            <div class="reponse-form" style="display:none;">
                <textarea class="reponse-input" placeholder="Votre réponse..."></textarea>
                <button class="btn-envoyer-reponse">Envoyer</button>
            </div>
        `;
    }

    // =============================================
    // GESTION DES RÉPONSES (DÉLÉGATION D'ÉVÉNEMENT)
    // =============================================
    $(document)
        .on('click', '.btn-repondre:not(.btn-repondu)', function() {
            $(this).next('.reponse-form').toggle();
        })
        .on('click', '.btn-envoyer-reponse', function() {
            const button = $(this);
            const container = button.closest('.question-item');
            const questionId = container.data('id');
            const reponse = button.siblings('.reponse-input').val().trim();
            
            if (!reponse) {
                showMessage('error', 'Veuillez saisir une réponse');
                return;
            }

            button.prop('disabled', true).text('Envoi en cours...');

            $.post('/projetannuaire/client/src/ajouter-reponse.php', 
                { 
                    id: questionId,
                    reponse: reponse 
                },
                function(response) {
                    if (response.trim() === 'success') {
                        container.find('.reponse').text(reponse);
                        container.find('.reponse-form').remove();
                        container.find('.btn-repondre')
                            .text('Répondu')
                            .addClass('btn-repondu')
                            .prop('disabled', true);
                        showMessage('success', 'Réponse enregistrée avec succès');
                    } else {
                        showMessage('error', response);
                    }
                }
            ).fail(function() {
                showMessage('error', 'Erreur de connexion au serveur');
            }).always(function() {
                button.prop('disabled', false).text('Envoyer');
            });
        });
});