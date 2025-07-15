// --- Code déplacé depuis main-ui.js ---
// English placeholders depending on the mode
function updatePlaceholders() {
    var mode = document.getElementById('search_mode');
    var input = document.getElementById('unified-search');
    if (mode && input) {
        // Set default to tags if not set
        if (!mode.value || mode.value === '') mode.value = 'tags';
        if (mode.value === 'tags') {
            input.placeholder = 'Search for words in the tags';
        } else {
            input.placeholder = 'Search for words within the notes';
        }
    }
    var modeLeft = document.getElementById('search_mode_left');
    var inputLeft = document.getElementById('unified-search-left');
    if (modeLeft && inputLeft) {
        // Set default to tags if not set
        if (!modeLeft.value || modeLeft.value === '') modeLeft.value = 'tags';
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
// Force default search mode to 'tags' on load
document.addEventListener('DOMContentLoaded', function() {
    // On ne force le mode 'tags' que si l'utilisateur arrive sur la page d'accueil sans paramètre de recherche
    var url = window.location.href;
    var hasQuery = url.indexOf('?') !== -1;
    var mode = document.getElementById('search_mode');
    var modeLeft = document.getElementById('search_mode_left');
    if (!hasQuery) {
        if (mode && (!mode.value || mode.value === '')) mode.value = 'tags';
        if (modeLeft && (!modeLeft.value || modeLeft.value === '')) modeLeft.value = 'tags';
    }
    // Synchronise l'icône desktop
    var iconDesktop = document.getElementById('toggle-icon');
    if (iconDesktop && modeLeft) {
        if (modeLeft.value === 'tags') {
            iconDesktop.classList.remove('fa-file');
            iconDesktop.classList.add('fa-tags');
        } else {
            iconDesktop.classList.remove('fa-tags');
            iconDesktop.classList.add('fa-file');
        }
    }
    // Synchronise l'icône mobile
    var iconMobile = document.getElementById('toggle-icon-left');
    if (iconMobile && mode) {
        if (mode.value === 'tags') {
            iconMobile.classList.remove('fa-file');
            iconMobile.classList.add('fa-tags');
        } else {
            iconMobile.classList.remove('fa-tags');
            iconMobile.classList.add('fa-file');
        }
    }
    updatePlaceholders();
});

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
var editedButNotSaved = 0;  // Flag indicating that the note has been edited set to 1
var lastudpdate;
var noteid=-1;
var updateNoteEnCours = 0;

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

function updateident(el)
{
    noteid = el.id.substr(5);
}


function updatenote(){
    updateNoteEnCours = 1;
    var headi = document.getElementById("inp"+noteid).value;
    var ent = $("#entry"+noteid).html();  // Retrieve the content of the note and convert it to HTML (images are converted to base64) to save it in a file (using fwrite in updatenote.php)

    // console.log("RESULT :" + ent);

    var ent = ent.replace(/<br\s*[\/]?>/gi, "&nbsp;<br>");  // Replace empty lines with &nbsp; so that if we format it as code, the line break is preserved
    var entcontent = $("#entry"+noteid).text(); // Retrieve the text content of the note to save it in the database (in updatenote.php)
    // console.log("entcontent:" + entcontent);
    // console.log("ent:" + ent);
    var tags = document.getElementById("tags"+noteid).value;
    
    $.post( "updatenote.php", {id: noteid, tags: tags, heading: headi, entry: ent, entrycontent: entcontent, now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60})
    .done(function(data) {  // We retrieved the date and time in updatenote.php and stored it in "data".
        if(data=='1')
        {
            editedButNotSaved = 0;
            $('#lastupdated'+noteid).html('Last Saved Today');
        }
        else
        {
            editedButNotSaved = 0;
            $('#lastupdated'+noteid).html(data); // We display "data" (which is the date) on the note.
        }
        updateNoteEnCours = 0;
    });
    $('#newnotes').hide().show(0);
}

function newnote(){

    $.post( "insertnew.php", {now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60})
    .done(function(data) {
        try {
            var res = typeof data === 'string' ? JSON.parse(data) : data;
            if(res.status === 1) {
                $(window).scrollTop(0);
                // Redirige vers la nouvelle note (par heading, car c'est ce que index.php attend)
                window.location.href = "index.php?note=" + encodeURIComponent(res.heading);
            } else {
                alert(res.error || data);
            }
        } catch(e) {
            alert('Erreur lors de la création de la note: ' + data);
        }
    });
}

function emptytrash() {
    var r = confirm("Are you sure you want to permanently delete all notes from the trash? They will be lost forever!");
    if (r == true) {
        // Create a new FormData object to send the data
        const formData = new FormData();

        // Use the fetch API to send a POST request
        fetch('emptytrash.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // If successful, redirect after a short delay
            setTimeout(function() { 
                window.location.href = "trash.php"; 
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error); // Log any errors
        });
    }
}

function deletePermanent(iid) {
    var r = confirm("Are you sure you want to permanently delete the note? \"" + document.getElementById("inp" + iid).value + "\"? It will be lost forever!");
    if (r == true) {
        // Create a new FormData object to send the data
        const formData = new FormData();
        formData.append('id', iid);

        // Use the fetch API to send a POST request
        fetch('permanentDelete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === '1') {
                document.getElementById('note' + iid).style.display = 'none'; // Hide the note
            } else {
                alert(data); // Show error message
            }
        })
        .catch(error => {
            console.error('Error:', error); // Log any errors
        });
    }
}

function putBack(iid) {
    // Create a new FormData object to send the data
    const formData = new FormData();
    formData.append('id', iid);

    // Use the fetch API to send a POST request
    fetch('putback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === '1') {
            document.getElementById('note' + iid).style.display = 'none'; // Hide the note
        } else {
            alert(data); // Show error message
        }
    })
    .catch(error => {
        console.error('Error:', error); // Log any errors
    });
}

function deleteNote(iid){
    $.post( "deletenote.php", {id:iid})
    .done(function(data) {
        if(data=='1') {
            // Redirige vers le menu sans note sélectionnée
            window.location.href = "index.php";
        } else {
            alert(data);
        }
    });
}


// The functions below trigger the `update()` function when a note is modified. This simply sets a flag indicating that the note has been modified, but it does not save the changes.

$('body').on( 'keyup', '.name_doss', function (){
    if(updateNoteEnCours==1){
        //alert("Save in progress.")
        showNotificationPopup("Save in progress.");
    }
    else{
        update();
    }
});

$('body').on( 'keyup', '.noteentry', function (){
    if(updateNoteEnCours==1){
        //alert("Automatic save in progress, please do not modify the note.")
        showNotificationPopup("Automatic save in progress, please do not modify the note.");
    }
    else{
        update();
    }
});

$('body').on( 'click', '.popline-btn', function (){
    if(updateNoteEnCours==1){
        //alert("Automatic save in progress, please do not modify the note.")
        showNotificationPopup("Automatic save in progress, please do not modify the note.");
    }
    else{
        update();
    }
});

$('body').on( 'keyup', 'input', function (){
    if(updateNoteEnCours==1){
        //alert("Automatic save in progress, please do not modify the note.")
        showNotificationPopup("Automatic save in progress, please do not modify the note.");
    }
    else{
        update();
    }
});

function update(){
    if(noteid=='search') return;
    editedButNotSaved = 1;  // We set the flag to indicate that the note has been modified.
    var curdate = new Date();
    var curtime = curdate.getTime();
    lastudpdate = curtime;
    displayEditInProgress(); // We display that an action has been taken to edit the note.
}

function displaySavingInProgress(){
    $('#lastupdated'+noteid).html('<span style="color:#FF0000";>Saving in progress...</span>');
}

function displayModificationsDone(){
    $('#lastupdated'+noteid).html('<span style="color:#FF0000";>Note modified</span>');
}

function displayEditInProgress(){
    $('#lastupdated'+noteid).html('<span>Editing in progress...</span>');
}

// Every X seconds (5000 = 5s), we call the `checkedit()` function and display "Note modified" if there have been changes, or "Saving in progress..." if the note is being saved.
$( document ).ready(function() {
   if(editedButNotSaved==0){
       setInterval(function(){
           checkedit();
          // console.log("editedButNotSaved = " + editedButNotSaved);
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