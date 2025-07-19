<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Détection mobile par user agent (doit être fait AVANT tout output et ne jamais être redéfini)
$is_mobile = false;
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_mobile = preg_match('/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/', $user_agent) ? true : false;
}

@ob_start();
require 'config.php';
include 'functions.php';
include 'db_connect.php';

$search = $_POST['search'] ?? $_GET['search'] ?? '';
$tags_search = $_POST['tags_search'] ?? $_GET['tags_search'] ?? $_GET['tags_search_from_list'] ?? '';
$note = $_GET['note'] ?? '';
$folder_filter = $_GET['folder'] ?? '';

// Determine current note folder early for JavaScript
$current_note_folder = 'Uncategorized';
if($note != '') {
    $query_note_folder = "SELECT folder FROM entries WHERE trash = 0 AND heading = '" . mysqli_real_escape_string($con, $note) . "'";
    $res_note_folder = $con->query($query_note_folder);
    if($res_note_folder && $res_note_folder->num_rows > 0) {
        $note_data = mysqli_fetch_array($res_note_folder, MYSQLI_ASSOC);
        $current_note_folder = $note_data["folder"] ?: 'Uncategorized';
    }
}
?>

<html>

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1"/>
    <title><?php echo JOURNAL_NAME;?></title>
    <link type="text/css" rel="stylesheet" href="css/index.css"/>
    <link rel="stylesheet" href="css/index-mobile.css" media="(max-width: 800px)">
    <link rel="stylesheet" href="css/font-awesome.css" />
    <script src="js/toolbar.js"></script>
</head>

<body<?php echo ($is_mobile && $note != '') ? ' class="note-open"' : ''; ?>>   



    <!-- Notification popup -->
    <div id="notificationPopup"></div>
    
    <!-- Modal for creating new folder -->
    <div id="newFolderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('newFolderModal')">&times;</span>
            <h3>Create New Folder</h3>
            <input type="text" id="newFolderName" placeholder="Folder name" maxlength="255" onkeypress="if(event.key==='Enter') createFolder()">
            <div class="modal-buttons">
                <button onclick="createFolder()">Create</button>
                <button onclick="closeModal('newFolderModal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Modal for moving note to folder -->
    <div id="moveNoteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('moveNoteModal')">&times;</span>
            <h3>Move Note to Folder</h3>
            <p>Move "<span id="moveNoteTitle"></span>" to:</p>
            <select id="moveNoteFolder">
                <option value="Uncategorized">Uncategorized</option>
            </select>
            <div class="modal-buttons">
                <button onclick="moveNoteToFolder()">Move</button>
                <button onclick="closeModal('moveNoteModal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Modal for moving note to folder from toolbar -->
    <div id="moveNoteFolderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('moveNoteFolderModal')">&times;</span>
            <h3>Move Note to Folder</h3>
            <select id="moveNoteFolderSelect">
                <option value="Uncategorized">Uncategorized</option>
            </select>
            <div class="modal-buttons">
                <button onclick="moveCurrentNoteToFolder()">Move</button>
                <button onclick="closeModal('moveNoteFolderModal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- Modal for editing folder name -->
    <div id="editFolderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editFolderModal')">&times;</span>
            <h3>Rename Folder</h3>
            <input type="text" id="editFolderName" placeholder="New folder name" maxlength="255">
            <div class="modal-buttons">
                <button onclick="saveFolderName()">Save</button>
                <button onclick="closeModal('editFolderModal')">Cancel</button>
            </div>
        </div>
    </div>
    
    <!-- LEFT COLUMN -->	
    <div id="left_col">

        <!-- Menu pour mobile -->
        <?php if ($is_mobile): ?>
        <div class="containbuttons">
            <div class="newbutton" onclick="newnote();"><span><span title="Create a new note" class="fas fa-file-medical"></span></span></div>
            <div class="newfolderbutton" onclick="newFolder();"><span><span title="Create a new folder" class="fas fa-folder-plus"></span></span></div>
            <div class="list_tags" onclick="window.location = 'listtags.php';"><span><span title="List the tags" class="fas fa-tags"></span></span></div>
            <div class="exportAllButton" onclick="startDownload();">
                <span><span title="Export all notes as a zip file for offline viewing" class="fas fa-download"></span></span>
            </div>
            <div class="trashnotebutton" onclick="window.location = 'trash.php';"><span><span title="Go to the trash" class="fas fa-trash-alt"></span></span></div>
        </div>
        <?php endif; ?>

        <!-- Deux barres de recherche pour mobile -->
        <?php if ($is_mobile): ?>
        <div class="mobile-search-container">
            <form id="search-notes-form-mobile" action="index.php" method="POST">
                <div class="searchbar-row searchbar-icon-row">
                    <div class="searchbar-input-wrapper">
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-notes-mobile" type="search" name="search" class="search form-control searchbar-input" placeholder="Search words in notes" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES); ?>" />
                        <span class="searchbar-icon"><span class="fas fa-search"></span></span>
                    </div>
                    <?php if (!empty($search)): ?>
                        <button type="button" class="searchbar-clear searchbar-clear-outer" title="Clear search" onclick="window.location='index.php'; return false;"><span class="fas fa-times-circle"></span></button>
                    <?php endif; ?>
                </div>
            </form>
            <form id="search-tags-form-mobile" action="index.php" method="POST">
                <div class="searchbar-row searchbar-icon-row">
                    <div class="searchbar-input-wrapper">
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-tags-mobile" type="search" name="tags_search" class="search form-control searchbar-input" placeholder="Search words in tags" value="<?php echo htmlspecialchars($tags_search ?? '', ENT_QUOTES); ?>" />
                        <span class="searchbar-icon"><span class="fas fa-tags"></span></span>
                    </div>
                    <?php if (!empty($tags_search)): ?>
                        <button type="button" class="searchbar-clear searchbar-clear-outer" title="Clear tag search" onclick="window.location='index.php'; return false;"><span class="fas fa-times-circle"></span></button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
    <!-- MENU -->

    <!-- Depending on the cases, we create the queries. -->  
        
    <?php
    // Build search conditions for notes and tags séparément
    $search_condition = '';
    if (!empty($search)) {
        $terms = explode(' ', trim($search));
        foreach ($terms as $term) {
            if (!empty(trim($term))) {
                $search_condition .= " AND (heading LIKE '%" . trim($term) . "%' OR entry LIKE '%" . trim($term) . "%')";
            }
        }
    }
    if (!empty($tags_search)) {
        $terms = explode(' ', trim($tags_search));
        foreach ($terms as $term) {
            if (!empty(trim($term))) {
                $search_condition .= " AND tags LIKE '%" . trim($term) . "%'";
            }
        }
    }
    
    // Add folder filter condition
    $folder_condition = '';
    if (!empty($folder_filter)) {
        $folder_condition = " AND folder = '" . mysqli_real_escape_string($con, $folder_filter) . "'";
    }
    
    $query_left = "SELECT heading, folder FROM entries WHERE trash = 0$search_condition$folder_condition ORDER BY folder, updated DESC";
    $query_right = "SELECT * FROM entries WHERE trash = 0$search_condition$folder_condition ORDER BY updated DESC LIMIT 1";
    ?>
    
    <!-- MENU -->

    <?php if (!$is_mobile): ?>
    <div class="containbuttons">
        <div class="newbutton" onclick="newnote();"><span><span title="Create a new note" class="fas fa-file-medical"></span></span></div>
        <div class="newfolderbutton" onclick="newFolder();"><span><span title="Create a new folder" class="fas fa-folder-plus"></span></span></div>
        <div class="list_tags" onclick="window.location = 'listtags.php';"><span><span title="List the tags" class="fas fa-tags"></span></span></div>
        <div class="exportAllButton" onclick="startDownload();">
            <span><span title="Export all notes as a zip file for offline viewing" class="fas fa-download"></span></span>
        </div>
        <div class="trashnotebutton" onclick="window.location = 'trash.php';"><span><span title="Go to the trash" class="fas fa-trash-alt"></span></span></div>
        <?php
        // Croix rouge retirée
        ?>
    </div>
    <?php endif; ?>
    
    <?php if (!$is_mobile): ?>
    <div class="contains_forms_search searchbar-desktop">
        <form id="search-notes-form" action="index.php" method="POST">
            <div class="searchbar-row searchbar-icon-row">
                <div class="searchbar-input-wrapper">
                    <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-notes" type="search" name="search" class="search form-control searchbar-input" placeholder="Search words in notes" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES); ?>" />
                    <span class="searchbar-icon"><span class="fas fa-search"></span></span>
                </div>
                <?php if (!empty($search)): ?>
                    <button type="button" class="searchbar-clear searchbar-clear-outer" title="Clear search" onclick="window.location='index.php'; return false;"><span class="fas fa-times-circle"></span></button>
                <?php endif; ?>
            </div>
        </form>
        <form id="search-tags-form" action="index.php" method="POST">
            <div class="searchbar-row searchbar-icon-row">
                <div class="searchbar-input-wrapper">
                    <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-tags" type="search" name="tags_search" class="search form-control searchbar-input" placeholder="Search words in tags" value="<?php echo htmlspecialchars($tags_search ?? '', ENT_QUOTES); ?>" />
                    <span class="searchbar-icon"><span class="fas fa-tags"></span></span>
                </div>
                <?php if (!empty($tags_search)): ?>
                    <button type="button" class="searchbar-clear searchbar-clear-outer" title="Clear tag search" onclick="window.location='index.php'; return false;"><span class="fas fa-times-circle"></span></button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php endif; ?>
        
    <script>
        // Variables for folder management
        var isSearchMode = <?php echo (!empty($search) || !empty($tags_search)) ? 'true' : 'false'; ?>;
        var currentNoteFolder = <?php 
            if ($note != '' && empty($search) && empty($tags_search)) {
                echo json_encode($current_note_folder ?? 'Uncategorized');
            } else if ($default_note_folder && empty($search) && empty($tags_search)) {
                echo json_encode($default_note_folder);
            } else {
                echo 'null';
            }
        ?>;
    </script>
        
    <br><hr><br>
            
    <?php
  
        $default_note_folder = null; // Track folder of default note
        
        if($note!='') // If the note is not empty, it means we have just clicked on a note.
        {          
            $query_note = "SELECT * FROM entries WHERE trash = 0 AND heading = '" . mysqli_real_escape_string($con, $note) . "'";
            $res_right = $con->query($query_note);
        } else {
            // No specific note requested, don't show any note by default
            $res_right = null;
        }
        
        // Determine which folders should be open
        $is_search_mode = !empty($search) || !empty($tags_search);
        
        // Exécution de la requête pour la colonne de gauche
        $res_query_left = $con->query($query_left);
        
        // Group notes by folder for hierarchical display
        $folders = [];
        $folders_with_results = []; // Track folders that have search results
        while($row1 = mysqli_fetch_array($res_query_left, MYSQLI_ASSOC)) {
            $folder = $row1["folder"] ?: 'Uncategorized';
            if (!isset($folders[$folder])) {
                $folders[$folder] = [];
            }
            $folders[$folder][] = $row1;
            
            // If in search mode, track folders with results
            if($is_search_mode) {
                $folders_with_results[$folder] = true;
            }
        }
        
        // Add empty folders from folders table
        $empty_folders_query = $con->query("SELECT name FROM folders ORDER BY name");
        while($folder_row = mysqli_fetch_array($empty_folders_query, MYSQLI_ASSOC)) {
            if (!isset($folders[$folder_row['name']])) {
                $folders[$folder_row['name']] = [];
            }
        }
        
        // Sort folders alphabetically (Uncategorized first)
        uksort($folders, function($a, $b) {
            if ($a === 'Uncategorized') return -1;
            if ($b === 'Uncategorized') return 1;
            return strcasecmp($a, $b);
        });
        
        // Display folders and notes
        foreach($folders as $folderName => $notes) {
            // Show folder header only if not filtering by folder
            if (empty($folder_filter)) {
                $folderClass = 'folder-header';
                $folderId = 'folder-' . md5($folderName);
                
                // Determine if this folder should be open
                $should_be_open = false;
                if($is_search_mode) {
                    // In search mode: open folders that have results
                    $should_be_open = isset($folders_with_results[$folderName]);
                } else if($note != '') {
                    // If a note is selected: open only the folder of the current note
                    $should_be_open = ($folderName === $current_note_folder);
                } else if($default_note_folder) {
                    // If no specific note selected but default note loaded: open its folder
                    $should_be_open = ($folderName === $default_note_folder);
                }
                
                // Set appropriate icon and display style
                $chevron_icon = $should_be_open ? 'fa-chevron-down' : 'fa-chevron-right';
                $folder_display = $should_be_open ? 'block' : 'none';
                
                echo "<div class='$folderClass' data-folder='$folderName' onclick='selectFolder(\"$folderName\", this)'>";
                echo "<div class='folder-toggle' onclick='event.stopPropagation(); toggleFolder(\"$folderId\")' data-folder-id='$folderId'>";
                echo "<i class='fas $chevron_icon folder-icon'></i>";
                echo "<i class='fas fa-folder folder-name-icon'></i>";
                echo "<span class='folder-name' ondblclick='editFolderName(\"$folderName\")'>$folderName</span>";
                echo "<span class='folder-actions'>";
                echo "<i class='fas fa-edit folder-edit-btn' onclick='event.stopPropagation(); editFolderName(\"$folderName\")' title='Rename folder'></i>";
                if ($folderName === 'Uncategorized') {
                    echo "<i class='fas fa-trash-alt folder-empty-btn' onclick='event.stopPropagation(); emptyFolder(\"$folderName\")' title='Move all notes to trash'></i>";
                } else {
                    echo "<i class='fas fa-trash folder-delete-btn' onclick='event.stopPropagation(); deleteFolder(\"$folderName\")' title='Delete folder'></i>";
                }
                echo "</span>";
                echo "</div>";
                echo "<div class='folder-content' id='$folderId' style='display: $folder_display;'>";
            }
            
            // Display notes in folder
            foreach($notes as $row1) {
                $isSelected = ($note === $row1["heading"]) ? 'selected-note' : '';
                // Préserver l'état de recherche dans les liens de notes
                $params = [];
                if (!empty($search)) $params[] = 'search=' . urlencode($search);
                if (!empty($tags_search)) $params[] = 'tags_search=' . urlencode($tags_search);
                if (!empty($folder_filter)) $params[] = 'folder=' . urlencode($folder_filter);
                $params[] = 'note=' . urlencode($row1["heading"]);
                $link = 'index.php?' . implode('&', $params);
                
                $noteClass = empty($folder_filter) ? 'links_arbo_left note-in-folder' : 'links_arbo_left';
                echo "<a class='$noteClass $isSelected' href='$link' data-note-id='" . $row1["heading"] . "' data-folder='$folderName'>";
                echo "<i class='fas fa-folder-open move-note-btn' onclick='showMoveNoteDialog(\"" . addslashes($row1["heading"]) . "\")' title='Move to folder'></i>";
                echo "<span class='note-title'>" . ($row1["heading"] ?: 'Untitled note') . "</span>";
                echo "</a>";
                echo "<div id=pxbetweennotes></div>";
            }
            
            if (empty($folder_filter)) {
                echo "</div>"; // Close folder-content
                echo "</div>"; // Close folder-header
            }
        }
                 
    ?>
    </div>
    
    <!-- RIGHT COLUMN -->	
    <div id="right_col">
    
        <!-- Barre de recherche supprimée de la colonne de droite (desktop) -->
        
        <?php        
            
            // Right-side list based on the query created earlier //		
            
            // Check if we should display a note or welcome message
            if ($res_right && $res_right->num_rows > 0) {
                while($row = mysqli_fetch_array($res_right, MYSQLI_ASSOC))
                {
                
                    $filename = "entries/".$row["id"].".html";
                    $title = $row['heading'];             
                    $entryfinal = file_exists($filename) ? file_get_contents($filename) : '';
               
           
                // Affichage harmonisé desktop/mobile :
                echo '<div id="note'.$row['id'].'" class="notecard">';
                echo '<div class="innernote">';
                // Ligne 1 : barre d’édition centrée (plus de date)
                echo '<div class="note-header">';
                // Boutons de formatage (cachés par défaut sur mobile, visibles lors de sélection)
                echo '<div class="note-edit-toolbar">';
                echo '<button type="button" class="toolbar-btn btn-bold" title="Gras" onclick="document.execCommand(\'bold\')"><i class="fas fa-bold"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-italic" title="Italique" onclick="document.execCommand(\'italic\')"><i class="fas fa-italic"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-underline" title="Souligné" onclick="document.execCommand(\'underline\')"><i class="fas fa-underline"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-strikethrough" title="Barré" onclick="document.execCommand(\'strikeThrough\')"><i class="fas fa-strikethrough"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-link" title="Lien" onclick="addLinkToNote()"><i class="fas fa-link"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-unlink" title="Supprimer le lien" onclick="document.execCommand(\'unlink\')"><i class="fas fa-unlink"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-color" title="Couleur texte" onclick="toggleRedColor()"><i class="fas fa-palette" style="color:#ff2222;"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-highlight" title="Surlignage" onclick="toggleYellowHighlight()"><i class="fas fa-fill-drip" style="color:#ffe066;"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-list-ul" title="Liste à puces" onclick="document.execCommand(\'insertUnorderedList\')"><i class="fas fa-list-ul"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-list-ol" title="Liste numérotée" onclick="document.execCommand(\'insertOrderedList\')"><i class="fas fa-list-ol"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-text-height" title="Taille police" onclick="changeFontSize()"><i class="fas fa-text-height"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-code" title="Bloc de code" onclick="toggleCodeBlock()"><i class="fas fa-code"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-eraser" title="Effacer formatage" onclick="document.execCommand(\'removeFormat\')"><i class="fas fa-eraser"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-separator" title="Ajouter un séparateur" onclick="insertSeparator()"><i class="fas fa-minus"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-save" title="Enregistrer la note" onclick="saveFocusedNoteJS()"><i class="fas fa-save"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-folder" title="Changer de dossier" onclick="showMoveFolderDialog(\''.$row['id'].'\')"><i class="fas fa-folder"></i></button>';
                echo '<a href="'.$filename.'" download="'.$title.'" class="toolbar-btn btn-download" title="Exporter la note"><i class="fas fa-download"></i></a>';
                echo '<button type="button" class="toolbar-btn btn-info" title="Infos note" onclick="alert(\'Note file: '.$row['id'].'.html\\nCreated on: '.formatDateTime(strtotime($row['created'])).'\\nLast updated: '.formatDateTime(strtotime($row['updated'])).'\')"><i class="fas fa-info-circle"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-trash" title="Supprimer la note" onclick="deleteNote(\''.$row['id'].'\')"><i class="fas fa-trash"></i></button>';
                echo '</div>';
                echo '</div>';
                
                // Tags only (folder selection removed)
                echo '<div class="note-tags-row">';
                echo '<span class="fa fa-tag icon_tag"></span>';
                echo '<span class="name_tags">'
                    .'<input class="add-margin" size="70px" autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="Tags?" onfocus="updateidtags(this);" id="tags'.$row['id'].'" type="text" placeholder="Tags ?" value="'.htmlspecialchars(str_replace(',', ' ', $row['tags']), ENT_QUOTES).'"/>'
                .'</span>';
                echo '</div>';
                
                // Hidden folder value for the note
                echo '<input type="hidden" id="folder'.$row['id'].'" value="'.htmlspecialchars($row['folder'] ?: 'Uncategorized', ENT_QUOTES).'"/>';
                // Titre
                echo '<h4><input class="css-title" autocomplete="off" autocapitalize="off" spellcheck="false" onfocus="updateidhead(this);" id="inp'.$row['id'].'" type="text" placeholder="Title ?" value="'.htmlspecialchars(htmlspecialchars_decode($row['heading'] ?: 'Untitled note'), ENT_QUOTES).'"/></h4>';
                // Contenu de la note
                echo '<div class="noteentry" autocomplete="off" autocapitalize="off" spellcheck="false" onfocus="updateident(this);" id="entry'.$row['id'].'" data-ph="Enter text or paste images" contenteditable="true">'.$entryfinal.'</div>';
                echo '<div class="note-bottom-space"></div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            // Display welcome message when no note is selected
            echo '<div class="welcome-message" style="padding: 40px; text-align: center; color: #666; font-family: \'Inter\', sans-serif;">';
            echo '<div style="font-size: 24px; margin-bottom: 20px; color: #333;">📝 Welcome to ' . JOURNAL_NAME . '</div>';
            echo '<div style="font-size: 16px; line-height: 1.6; max-width: 400px; margin: 0 auto;">';
            echo '<p>Select a note from the sidebar to start editing, or create a new note.</p>';
            echo '<p style="margin-top: 30px;"><strong>Getting started:</strong></p>';
            echo '<ul style="text-align: left; display: inline-block;">';
            echo '<li>Click the <i class="fas fa-file-medical" style="color: #007DB8;"></i> button to create a new note</li>';
            echo '<li>Click the <i class="fas fa-folder-plus" style="color: #007DB8;"></i> button to create a new folder</li>';
            echo '<li>Use the search bars to find specific notes</li>';
            echo '</ul>';
            echo '</div>';
            echo '</div>';
        }
        ?>        
    </div>
        
    </div>
</body>
<script src="js/script.js"></script>
</html>