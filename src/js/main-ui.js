// File extracted from index.php for main UI logic (excluding jQuery plugins)

// English placeholders depending on the mode
function updatePlaceholders() {
    var mode = document.getElementById('search_mode');
    var input = document.getElementById('unified-search');
    if (mode && input) {
        if (mode.value === 'tags') {
            input.placeholder = 'Search for words in the tags';
        } else {
            input.placeholder = 'Search for words within the notes';
        }
    }
    var modeLeft = document.getElementById('search_mode_left');
    var inputLeft = document.getElementById('unified-search-left');
    if (modeLeft && inputLeft) {
        if (modeLeft.value === 'tags') {
            inputLeft.placeholder = 'Search for words in the tags';
        } else {
            inputLeft.placeholder = 'Search for words within the notes';
        }
    }
}
// Toggle for desktop
var toggleDesktop = document.getElementById('toggle-search-mode');
if (toggleDesktop) {
    toggleDesktop.onclick = function(e) {
        e.preventDefault();
        var mode = document.getElementById('search_mode_left');
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
            document.getElementById('unified-search-left').focus();
        }
    };
}
// Toggle for mobile
var toggleMobile = document.getElementById('toggle-search-mode-left');
if (toggleMobile) {
    toggleMobile.onclick = function(e) {
        e.preventDefault();
        var mode = document.getElementById('search_mode');
        var icon = document.getElementById('toggle-icon-left');
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
            var input = document.getElementById('unified-search');
            if (input) input.focus();
        }
    };
}
// Submit form on Enter key (with null checks)
var unifiedSearch = document.getElementById('unified-search');
if (unifiedSearch) {
    unifiedSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            var form = document.getElementById('unified-search-form');
            if (form) form.submit();
        }
    });
}
var unifiedSearchLeft = document.getElementById('unified-search-left');
if (unifiedSearchLeft) {
    unifiedSearchLeft.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            var formLeft = document.getElementById('unified-search-form-left');
            if (formLeft) formLeft.submit();
        }
    });
}
// Initialize placeholder on load
updatePlaceholders();

// Clear search for desktop (left column)
var clearSearchLeft = document.getElementById('clear-search-left');
if (clearSearchLeft) {
    clearSearchLeft.onclick = function(e) {
        e.preventDefault();
        var inputLeft = document.getElementById('unified-search-left');
        if (inputLeft) {
            inputLeft.value = '';
        }
        document.getElementById('unified-search-form-left').submit();
    };
}

// Clear search for mobile
var clearSearchMobile = document.getElementById('clear-search');
if (clearSearchMobile) {
    clearSearchMobile.onclick = function(e) {
        e.preventDefault();
        var input = document.getElementById('unified-search');
        if (input) {
            input.value = '';
        }
        var form = document.getElementById('unified-search-form');
        if (form) form.submit();
    };
}

// Download function (popup)
function startDownload() {
    document.getElementById('downloadPopup').style.display = 'block';
    window.location = 'exportEntries.php';
    setTimeout(function() {
        document.getElementById('downloadPopup').style.display = 'none';
    }, 4000);
}

// Fixes the behavior of the home button on mobile
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
