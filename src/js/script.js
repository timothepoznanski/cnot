// Download function (popup)
function startDownload() {
    window.location = 'exportEntries.php';
}

var editedButNotSaved = 0;  // Flag indicating that the note has been edited set to 1
var lastudpdate;
var noteid=-1;
var updateNoteEnCours = 0;
var selectedFolder = 'Uncategorized'; // Track currently selected folder
var currentNoteIdForAttachments = null; // Track current note for attachments

// Function to toggle the note settings dropdown menu
function toggleNoteMenu(noteId) {
    const menu = document.getElementById('note-menu-' + noteId);
    const button = document.getElementById('settings-btn-' + noteId);
    
    // Close all other open menus
    document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
        if (otherMenu.id !== 'note-menu-' + noteId) {
            otherMenu.style.display = 'none';
        }
    });
    
    // Toggle current menu
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
        button.classList.add('active');
        
        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!menu.contains(e.target) && !button.contains(e.target)) {
                    menu.style.display = 'none';
                    button.classList.remove('active');
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 100);
    } else {
        menu.style.display = 'none';
        button.classList.remove('active');
    }
}

// Function to show note information in a better formatted way
function showNoteInfo(noteId, created, updated) {
    const createdDate = new Date(created).toLocaleString();
    const updatedDate = new Date(updated).toLocaleString();
    const message = `Note ID: ${noteId}\nCréée le: ${createdDate}\nDernière modification: ${updatedDate}`;
    alert(message);
}

// Function to toggle the vertical toolbar menu (legacy - keeping for compatibility)
function toggleToolbarMenu(noteId) {
    const menu = document.getElementById('toolbarMenu' + noteId);
    const settingsBtn = document.querySelector('.btn-settings');
    
    if (menu && (menu.style.display === 'none' || menu.style.display === '')) {
        // Close any other open menus first
        document.querySelectorAll('.toolbar-vertical-menu').forEach(otherMenu => {
            otherMenu.style.display = 'none';
        });
        document.querySelectorAll('.btn-settings').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Open this menu
        menu.style.display = 'block';
        settingsBtn.classList.add('active');
        
        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!menu.contains(e.target) && !settingsBtn.contains(e.target)) {
                    menu.style.display = 'none';
                    settingsBtn.classList.remove('active');
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 100);
    } else if (menu) {
        // Close menu
        menu.style.display = 'none';
        settingsBtn.classList.remove('active');
    }
}

// Add attachment functionality
function showAttachmentDialog(noteId) {
    currentNoteIdForAttachments = noteId;
    document.getElementById('attachmentModal').style.display = 'block';
    loadAttachments(noteId);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function uploadAttachment() {
    const fileInput = document.getElementById('attachmentFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file');
        return;
    }
    
    if (!currentNoteIdForAttachments) {
        alert('No note selected');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('note_id', currentNoteIdForAttachments);
    formData.append('file', file);
    
    fetch('api_attachments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fileInput.value = ''; // Clear input
            loadAttachments(currentNoteIdForAttachments); // Reload list
            updateAttachmentCountInMenu(currentNoteIdForAttachments); // Update count in menu
        } else {
            alert('Upload failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload failed');
    });
}

function loadAttachments(noteId) {
    fetch(`api_attachments.php?action=list&note_id=${noteId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAttachments(data.attachments);
        } else {
            console.error('Failed to load attachments:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function displayAttachments(attachments) {
    const container = document.getElementById('attachmentsList');
    
    if (attachments.length === 0) {
        container.innerHTML = '<p>No attachments</p>';
        return;
    }
    
    let html = '';
    attachments.forEach(attachment => {
        const fileSize = formatFileSize(attachment.file_size);
        const uploadDate = new Date(attachment.uploaded_at).toLocaleDateString();
        
        html += `
            <div class="attachment-item">
                <div class="attachment-info">
                    <strong>${attachment.original_filename}</strong>
                    <br>
                    <small>${fileSize} - ${uploadDate}</small>
                </div>
                <div class="attachment-actions">
                    <button onclick="downloadAttachment('${attachment.id}')" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button onclick="deleteAttachment('${attachment.id}')" title="Delete" class="delete-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function downloadAttachment(attachmentId) {
    if (!currentNoteIdForAttachments) {
        alert('No note selected');
        return;
    }
    window.open(`api_attachments.php?action=download&note_id=${currentNoteIdForAttachments}&attachment_id=${attachmentId}`, '_blank');
}

function deleteAttachment(attachmentId) {
    if (!currentNoteIdForAttachments) {
        alert('No note selected');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('note_id', currentNoteIdForAttachments);
    formData.append('attachment_id', attachmentId);
    
    fetch('api_attachments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadAttachments(currentNoteIdForAttachments); // Reload list
            updateAttachmentCountInMenu(currentNoteIdForAttachments); // Update count in menu
        } else {
            alert('Delete failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Delete failed');
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Function to update attachment count in dropdown menu
function updateAttachmentCountInMenu(noteId) {
    fetch(`api_attachments.php?action=list&note_id=${noteId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const count = data.attachments.length;
            const hasAttachments = count > 0;
            
            // Update dropdown menu count
            const menu = document.getElementById('note-menu-' + noteId);
            if (menu) {
                // Find the attachments menu item and update its text and class
                const attachmentItems = menu.querySelectorAll('.dropdown-item');
                attachmentItems.forEach(item => {
                    if (item.innerHTML.includes('fa-paperclip')) {
                        const icon = item.querySelector('i.fas.fa-paperclip');
                        if (icon) {
                            item.innerHTML = `<i class="fas fa-paperclip"></i> Attachments (${count})`;
                            // Re-add the onclick handler as innerHTML replaces it
                            item.onclick = function() { showAttachmentDialog(noteId); };
                            // Update has-attachments class
                            if (hasAttachments) {
                                item.classList.add('has-attachments');
                            } else {
                                item.classList.remove('has-attachments');
                            }
                        }
                    }
                });
            }
            
            // Update settings button (gear icon) class
            const settingsBtn = document.getElementById('settings-btn-' + noteId);
            if (settingsBtn) {
                if (hasAttachments) {
                    settingsBtn.classList.add('has-attachments');
                } else {
                    settingsBtn.classList.remove('has-attachments');
                }
            }
            
            // Update attachment button class (mobile)
            const attachmentBtns = document.querySelectorAll('.btn-attachment[onclick*="' + noteId + '"]');
            attachmentBtns.forEach(btn => {
                if (hasAttachments) {
                    btn.classList.add('has-attachments');
                } else {
                    btn.classList.remove('has-attachments');
                }
            });
        }
    })
    .catch(error => {
        console.error('Error updating attachment count:', error);
    });
}

// Make links clickable in contenteditable areas
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        // Check if clicked element is a link inside a contenteditable area
        if (e.target.tagName === 'A' && e.target.closest('[contenteditable="true"]')) {
            e.preventDefault();
            e.stopPropagation();
            
            // Open link directly in new tab
            window.open(e.target.href, '_blank');
        }
    });
});

// Function to toggle favorite status
function toggleFavorite(noteId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'api_favorites.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Update the star icon
                    const starIcon = document.querySelector(`button[onclick*="toggleFavorite('${noteId}')"] i`);
                    if (starIcon) {
                        starIcon.style.color = '#007DB8'; // Always blue
                        
                        // Simple logic: full star if favorite, empty star if not favorite
                        if (response.is_favorite) {
                            starIcon.classList.remove('far');
                            starIcon.classList.add('fas'); // Full star for favorite
                        } else {
                            starIcon.classList.remove('fas');
                            starIcon.classList.add('far'); // Empty star for non-favorite
                        }
                    }
                    
                    // Refresh to update favorites folder (no notification)
                    window.location.reload();
                } else {
                    showNotificationPopup('Erreur: ' + response.message);
                }
            } catch (e) {
                showNotificationPopup('Erreur lors de la mise à jour des favoris');
            }
        }
    };
    
    xhr.send('action=toggle_favorite&note_id=' + encodeURIComponent(noteId));
}

function updateidsearch(el)
{
    noteid = el.id.substr(5);
}

function updateidhead(el)
{
    noteid = el.id.substr(3); // 3 stands for inp
}

function updateidtags(el)
{
    noteid = el.id.substr(4);
}

function updateidfolder(el)
{
    noteid = el.id.substr(6); // 6 stands for folder
}

function updateident(el)
{
    noteid = el.id.substr(5);
}


function updatenote(){
    updateNoteEnCours = 1;
    var headi = document.getElementById("inp"+noteid).value;
    var entryElem = document.getElementById("entry"+noteid);
    var ent = entryElem ? entryElem.innerHTML : "";
    // console.log("RESULT :" + ent);
    ent = ent.replace(/<br\s*[\/]?>/gi, "&nbsp;<br>");
    var entcontent = entryElem ? entryElem.textContent : "";
    // console.log("entcontent:" + entcontent);
    // console.log("ent:" + ent);
    var tags = document.getElementById("tags"+noteid).value;
    var folderElem = document.getElementById("folder"+noteid);
    var folder = folderElem ? folderElem.value : 'Uncategorized';

    var params = new URLSearchParams({
        id: noteid,
        tags: tags,
        folder: folder,
        heading: headi,
        entry: ent,
        entrycontent: entcontent,
        now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60
    });
    fetch("updatenote.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.text())
    .then(function(data) {
        if(data=='1') {
            editedButNotSaved = 0;
            var lastUpdatedElem = document.getElementById('lastupdated'+noteid);
            if (lastUpdatedElem) lastUpdatedElem.innerHTML = 'Last Saved Today';
        } else {
            editedButNotSaved = 0;
            var lastUpdatedElem = document.getElementById('lastupdated'+noteid);
            if (lastUpdatedElem) lastUpdatedElem.innerHTML = data;
        }
        updateNoteEnCours = 0;
        setSaveButtonRed(false);
    });
    var newNotesElem = document.getElementById('newnotes');
    if (newNotesElem) {
        newNotesElem.style.display = 'none';
        // Force reflow and show again
        void newNotesElem.offsetWidth;
        newNotesElem.style.display = '';
    }
}

function newnote(){
    var params = new URLSearchParams({
        now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60,
        folder: selectedFolder
    });
    fetch("insertnew.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.text())
    .then(function(data) {
        try {
            var res = typeof data === 'string' ? JSON.parse(data) : data;
            if(res.status === 1) {
                window.scrollTo(0, 0);
                window.location.href = "index.php?note=" + encodeURIComponent(res.heading);
            } else {
                alert(res.error || data);
            }
        } catch(e) {
            alert('Erreur lors de la création de la note: ' + data);
        }
    });
}

function deleteNote(iid){
    var params = new URLSearchParams({ id: iid });
    fetch("deletenote.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.text())
    .then(function(data) {
        if(data=='1') {
            window.location.href = "index.php";
        } else {
            alert(data);
        }
    });
}


// The functions below trigger the `update()` function when a note is modified. This simply sets a flag indicating that the note has been modified, but it does not save the changes.


// Déclenche update() sur keyup, input, et paste dans .noteentry
['keyup', 'input', 'paste'].forEach(eventType => {
  document.body.addEventListener(eventType, function(e) {
    if (e.target.classList.contains('name_doss')) {
      if(updateNoteEnCours==1){
        showNotificationPopup("Save in progress.");
      } else {
        update();
      }
    } else if (e.target.classList.contains('noteentry')) {
      if(updateNoteEnCours==1){
        showNotificationPopup("Automatic save in progress, please do not modify the note.");
      } else {
        update();
      }
    } else if (e.target.tagName === 'INPUT') {
      // Ne déclenche update() que pour les inputs de note
      if (
        e.target.classList.contains('searchbar') ||
        e.target.id === 'search' ||
        e.target.classList.contains('searchtrash') ||
        e.target.id === 'myInputFiltrerTags'
      ) {
        return;
      }
      // On déclenche update() pour les champs de note : titre et tags
      if (
        e.target.classList.contains('css-title') ||
        (e.target.id && e.target.id.startsWith('inp')) ||
        (e.target.id && e.target.id.startsWith('tags'))
      ) {
        if(updateNoteEnCours==1){
          showNotificationPopup("Automatic save in progress, please do not modify the note.");
        } else {
          update();
        }
      }
    }
  });
});

// Réinitialise noteid quand la barre de recherche reçoit le focus
document.body.addEventListener('focusin', function(e) {
  if (e.target.classList.contains('searchbar') || e.target.id === 'search' || e.target.classList.contains('searchtrash')) {
    noteid = -1;
  }
});

function update(){
    if(noteid=='search' || noteid==-1) return;
    editedButNotSaved = 1;  // We set the flag to indicate that the note has been modified.
    var curdate = new Date();
    var curtime = curdate.getTime();
    lastudpdate = curtime;
    displayEditInProgress(); // We display that an action has been taken to edit the note.
}

function displaySavingInProgress(){
    var elem = document.getElementById('lastupdated'+noteid);
    if (elem) elem.innerHTML = '<span style="color:#FF0000";>Saving in progress...</span>';
    setSaveButtonRed(true);
}

function displayModificationsDone(){
    var elem = document.getElementById('lastupdated'+noteid);
    if (elem) elem.innerHTML = '<span style="color:#FF0000";>Note modified</span>';
    setSaveButtonRed(true);
}

function displayEditInProgress(){
    var elem = document.getElementById('lastupdated'+noteid);
    if (elem) elem.innerHTML = '<span>Editing in progress...</span>';
    setSaveButtonRed(true);
}

// Met à jour la couleur du bouton sauvegarder
function setSaveButtonRed(isRed) {
    // On prend le premier bouton .toolbar-btn qui contient .fa-save
    var saveBtn = document.querySelector('.toolbar-btn > .fa-save')?.parentElement;
    if (!saveBtn) {
        // fallback: bouton avec icône save
        var btns = document.querySelectorAll('.toolbar-btn');
        btns.forEach(function(btn){
            if(btn.querySelector && btn.querySelector('.fa-save')) saveBtn = btn;
        });
    }
    if (saveBtn) {
        if (isRed) {
            saveBtn.classList.add('save-modified');
        } else {
            saveBtn.classList.remove('save-modified');
        }
    }
}

// Every X seconds (5000 = 5s), we call the `checkedit()` function and display "Note modified" if there have been changes, or "Saving in progress..." if the note is being saved.
document.addEventListener('DOMContentLoaded', function() {
    if(editedButNotSaved==0){
        setInterval(function(){
            checkedit();
            if(editedButNotSaved==1){displayModificationsDone();}
            if(updateNoteEnCours==1){displaySavingInProgress();}
        }, 2000);
    }

    // Warn user if note is modified but not saved when leaving the page
    window.addEventListener('beforeunload', function (e) {
        // Only warn if a note is selected and not in search mode
        if (
            editedButNotSaved === 1 &&
            updateNoteEnCours === 0 &&
            noteid !== -1 &&
            noteid !== 'search'
        ) {
            var confirmationMessage = 'You have unsaved changes in your note. Are you sure you want to leave without saving?';
            (e || window.event).returnValue = confirmationMessage; // For old browsers
            return confirmationMessage; // For modern browsers
        }
    });
});

function checkedit(){
    if(noteid==-1) return ;
    var curdate = new Date();
    var curtime = curdate.getTime();
    // If there has been a modification and more than X seconds (1000 = 1s) have passed, and the note is not currently being saved (update), then update the note in the database and the HTML file.
    //if(editedButNotSaved==1 && curtime-lastudpdate > 5000)  // If we don't control the `updateNoteEnCours` flag, it will create excessive requests if the network is slow.
    if(updateNoteEnCours==0 && editedButNotSaved==1 && curtime-lastudpdate > 15000)
    {
        displaySavingInProgress();
        updatenote();
    }
    else{
        //alert("test");
    }
}

function saveFocusedNoteJS(){
    //console.log("noteid = " + noteid);
    //if(noteid==-1) return ;
    if(noteid == -1){
        showNotificationPopup("Click anywhere in the note to be saved, then try again.");
        return ;
    }
    //console.log("updateNoteEnCours = " + editedButNotSaved / "editedButNotSaved = " + editedButNotSaved);
    if(updateNoteEnCours==0 && editedButNotSaved==1)
    {
        displaySavingInProgress();
        updatenote();
    }
    else{
        if(updateNoteEnCours==1){
            showNotificationPopup("Save already in progress.");
        }
        else{
            if(editedButNotSaved==0){showNotificationPopup("Nothing to save.");}
        }
    }
}

function showNotificationPopup(message) {
    var popup = document.getElementById('notificationPopup');
    popup.innerText = message;
    popup.style.display = 'block';

    // Hide the popup after a certain amount of time
    setTimeout(function() {
        popup.style.display = 'none';
    }, 4000); // Hide after 4 seconds.
}

// Folder management functions
function newFolder() {
    document.getElementById('newFolderModal').style.display = 'block';
    document.getElementById('newFolderName').focus();
}

function createFolder() {
    var folderName = document.getElementById('newFolderName').value.trim();
    if (!folderName) {
        alert('Please enter a folder name');
        return;
    }
    
    var params = new URLSearchParams({
        action: 'create',
        folder_name: folderName
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        console.log('Create folder response:', data); // Debug
        if (data.success) {
            closeModal('newFolderModal');
            document.getElementById('newFolderName').value = ''; // Clear input
            showNotificationPopup('Folder "' + folderName + '" created successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error creating folder:', error);
        alert('Error creating folder: ' + error);
    });
}

function toggleFolder(folderId) {
    var content = document.getElementById(folderId);
    var icon = document.querySelector(`[data-folder-id="${folderId}"] .folder-icon`);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
        localStorage.setItem('folder_' + folderId, 'open');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
        localStorage.setItem('folder_' + folderId, 'closed');
    }
}

function selectFolder(folderName, element) {
    // Remove previous selection
    document.querySelectorAll('.folder-header').forEach(el => el.classList.remove('selected-folder'));
    
    // Add selection to clicked folder
    element.classList.add('selected-folder');
    
    // Update selected folder
    selectedFolder = folderName;
    
    console.log('Selected folder:', selectedFolder);
}

function editFolderName(oldName) {
    document.getElementById('editFolderModal').style.display = 'block';
    document.getElementById('editFolderName').value = oldName;
    document.getElementById('editFolderName').dataset.oldName = oldName;
    document.getElementById('editFolderName').focus();
}

function saveFolderName() {
    var newName = document.getElementById('editFolderName').value.trim();
    var oldName = document.getElementById('editFolderName').dataset.oldName;
    
    if (!newName) {
        alert('Please enter a folder name');
        return;
    }
    
    if (newName === oldName) {
        closeModal('editFolderModal');
        return;
    }
    
    var params = new URLSearchParams({
        action: 'rename',
        old_name: oldName,
        new_name: newName
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            closeModal('editFolderModal');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error renaming folder: ' + error);
    });
}

function deleteFolder(folderName) {
    // First, check how many notes are in this folder
    var params = new URLSearchParams({
        action: 'count_notes_in_folder',
        folder_name: folderName
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            var noteCount = data.count || 0;
            
            // If folder is empty, delete without confirmation
            if (noteCount === 0) {
                // Proceed directly with deletion for empty folders
                var deleteParams = new URLSearchParams({
                    action: 'delete',
                    folder_name: folderName
                });
                
                fetch("folder_operations.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: deleteParams.toString()
                })
                .then(response => response.json())
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error deleting folder: ' + error);
                });
                return;
            }
            
            // For folders with notes, ask for confirmation
            var confirmMessage = `Are you sure you want to delete the folder "${folderName}"? \n${noteCount} note${noteCount > 1 ? 's' : ''} will be moved to "Uncategorized".\n\nIf you want to delete all the notes of this fold instead, you can move them to "Uncategorized" folder then empty it.`;
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            // Proceed with deletion
            var deleteParams = new URLSearchParams({
                action: 'delete',
                folder_name: folderName
            });
            
            fetch("folder_operations.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: deleteParams.toString()
            })
            .then(response => response.json())
            .then(function(data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error deleting folder: ' + error);
            });
        } else {
            alert('Error checking folder contents: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error checking folder contents: ' + error);
    });
}

function emptyFolder(folderName) {
    if (!confirm(`Are you sure you want to move all notes from "${folderName}" to trash?`)) {
        return;
    }
    
    var params = new URLSearchParams({
        action: 'empty_folder',
        folder_name: folderName
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            location.reload();
            showNotificationPopup(`All notes moved to trash from folder: ${folderName}`);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error emptying folder: ' + error);
    });
}

function toggleNewFolderInput() {
    const select = document.getElementById('moveNoteFolderSelect');
    const container = document.getElementById('newFolderInputContainer');
    const input = document.getElementById('moveNewFolderName');
    
    if (select.value === '__create_new__') {
        container.style.display = 'block';
        input.focus();
    } else {
        container.style.display = 'none';
        input.value = '';
    }
}

function showMoveFolderDialog(noteId) {
    noteid = noteId; // Set the current note ID
    
    // Load folders
    var params = new URLSearchParams({
        action: 'get_folders'
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            var select = document.getElementById('moveNoteFolderSelect');
            select.innerHTML = '';
            
            // Get current folder of the note
            var currentFolder = document.getElementById('folder' + noteId).value;
            
            data.folders.forEach(function(folder) {
                var option = document.createElement('option');
                option.value = folder;
                option.textContent = folder;
                if (folder === currentFolder) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Add "Create New Folder" option
            var createOption = document.createElement('option');
            createOption.value = '__create_new__';
            createOption.textContent = '+ Create New Folder';
            select.appendChild(createOption);
            
            // Reset new folder input
            document.getElementById('newFolderInputContainer').style.display = 'none';
            document.getElementById('moveNewFolderName').value = '';
            
            document.getElementById('moveNoteFolderModal').style.display = 'block';
        }
    });
}

function moveCurrentNoteToFolder() {
    var selectedFolder = document.getElementById('moveNoteFolderSelect').value;
    var folderToMoveTo;
    
    if (selectedFolder === '__create_new__') {
        var newFolderName = document.getElementById('moveNewFolderName').value.trim();
        if (!newFolderName) {
            alert('Please enter a folder name');
            return;
        }
        folderToMoveTo = newFolderName;
    } else {
        folderToMoveTo = selectedFolder;
    }
    
    var currentNoteHeading = document.getElementById('inp' + noteid).value; // Get the heading of the specific note
    
    var params = new URLSearchParams({
        action: 'move_note',
        note_heading: currentNoteHeading,
        folder: folderToMoveTo
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            closeModal('moveNoteFolderModal');
            // Reload the page to show the note in the new folder
            location.reload();
        } else {
            alert('Error: ' + data.error);
            closeModal('moveNoteFolderModal');
        }
    })
    .catch(error => {
        alert('Error moving note: ' + error);
        closeModal('moveNoteFolderModal');
    });
}

function showMoveNoteDialog(noteHeading) {
    event.preventDefault();
    event.stopPropagation();
    
    document.getElementById('moveNoteTitle').textContent = noteHeading;
    document.getElementById('moveNoteModal').dataset.noteHeading = noteHeading;
    
    // Load folders
    var params = new URLSearchParams({
        action: 'get_folders'
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            var select = document.getElementById('moveNoteFolder');
            select.innerHTML = '';
            data.folders.forEach(function(folder) {
                var option = document.createElement('option');
                option.value = folder;
                option.textContent = folder;
                select.appendChild(option);
            });
            document.getElementById('moveNoteModal').style.display = 'block';
        }
    });
}

function moveNoteToFolder() {
    var noteHeading = document.getElementById('moveNoteModal').dataset.noteHeading;
    var targetFolder = document.getElementById('moveNoteFolder').value;
    
    var params = new URLSearchParams({
        action: 'move_note',
        note_heading: noteHeading,
        target_folder: targetFolder
    });
    
    fetch("folder_operations.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: params.toString()
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.success) {
            closeModal('moveNoteModal');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error moving note: ' + error);
    });
}

function updateNoteFolder(noteId) {
    // This will be handled by the regular update mechanism
    update();
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    var modals = document.getElementsByClassName('modal');
    for (var i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = 'none';
        }
    }
}

// Restore folder states on page load
document.addEventListener('DOMContentLoaded', function() {
    var folderToggles = document.querySelectorAll('.folder-toggle');
    folderToggles.forEach(function(toggle) {
        var folderId = toggle.dataset.folderId;
        
        // Don't restore localStorage state if in search mode or if a note is selected
        // The PHP already set the correct initial state
        if (!window.isSearchMode && !window.currentNoteFolder) {
            var state = localStorage.getItem('folder_' + folderId);
            if (state === 'closed') {
                var content = document.getElementById(folderId);
                var icon = toggle.querySelector('.folder-icon');
                if (content && icon) {
                    content.style.display = 'none';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-right');
                }
            }
        }
    });
});

// Settings menu functions
function toggleSettingsMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('settingsMenu');
    
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
        
        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeSettingsMenu(e) {
                if (!menu.contains(e.target)) {
                    menu.style.display = 'none';
                    document.removeEventListener('click', closeSettingsMenu);
                }
            });
        }, 100);
    } else {
        menu.style.display = 'none';
    }
}

function foldAllFolders() {
    const folderContents = document.querySelectorAll('.folder-content');
    const folderIcons = document.querySelectorAll('.folder-icon');
    
    folderContents.forEach(content => {
        content.style.display = 'none';
    });
    
    folderIcons.forEach(icon => {
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    });
    
    // Close settings menu
    document.getElementById('settingsMenu').style.display = 'none';
}

function unfoldAllFolders() {
    const folderContents = document.querySelectorAll('.folder-content');
    const folderIcons = document.querySelectorAll('.folder-icon');
    
    folderContents.forEach(content => {
        content.style.display = 'block';
    });
    
    folderIcons.forEach(icon => {
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    });
    
    // Close settings menu
    document.getElementById('settingsMenu').style.display = 'none';
}

// Function to download a file
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Function to add copy buttons to code blocks
function addCopyButtonsToCodeBlocks() {
    const codeBlocks = document.querySelectorAll('.code-block, pre.code-block, .noteentry pre');
    console.log('Found code blocks:', codeBlocks.length); // Debug log
    
    codeBlocks.forEach(codeBlock => {
        // Check if copy button already exists
        if (codeBlock.querySelector('.copy-code-btn')) {
            return;
        }
        
        console.log('Adding copy button to code block'); // Debug log
        
        // Create copy button
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-code-btn';
        copyBtn.innerHTML = 'Copy';
        copyBtn.title = 'Copy code';
        
        // Add click event
        copyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Copy button event triggered'); // Debug log
            copyCodeToClipboard(codeBlock, copyBtn);
        });
        
        // Add button to code block
        codeBlock.style.position = 'relative';
        codeBlock.appendChild(copyBtn);
    });
}

// Function to copy code to clipboard
function copyCodeToClipboard(codeBlock, button) {
    console.log('Copy button clicked'); // Debug log
    
    // Clone the code block and remove the copy button from the clone
    const clonedBlock = codeBlock.cloneNode(true);
    const copyBtn = clonedBlock.querySelector('.copy-code-btn');
    if (copyBtn) {
        copyBtn.remove();
    }
    
    const text = clonedBlock.textContent || clonedBlock.innerText;
    console.log('Text to copy:', text); // Debug log
    
    // Use the modern Clipboard API first, then fallback
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('Clipboard API success'); // Debug log
            showCopySuccess(button);
        }).catch((err) => {
            console.log('Clipboard API failed, using fallback:', err); // Debug log
            fallbackCopyToClipboard(text, button);
        });
    } else {
        console.log('Using fallback copy method directly'); // Debug log
        fallbackCopyToClipboard(text, button);
    }
}

// Fallback copy method for older browsers
function fallbackCopyToClipboard(text, button) {
    console.log('Using fallback copy method'); // Debug log
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-999999px';
    textarea.style.top = '-999999px';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    
    try {
        const successful = document.execCommand('copy');
        console.log('Copy command successful:', successful); // Debug log
        if (successful) {
            showCopySuccess(button);
        } else {
            showCopyError(button);
        }
    } catch (err) {
        console.error('Failed to copy text: ', err);
        showCopyError(button);
    }
    
    document.body.removeChild(textarea);
}

// Show success feedback
function showCopySuccess(button) {
    const originalContent = button.innerHTML;
    button.innerHTML = 'Copied!';
    button.classList.add('copied');
    
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.classList.remove('copied');
    }, 2000);
}

// Show error feedback
function showCopyError(button) {
    const originalContent = button.innerHTML;
    button.innerHTML = 'Error';
    button.style.backgroundColor = '#dc3545';
    button.style.color = 'white';
    
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.style.backgroundColor = '';
        button.style.color = '';
    }, 2000);
}

// Initialize copy buttons when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - initializing copy buttons'); // Debug log
    addCopyButtonsToCodeBlocks();
    
    // Also try after a short delay to catch any dynamically loaded content
    setTimeout(() => {
        console.log('Delayed copy button initialization'); // Debug log
        addCopyButtonsToCodeBlocks();
    }, 1000);
    
    // Also add copy buttons when content changes (using MutationObserver)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is a code block or contains code blocks
                        if (node.matches && (node.matches('.code-block') || node.matches('pre.code-block') || node.matches('.noteentry pre'))) {
                            addCopyButtonsToCodeBlocks();
                        } else if (node.querySelectorAll) {
                            const codeBlocks = node.querySelectorAll('.code-block, pre.code-block, .noteentry pre');
                            if (codeBlocks.length > 0) {
                                addCopyButtonsToCodeBlocks();
                            }
                        }
                    }
                });
            }
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// Also try to initialize immediately in case DOM is already loaded
if (document.readyState === 'loading') {
    // DOM is still loading
} else {
    // DOM is already loaded
    console.log('DOM already loaded - initializing copy buttons immediately');
    addCopyButtonsToCodeBlocks();
}

// Global function for manual testing
window.testCopyButtons = function() {
    console.log('Manual test - adding copy buttons');
    addCopyButtonsToCodeBlocks();
};

// Global function to test copy functionality directly
window.testCopyFunction = function(text) {
    if (!text) text = "Test copy functionality";
    console.log('Testing copy with text:', text);
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('Copy test successful');
            alert('Copy test successful!');
        }).catch((err) => {
            console.log('Copy test failed:', err);
            alert('Copy test failed: ' + err);
        });
    } else {
        console.log('Clipboard API not available');
        alert('Clipboard API not available');
    }
};