// Fichier extrait depuis index.php pour la logique JS principale (hors plugins jQuery)

// Placeholders anglais selon le mode
function updatePlaceholders() {
    var mode = document.getElementById('search_mode');
    var input = document.getElementById('unified-search');
    if (mode && input) {
        if (mode.value === 'tags') {
            input.placeholder = 'Search for one or more words in the tags';
        } else {
            input.placeholder = 'Search for one or more words within the notes';
        }
    }
    var modeLeft = document.getElementById('search_mode_left');
    var inputLeft = document.getElementById('unified-search-left');
    if (modeLeft && inputLeft) {
        if (modeLeft.value === 'tags') {
            inputLeft.placeholder = 'Search for one or more words in the tags';
        } else {
            inputLeft.placeholder = 'Search for one or more words within the notes';
        }
    }
}
// Toggle pour desktop
document.getElementById('toggle-search-mode').onclick = function(e) {
    e.preventDefault();
    var mode = document.getElementById('search_mode');
    var icon = document.getElementById('toggle-icon');
    if (mode && icon) {
        if (mode.value === 'notes') {
            mode.value = 'tags';
            icon.classList.remove('fa-file');
            icon.classList.add('fa-tags');
        } else {
            mode.value = 'notes';
            icon.classList.remove('fa-tags');
            icon.classList.add('fa-file');
        }
        updatePlaceholders();
        document.getElementById('unified-search').focus();
    }
};
// Toggle pour mobile
document.getElementById('toggle-search-mode-left').onclick = function(e) {
    e.preventDefault();
    var modeLeft = document.getElementById('search_mode_left');
    var icon = document.getElementById('toggle-icon-left');
    if (modeLeft && icon) {
        if (modeLeft.value === 'notes') {
            modeLeft.value = 'tags';
            icon.classList.remove('fa-file');
            icon.classList.add('fa-tags');
        } else {
            modeLeft.value = 'notes';
            icon.classList.remove('fa-tags');
            icon.classList.add('fa-file');
        }
        updatePlaceholders();
        document.getElementById('unified-search-left').focus();
    }
};
// Soumission du formulaire sur entrée
document.getElementById('unified-search').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('unified-search-form').submit();
    }
});
document.getElementById('unified-search-left').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('unified-search-form-left').submit();
    }
});
// Initialiser le placeholder au chargement
updatePlaceholders();

// Fonction de téléchargement (popup)
function startDownload() {
    document.getElementById('downloadPopup').style.display = 'block';
    window.location = 'exportEntries.php';
    setTimeout(function() {
        document.getElementById('downloadPopup').style.display = 'none';
    }, 4000);
}

// Corrige le comportement du bouton maison mobile
if (document.querySelector('.mobile-menu-bar .btn-menu')) {
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.querySelector('.mobile-menu-bar .btn-menu');
        if(btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                window.location.href = 'index.php';
            };
        }
    });
}
