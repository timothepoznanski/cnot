// Welcome page functions
function createFirstNote() {
    // Redirect to create new note page or trigger new note creation
    window.location.href = 'insertnew.php';
}

function showWelcomeFeatures() {
    const featuresDiv = document.getElementById('welcome-features');
    const button = event.target.closest('.welcome-btn-secondary');
    
    if (featuresDiv.style.display === 'none' || featuresDiv.style.display === '') {
        featuresDiv.style.display = 'block';
        button.innerHTML = '<i class="fas fa-times"></i> Masquer les fonctionnalités';
        
        // Smooth scroll to features
        setTimeout(() => {
            featuresDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    } else {
        featuresDiv.style.display = 'none';
        button.innerHTML = '<i class="fas fa-lightbulb"></i> Découvrir les fonctionnalités';
    }
}
