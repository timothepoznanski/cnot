// Gestion de l'affichage des boutons de formatage sur mobile lors de la sélection de texte

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si on est sur mobile
    const isMobile = window.innerWidth <= 800;
    
    if (!isMobile) return; // Ne pas exécuter ce script sur desktop
    
    let selectionTimer;
    
    // Fonction pour afficher/cacher les boutons de formatage
    function toggleFormatButtons() {
        const selection = window.getSelection();
        const formatButtons = document.querySelectorAll('.format-buttons');
        
        if (selection.toString().length > 0) {
            // Il y a du texte sélectionné, afficher les boutons
            formatButtons.forEach(toolbar => {
                toolbar.classList.add('show');
            });
        } else {
            // Pas de sélection, cacher les boutons
            formatButtons.forEach(toolbar => {
                toolbar.classList.remove('show');
            });
        }
    }
    
    // Écouter les événements de sélection
    document.addEventListener('selectionchange', function() {
        // Utiliser un timer pour éviter trop d'appels
        clearTimeout(selectionTimer);
        selectionTimer = setTimeout(toggleFormatButtons, 100);
    });
    
    // Écouter aussi les clics sur les éléments éditables
    document.addEventListener('click', function(e) {
        if (e.target.closest('.noteentry')) {
            setTimeout(toggleFormatButtons, 100);
        }
    });
    
    // Écouter les événements tactiles pour mobile
    document.addEventListener('touchend', function(e) {
        if (e.target.closest('.noteentry')) {
            setTimeout(toggleFormatButtons, 150);
        }
    });
    
    // Cacher les boutons quand on clique en dehors d'une note
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notecard')) {
            const formatButtons = document.querySelectorAll('.format-buttons');
            formatButtons.forEach(toolbar => {
                toolbar.classList.remove('show');
            });
        }
    });
});
