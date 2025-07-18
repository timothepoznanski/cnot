// Download function (popup)
function startDownload() {
    window.location = 'exportEntries.php';
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
    var entryElem = document.getElementById("entry"+noteid);
    var ent = entryElem ? entryElem.innerHTML : "";
    // console.log("RESULT :" + ent);
    ent = ent.replace(/<br\s*[\/]?>/gi, "&nbsp;<br>");
    var entcontent = entryElem ? entryElem.textContent : "";
    // console.log("entcontent:" + entcontent);
    // console.log("ent:" + ent);
    var tags = document.getElementById("tags"+noteid).value;

    var params = new URLSearchParams({
        id: noteid,
        tags: tags,
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
        now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60
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

document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('popline-btn')) {
        if(updateNoteEnCours==1){
            showNotificationPopup("Automatic save in progress, please do not modify the note.");
        } else {
            update();
        }
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