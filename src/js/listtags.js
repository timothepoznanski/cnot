// JavaScript pour la page des tags

document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la recherche/filtrage des tags
    const searchInput = document.getElementById('tagsSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTags();
        });
    }
});

function filterTags() {
    const input = document.getElementById('tagsSearchInput');
    const filter = input.value.toUpperCase();
    const tagsList = document.getElementById('tagsList');
    const tagItems = tagsList.getElementsByClassName('tag-item');
    
    let visibleCount = 0;
    
    for (let i = 0; i < tagItems.length; i++) {
        const tagName = tagItems[i].querySelector('.tag-name');
        if (tagName) {
            const txtValue = tagName.textContent || tagName.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tagItems[i].style.display = 'block';
                visibleCount++;
            } else {
                tagItems[i].style.display = 'none';
            }
        }
    }
    
    // Mettre à jour le compteur de résultats
    updateSearchResults(visibleCount, filter);
}

function updateSearchResults(count, searchTerm) {
    let resultsDiv = document.getElementById('searchResults');
    if (!resultsDiv) {
        resultsDiv = document.createElement('div');
        resultsDiv.id = 'searchResults';
        resultsDiv.className = 'search-results';
        
        const searchContainer = document.querySelector('.tags-search-form');
        searchContainer.appendChild(resultsDiv);
    }
    
    if (searchTerm.trim() === '') {
        resultsDiv.style.display = 'none';
    } else {
        resultsDiv.style.display = 'block';
        resultsDiv.textContent = `${count} tag(s) found for "${searchTerm}"`;
    }
}
