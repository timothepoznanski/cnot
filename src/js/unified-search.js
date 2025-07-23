// UNIFIED SEARCH FUNCTIONALITY
function clearUnifiedSearch() {
    // Preserve search type preferences by checking current button states
    const notesActive = document.getElementById('search-notes-btn') && document.getElementById('search-notes-btn').classList.contains('active');
    const tagsActive = document.getElementById('search-tags-btn') && document.getElementById('search-tags-btn').classList.contains('active');
    
    // Check mobile buttons if desktop aren't found
    const notesMobileActive = document.getElementById('search-notes-btn-mobile') && document.getElementById('search-notes-btn-mobile').classList.contains('active');
    const tagsMobileActive = document.getElementById('search-tags-btn-mobile') && document.getElementById('search-tags-btn-mobile').classList.contains('active');
    
    // Use desktop state if available, otherwise mobile state
    const preserveNotes = notesActive || notesMobileActive;
    const preserveTags = tagsActive || tagsMobileActive;
    
    // Build URL with preserved preferences
    let url = 'index.php';
    const params = new URLSearchParams();
    
    // Preserve current folder filter if it exists
    const currentFolder = new URLSearchParams(window.location.search).get('folder');
    if (currentFolder) {
        params.set('folder', currentFolder);
    }
    
    // Add search type indicators to preserve button states
    if (preserveNotes) {
        params.set('preserve_notes', '1');
    }
    if (preserveTags) {
        params.set('preserve_tags', '1');
    }
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    window.location.href = url;
}

// Handle unified search form submission
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search state for both desktop and mobile
    initializeSearchButtons(false); // Desktop
    initializeSearchButtons(true);  // Mobile
    
    // Desktop form
    const unifiedForm = document.getElementById('unified-search-form');
    if (unifiedForm) {
        unifiedForm.addEventListener('submit', function(e) {
            handleUnifiedSearchSubmit(e, false);
        });
    }
    
    // Mobile form
    const unifiedFormMobile = document.getElementById('unified-search-form-mobile');
    if (unifiedFormMobile) {
        unifiedFormMobile.addEventListener('submit', function(e) {
            handleUnifiedSearchSubmit(e, true);
        });
    }
});

function initializeSearchButtons(isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const notesBtn = document.getElementById('search-notes-btn' + suffix);
    const tagsBtn = document.getElementById('search-tags-btn' + suffix);
    const searchInput = document.getElementById('unified-search' + suffix);
    const notesHidden = document.getElementById('search-in-notes' + suffix);
    const tagsHidden = document.getElementById('search-in-tags' + suffix);
    
    if (!notesBtn || !tagsBtn || !searchInput) return;
    
    // Check if there are existing search preferences from hidden inputs
    const hasNotesPreference = notesHidden && notesHidden.value === '1';
    const hasTagsPreference = tagsHidden && tagsHidden.value === '1';
    
    // Respect existing search type preferences
    if (hasNotesPreference) {
        notesBtn.classList.add('active');
        tagsBtn.classList.remove('active');
    } else if (hasTagsPreference) {
        tagsBtn.classList.add('active');
        notesBtn.classList.remove('active');
    } else {
        // Default state: activate notes search
        notesBtn.classList.add('active');
        tagsBtn.classList.remove('active');
    }
    
    // Add click handlers
    notesBtn.addEventListener('click', function() {
        toggleSearchType('notes', isMobile);
    });
    
    tagsBtn.addEventListener('click', function() {
        toggleSearchType('tags', isMobile);
    });
    
    // Update placeholder and input state
    updateSearchPlaceholder(isMobile);
}

function toggleSearchType(type, isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const btn = document.getElementById('search-' + type + '-btn' + suffix);
    
    if (!btn) return;
    
    // Determine the other button type
    const otherType = type === 'notes' ? 'tags' : 'notes';
    const otherBtn = document.getElementById('search-' + otherType + '-btn' + suffix);
    
    // If button is already active, activate the other one instead
    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        if (otherBtn) {
            otherBtn.classList.add('active');
        }
    } else {
        // Activate this button and deactivate the other
        btn.classList.add('active');
        if (otherBtn) {
            otherBtn.classList.remove('active');
        }
    }
    
    // Remove error styling
    hideSearchValidationError(isMobile);
    
    // Update search input state and placeholder
    updateSearchPlaceholder(isMobile);
    updateHiddenInputs(isMobile);
}

function handleUnifiedSearchSubmit(e, isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const notesBtn = document.getElementById('search-notes-btn' + suffix);
    const tagsBtn = document.getElementById('search-tags-btn' + suffix);
    const searchInput = document.getElementById('unified-search' + suffix);
    
    if (!notesBtn || !tagsBtn || !searchInput) return;
    
    const searchValue = searchInput.value.trim();
    
    // If no search value, clear search
    if (!searchValue) {
        e.preventDefault();
        clearUnifiedSearch();
        return;
    }
    
    // Check button states (there should always be exactly one active)
    const hasNotesActive = notesBtn.classList.contains('active');
    const hasTagsActive = tagsBtn.classList.contains('active');
    
    // Safety check: ensure exactly one is active (shouldn't happen with new logic)
    if (hasNotesActive && hasTagsActive) {
        e.preventDefault();
        // Reset to notes only as default
        notesBtn.classList.add('active');
        tagsBtn.classList.remove('active');
        updateSearchPlaceholder(isMobile);
        updateHiddenInputs(isMobile);
        return;
    }
    
    // Another safety check: ensure at least one is active
    if (!hasNotesActive && !hasTagsActive) {
        e.preventDefault();
        // Activate notes as default
        notesBtn.classList.add('active');
        updateSearchPlaceholder(isMobile);
        updateHiddenInputs(isMobile);
        return;
    }
    
    // Remove any existing validation error
    hideSearchValidationError(isMobile);
    
    // Update hidden inputs before form submission
    updateHiddenInputs(isMobile);
}

function showSearchValidationError(isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const container = document.querySelector(isMobile ? '.unified-search-container.mobile' : '.unified-search-container');
    const notesBtn = document.getElementById('search-notes-btn' + suffix);
    const tagsBtn = document.getElementById('search-tags-btn' + suffix);
    
    // Remove existing error message
    hideSearchValidationError(isMobile);
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'search-validation-error';
    errorDiv.textContent = 'Please select at least one search option (Notes or Tags)';
    
    // Insert error message after search bar
    const searchBar = container.querySelector('.searchbar-row');
    searchBar.parentNode.insertBefore(errorDiv, searchBar.nextSibling);
    
    // Add error styling to buttons
    notesBtn.classList.add('search-type-btn-error');
    tagsBtn.classList.add('search-type-btn-error');
    
    // Auto-hide error after 3 seconds
    setTimeout(() => hideSearchValidationError(isMobile), 3000);
}

function hideSearchValidationError(isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const container = document.querySelector(isMobile ? '.unified-search-container.mobile' : '.unified-search-container');
    const notesBtn = document.getElementById('search-notes-btn' + suffix);
    const tagsBtn = document.getElementById('search-tags-btn' + suffix);
    
    // Remove error message
    const errorMessage = container.querySelector('.search-validation-error');
    if (errorMessage) {
        errorMessage.remove();
    }
    
    // Remove error styling from buttons
    if (notesBtn) notesBtn.classList.remove('search-type-btn-error');
    if (tagsBtn) tagsBtn.classList.remove('search-type-btn-error');
}

function updateSearchPlaceholder(isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const notesBtn = document.getElementById('search-notes-btn' + suffix);
    const tagsBtn = document.getElementById('search-tags-btn' + suffix);
    const searchInput = document.getElementById('unified-search' + suffix);
    
    if (!notesBtn || !tagsBtn || !searchInput) return;
    
    const hasNotesActive = notesBtn.classList.contains('active');
    const hasTagsActive = tagsBtn.classList.contains('active');
    
    let placeholder = 'Search in notes...'; // Default placeholder
    
    if (hasNotesActive) {
        placeholder = 'Search in notes...';
    } else if (hasTagsActive) {
        placeholder = 'Search in tags...';
    }
    
    searchInput.placeholder = placeholder;
    
    // Always enable search input since there's always a selection
    searchInput.disabled = false;
    searchInput.style.opacity = '1';
}

function updateHiddenInputs(isMobile) {
    const suffix = isMobile ? '-mobile' : '';
    const notesBtn = document.getElementById('search-notes-btn' + suffix);
    const tagsBtn = document.getElementById('search-tags-btn' + suffix);
    const searchInput = document.getElementById('unified-search' + suffix);
    const notesHidden = document.getElementById('search-notes-hidden' + suffix);
    const tagsHidden = document.getElementById('search-tags-hidden' + suffix);
    const notesCheckHidden = document.getElementById('search-in-notes' + suffix);
    const tagsCheckHidden = document.getElementById('search-in-tags' + suffix);
    
    if (!notesBtn || !tagsBtn || !searchInput || !notesHidden || !tagsHidden) return;
    
    const searchValue = searchInput.value.trim();
    const hasNotesActive = notesBtn.classList.contains('active');
    const hasTagsActive = tagsBtn.classList.contains('active');
    
    // Update hidden inputs based on button states
    if (hasNotesActive) {
        notesHidden.value = searchValue;
        if (notesCheckHidden) notesCheckHidden.value = '1';
    } else {
        notesHidden.value = '';
        if (notesCheckHidden) notesCheckHidden.value = '';
    }
    
    if (hasTagsActive) {
        tagsHidden.value = searchValue;
        if (tagsCheckHidden) tagsCheckHidden.value = '1';
    } else {
        tagsHidden.value = '';
        if (tagsCheckHidden) tagsCheckHidden.value = '';
    }
}
