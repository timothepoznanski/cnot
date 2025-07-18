// JavaScript pour la page trash

document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la recherche dans les notes de la corbeille
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const noteCards = document.querySelectorAll('.trash-notecard');
            let visibleCount = 0;
            
            noteCards.forEach(card => {
                const title = card.querySelector('.css-title');
                const content = card.querySelector('.noteentry');
                
                let titleText = title ? title.textContent.toLowerCase() : '';
                let contentText = content ? content.textContent.toLowerCase() : '';
                
                if (titleText.includes(searchTerm) || contentText.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Afficher le nombre de résultats
            updateSearchResults(visibleCount, searchTerm);
        });
    }
    
    // Gestion des boutons de restauration et suppression définitive
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('icon_restore_trash')) {
            e.preventDefault();
            const noteid = e.target.getAttribute('data-noteid');
            if (noteid && confirm('Voulez-vous restaurer cette note ?')) {
                restoreNote(noteid);
            }
        }
        
        if (e.target.classList.contains('icon_trash_trash')) {
            e.preventDefault();
            const noteid = e.target.getAttribute('data-noteid');
            if (noteid && confirm('Voulez-vous supprimer définitivement cette note ? Cette action est irréversible.')) {
                deleteNote(noteid);
            }
        }
    });
    
    // Gestion du bouton "Vider la corbeille"
    const emptyTrashBtn = document.getElementById('emptyTrashBtn');
    if (emptyTrashBtn) {
        emptyTrashBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Voulez-vous vider complètement la corbeille ? Cette action est irréversible.')) {
                window.location.href = 'emptytrash.php';
            }
        });
    }
});

function updateSearchResults(count, searchTerm) {
    let resultsDiv = document.getElementById('searchResults');
    if (!resultsDiv) {
        resultsDiv = document.createElement('div');
        resultsDiv.id = 'searchResults';
        resultsDiv.className = 'trash-search-results';
        
        const searchContainer = document.querySelector('.trash-search-input').parentNode;
        searchContainer.appendChild(resultsDiv);
    }
    
    if (searchTerm.trim() === '') {
        resultsDiv.style.display = 'none';
    } else {
        resultsDiv.style.display = 'block';
        resultsDiv.textContent = `${count} note(s) trouvée(s) pour "${searchTerm}"`;
    }
}

function restoreNote(noteid) {
    fetch('putback.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'noteid=' + encodeURIComponent(noteid)
    })
    .then(response => response.text())
    .then(data => {
        // Recharger la page pour mettre à jour la liste
        window.location.reload();
    })
    .catch(error => {
        console.error('Erreur lors de la restauration:', error);
        alert('Erreur lors de la restauration de la note');
    });
}

function deleteNote(noteid) {
    fetch('permanentDelete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'noteid=' + encodeURIComponent(noteid)
    })
    .then(response => response.text())
    .then(data => {
        // Recharger la page pour mettre à jour la liste
        window.location.reload();
    })
    .catch(error => {
        console.error('Erreur lors de la suppression:', error);
        alert('Erreur lors de la suppression définitive de la note');
    });
}

// Optimisation pour mobile : gestion du scroll
if (window.innerWidth <= 800) {
    document.body.style.overflow = 'auto';
    document.body.style.height = 'auto';
}
