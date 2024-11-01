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

window.onbeforeunload = function(){
    if(editedButNotSaved!=0){
        return 'We are still attempting to save something...';
    }
};

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

    $.post( "updatenote.php", {pass: app_pass, id: noteid, tags: tags, heading: headi, entry: ent, entrycontent: entcontent, now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60})
    .done(function(data) {  // We retrieved the date and time in updatenote.php and stored it in "data".
        if(data=='1')
        {
            editied = 0;
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
        
    $.post( "insertnew.php", {pass: app_pass, now: (new Date().getTime()/1000)-new Date().getTimezoneOffset()*60})
    .done(function(data) {
        if(data=='1') 
        {
            $(window).scrollTop(0);
            //location.reload(true);
            window.location.href = "index.php";
        }
        else alert(data);
    });    
}                  

function emptytrash(){
    var r = confirm("Are you sure you want to permanently delete all notes from the trash? They will be lost forever!");
    if (r == true) {
        $.post( "emptytrash.php", {pass: app_pass})
        setTimeout(function(){ window.location.href = "trash.php"; }, 1000);
    }
}

function deletePermanent(iid){
    var r = confirm("Are you sure you want to permanently delete the note? \""+document.getElementById("inp"+iid).value+"\"? It will be lost forever!");
    if (r == true) {
        $.post( "permanentDelete.php", {pass: app_pass, id:iid})
        .done(function(data) {
            if(data=='1') $('#note'+iid).hide();
            else alert(data);
        });
    }
}

function putBack(iid){
    $.post( "putback.php", {pass: app_pass, id:iid})
    .done(function(data) {
        if(data=='1') $('#note'+iid).hide();
        else alert(data);
    });
}

function deleteNote(iid){
        $.post( "deletenote.php", {pass: app_pass, id:iid})
        .done(function(data) {
            if(data=='1') $('#note'+iid).hide();
            else alert(data);
        });
		setTimeout(function(){ window.location.reload(); }, 1000);
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
    $('#lastupdated'+noteid).html('<b><span style="color:#FF0000";>Saving in progress...</span></b>');
}

function displayModificationsDone(){
    $('#lastupdated'+noteid).html('<b><span style="color:#FF0000";>Note modified</span></b>');
}

function displayEditInProgress(){
    $('#lastupdated'+noteid).html('<span>Editing in progress...</span>');
}

// Every X seconds (5000 = 5s), we call the `checkedit()` function and display "Note modified" if there have been changes, or "Saving in progress..." if the note is being saved.
$( document ).ready(function() {
   if(editedButNotSaved==0){
       setInterval(function(){
           checkedit(); 
           console.log("editedButNotSaved = " + editedButNotSaved); 
           if(editedButNotSaved==1){displayModificationsDone();} 
           if(updateNoteEnCours==1){displaySavingInProgress();} 
        }, 2000);
   }
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
    console.log("noteid = " + noteid); 
    //if(noteid==-1) return ;
    if(noteid==-1){ 
        showNotificationPopup("Click anywhere in the note to be saved, then try again."); 
        return ;
    }
    console.log("updateNoteEnCours = " + editedButNotSaved / "editedButNotSaved = " + editedButNotSaved); 
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