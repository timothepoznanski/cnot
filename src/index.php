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

ob_start();
require 'config.php';
include 'functions.php';
include 'db_connect.php';

// Vérification et migration des colonnes (seulement à l'ouverture de l'application)
$result = $con->query("SHOW COLUMNS FROM entries LIKE 'folder'");
if ($result->num_rows == 0) {
    $con->query("ALTER TABLE entries ADD COLUMN folder varchar(255) DEFAULT 'Uncategorized'");
}

$result = $con->query("SHOW COLUMNS FROM entries LIKE 'favorite'");
if ($result->num_rows == 0) {
    $con->query("ALTER TABLE entries ADD COLUMN favorite TINYINT(1) DEFAULT 0");
}

$result = $con->query("SHOW COLUMNS FROM entries LIKE 'attachments'");
if ($result->num_rows == 0) {
    $con->query("ALTER TABLE entries ADD COLUMN attachments TEXT DEFAULT NULL");
}

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
            <select id="moveNoteFolderSelect" onchange="toggleNewFolderInput()">
                <option value="Uncategorized">Uncategorized</option>
            </select>
            <div id="newFolderInputContainer" style="display: none; margin-top: 10px;">
                <input type="text" id="moveNewFolderName" placeholder="Enter new folder name" style="width: 100%; padding: 8px; margin-bottom: 10px;">
            </div>
            <div class="modal-buttons">
                <button type="button" onclick="moveCurrentNoteToFolder()">Move</button>
                <button type="button" onclick="closeModal('moveNoteFolderModal')">Cancel</button>
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
    
    <!-- Modal for attachments -->
    <div id="attachmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('attachmentModal')">&times;</span>
            <h3>Manage Attachments</h3>
            <div class="attachment-upload">
                <div class="file-input-container">
                    <input type="file" id="attachmentFile" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar">
                </div>
                <div class="upload-button-container">
                    <button onclick="uploadAttachment()">Upload File</button>
                </div>
            </div>
            <div id="attachmentsList" class="attachments-list">
                <!-- Attachments will be loaded here -->
            </div>
            <div class="modal-buttons">
                <button onclick="closeModal('attachmentModal')">Close</button>
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
            <div class="settings-dropdown">
                <div class="settingsbutton" onclick="toggleSettingsMenu(event);" title="Settings">
                    <span><span class="fas fa-cog"></span></span>
                </div>
                <div class="settings-menu" id="settingsMenu">
                    <div class="settings-menu-item" onclick="foldAllFolders();">
                        <i class="fas fa-minus-square"></i>
                        <span>Fold All Folders</span>
                    </div>
                    <div class="settings-menu-item" onclick="unfoldAllFolders();">
                        <i class="fas fa-plus-square"></i>
                        <span>Unfold All Folders</span>
                    </div>
                </div>
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
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-notes-mobile" type="search" name="search" class="search form-control searchbar-input" placeholder="Search words in all notes" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES); ?>" />
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
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-tags-mobile" type="search" name="tags_search" class="search form-control searchbar-input" placeholder="Search words in all tags" value="<?php echo htmlspecialchars($tags_search ?? '', ENT_QUOTES); ?>" />
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
        if ($folder_filter === 'Favorites') {
            $folder_condition = " AND favorite = 1";
        } else {
            $folder_condition = " AND folder = '" . mysqli_real_escape_string($con, $folder_filter) . "'";
        }
    }
    
    $query_left = "SELECT heading, folder, favorite FROM entries WHERE trash = 0$search_condition$folder_condition ORDER BY folder, updated DESC";
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
        <div class="settings-dropdown">
            <div class="settingsbutton" onclick="toggleSettingsMenu(event);" title="Settings">
                <span><span class="fas fa-cog"></span></span>
            </div>
            <div class="settings-menu" id="settingsMenu">
                <div class="settings-menu-item" onclick="foldAllFolders();">
                    <i class="fas fa-minus-square"></i>
                    <span>Fold All Folders</span>
                </div>
                <div class="settings-menu-item" onclick="unfoldAllFolders();">
                    <i class="fas fa-plus-square"></i>
                    <span>Unfold All Folders</span>
                </div>
            </div>
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
                    <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-notes" type="search" name="search" class="search form-control searchbar-input" placeholder="Search words in all notes" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES); ?>" />
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
                    <input autocomplete="off" autocapitalize="off" spellcheck="false" id="search-tags" type="search" name="tags_search" class="search form-control searchbar-input" placeholder="Search words in all tags" value="<?php echo htmlspecialchars($tags_search ?? '', ENT_QUOTES); ?>" />
                    <span class="searchbar-icon"><span class="fas fa-tags"></span></span>
                </div>
                <?php if (!empty($tags_search)): ?>
                    <button type="button" class="searchbar-clear searchbar-clear-outer" title="Clear tag search" onclick="window.location='index.php'; return false;"><span class="fas fa-times-circle"></span></button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php endif; ?>
        
    <?php
        // Determine default note folder before JavaScript
        $default_note_folder = null; // Track folder of default note
        
        if($note!='') // If the note is not empty, it means we have just clicked on a note.
        {          
            $query_note = "SELECT * FROM entries WHERE trash = 0 AND heading = '" . mysqli_real_escape_string($con, $note) . "'";
            $res_right = $con->query($query_note);
            
            // Si la note demandée n'existe pas, afficher la dernière note mise à jour
            if(!$res_right || $res_right->num_rows == 0) {
                $note = ''; // Reset note to trigger showing latest note
                $check_notes_query = "SELECT COUNT(*) as note_count FROM entries WHERE trash = 0$search_condition$folder_condition";
                $check_result = $con->query($check_notes_query);
                $note_count = $check_result->fetch_assoc()['note_count'];
                
                if ($note_count > 0) {
                    // Show the most recently updated note
                    $res_right = $con->query($query_right);
                    if($res_right && $res_right->num_rows > 0) {
                        $latest_note = $res_right->fetch_assoc();
                        $default_note_folder = $latest_note["folder"] ?: 'Uncategorized';
                        // Reset the result pointer for display
                        $res_right->data_seek(0);
                    }
                } else {
                    // No notes available, show welcome message
                    $res_right = null;
                }
            }
        } else {
            // No specific note requested, check if we have notes to show the latest one
            $check_notes_query = "SELECT COUNT(*) as note_count FROM entries WHERE trash = 0$search_condition$folder_condition";
            $check_result = $con->query($check_notes_query);
            $note_count = $check_result->fetch_assoc()['note_count'];
            
            if ($note_count > 0) {
                // Show the most recently updated note
                $res_right = $con->query($query_right);
                if($res_right && $res_right->num_rows > 0) {
                    $latest_note = $res_right->fetch_assoc();
                    $default_note_folder = $latest_note["folder"] ?: 'Uncategorized';
                    // Reset the result pointer for display
                    $res_right->data_seek(0);
                }
            } else {
                // No notes available, show welcome message
                $res_right = null;
            }
        }
    ?>
        
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
                    
    <?php
        
        // Determine which folders should be open
        $is_search_mode = !empty($search) || !empty($tags_search);
        
        // Exécution de la requête pour la colonne de gauche
        $res_query_left = $con->query($query_left);
        
        // Group notes by folder for hierarchical display
        $folders = [];
        $folders_with_results = []; // Track folders that have search results
        $favorites = []; // Store favorite notes
        
        while($row1 = mysqli_fetch_array($res_query_left, MYSQLI_ASSOC)) {
            $folder = $row1["folder"] ?: 'Uncategorized';
            if (!isset($folders[$folder])) {
                $folders[$folder] = [];
            }
            $folders[$folder][] = $row1;
            
            // If the note is a favorite, also add it to the favorites "folder"
            if ($row1["favorite"]) {
                $favorites[] = $row1;
            }
            
            // If in search mode, track folders with results
            if($is_search_mode) {
                $folders_with_results[$folder] = true;
                if ($row1["favorite"]) {
                    $folders_with_results['Favorites'] = true;
                }
            }
        }
        
        // Add favorites as a special folder if there are any favorites
        if (!empty($favorites)) {
            $folders = ['Favorites' => $favorites] + $folders;
        }
        
        // Add empty folders from folders table
        $empty_folders_query = $con->query("SELECT name FROM folders ORDER BY name");
        while($folder_row = mysqli_fetch_array($empty_folders_query, MYSQLI_ASSOC)) {
            if (!isset($folders[$folder_row['name']])) {
                $folders[$folder_row['name']] = [];
            }
        }
        
        // Sort folders alphabetically (Favorites first, then Uncategorized, then others)
        uksort($folders, function($a, $b) {
            if ($a === 'Favorites') return -1;
            if ($b === 'Favorites') return 1;
            if ($a === 'Uncategorized') return -1;
            if ($b === 'Uncategorized') return 1;
            return strcasecmp($a, $b);
        });
        
        // Display folders and notes
        foreach($folders as $folderName => $notes) {
            // En mode recherche, ne pas afficher les dossiers vides
            if ($is_search_mode && empty($notes)) {
                continue;
            }
            
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
                    // If a note is selected: open the folder of the current note AND Favoris if note is favorite
                    if ($folderName === $current_note_folder) {
                        $should_be_open = true;
                    } else if ($folderName === 'Favoris') {
                        // Open Favoris folder if the current note is favorite
                        $query_check_favorite = "SELECT favorite FROM entries WHERE trash = 0 AND heading = '" . mysqli_real_escape_string($con, $note) . "'";
                        $res_check_favorite = $con->query($query_check_favorite);
                        if ($res_check_favorite && $res_check_favorite->num_rows > 0) {
                            $favorite_data = $res_check_favorite->fetch_assoc();
                            $should_be_open = $favorite_data['favorite'] == 1;
                        }
                    }
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
                
                // Icône spéciale pour le dossier Favorites
                if ($folderName === 'Favorites') {
                    echo "<i class='fas fa-star folder-name-icon' style='color:#007DB8;'></i>";
                } else {
                    echo "<i class='fas fa-folder folder-name-icon'></i>";
                }
                
                echo "<span class='folder-name' ondblclick='editFolderName(\"$folderName\")'>$folderName</span>";
                echo "<span class='folder-note-count'>(" . count($notes) . ")</span>";
                echo "<span class='folder-actions'>";
                
                // Actions différentes selon le type de dossier
                if ($folderName === 'Favorites') {
                    // Pas d'actions pour le dossier Favorites (il se gère automatiquement)
                } else if ($folderName === 'Uncategorized') {
                    echo "<i class='fas fa-edit folder-edit-btn' onclick='event.stopPropagation(); editFolderName(\"$folderName\")' title='Rename folder'></i>";
                    echo "<i class='fas fa-trash-alt folder-empty-btn' onclick='event.stopPropagation(); emptyFolder(\"$folderName\")' title='Move all notes to trash'></i>";
                } else {
                    echo "<i class='fas fa-edit folder-edit-btn' onclick='event.stopPropagation(); editFolderName(\"$folderName\")' title='Rename folder'></i>";
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
                if ($is_mobile) {
                    echo '<button type="button" class="toolbar-btn btn-home" title="Home" onclick="window.location.href=\'index.php\'"><i class="fas fa-home"></i></button>';
                }
                echo '<button type="button" class="toolbar-btn btn-bold" title="Bold" onclick="document.execCommand(\'bold\')"><i class="fas fa-bold"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-italic" title="Italic" onclick="document.execCommand(\'italic\')"><i class="fas fa-italic"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-underline" title="Underline" onclick="document.execCommand(\'underline\')"><i class="fas fa-underline"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-strikethrough" title="Strikethrough" onclick="document.execCommand(\'strikeThrough\')"><i class="fas fa-strikethrough"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-link" title="Link" onclick="addLinkToNote()"><i class="fas fa-link"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-unlink" title="Remove link" onclick="document.execCommand(\'unlink\')"><i class="fas fa-unlink"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-color" title="Text color" onclick="toggleRedColor()"><i class="fas fa-palette" style="color:#ff2222;"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-highlight" title="Highlight" onclick="toggleYellowHighlight()"><i class="fas fa-fill-drip" style="color:#ffe066;"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-list-ul" title="Bullet list" onclick="document.execCommand(\'insertUnorderedList\')"><i class="fas fa-list-ul"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-list-ol" title="Numbered list" onclick="document.execCommand(\'insertOrderedList\')"><i class="fas fa-list-ol"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-text-height" title="Font size" onclick="changeFontSize()"><i class="fas fa-text-height"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-code" title="Code block" onclick="toggleCodeBlock()"><i class="fas fa-code"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-eraser" title="Clear formatting" onclick="document.execCommand(\'removeFormat\')"><i class="fas fa-eraser"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-separator" title="Add separator" onclick="insertSeparator()"><i class="fas fa-minus"></i></button>';
                echo '<button type="button" class="toolbar-btn btn-save" title="Save note" onclick="saveFocusedNoteJS()"><i class="fas fa-save"></i></button>';
                
                // Menu déroulant pour les actions sur la note (desktop seulement)
                if (!$is_mobile) {
                    echo '<div class="toolbar-dropdown">';
                    
                    // Calculer le nombre d'attachments pour déterminer la couleur du bouton settings
                    $attachments_count = 0;
                    if (!empty($row['attachments'])) {
                        $attachments_data = json_decode($row['attachments'], true);
                        if (is_array($attachments_data)) {
                            $attachments_count = count($attachments_data);
                        }
                    }
                    
                    echo '<button type="button" class="toolbar-btn btn-settings'.($attachments_count > 0 ? ' has-attachments' : '').'" title="Note settings" onclick="toggleNoteMenu(\''.$row['id'].'\')" id="settings-btn-'.$row['id'].'"><i class="fas fa-cog"></i></button>';
                    echo '<div class="dropdown-menu" id="note-menu-'.$row['id'].'" style="display: none;">';
                    
                    // Bouton favoris avec icône étoile
                    $is_favorite = $row['favorite'] ?? 0;
                    $star_class = $is_favorite ? 'fas' : 'far';
                    $favorite_text = $is_favorite ? 'Remove from favorites' : 'Add to favorites';
                    echo '<div class="dropdown-item" onclick="toggleFavorite(\''.$row['id'].'\')"><i class="'.$star_class.' fa-star" style="color:#007DB8;"></i> '.$favorite_text.'</div>';
                    
                    echo '<div class="dropdown-item" onclick="showMoveFolderDialog(\''.$row['id'].'\')"><i class="fas fa-folder"></i> Move to folder</div>';
                    
                    echo '<div class="dropdown-item'.($attachments_count > 0 ? ' has-attachments' : '').'" onclick="showAttachmentDialog(\''.$row['id'].'\')"><i class="fas fa-paperclip"></i> Attachments ('.$attachments_count.')</div>';
                    echo '<div class="dropdown-item" onclick="downloadFile(\''.$filename.'\', \''.addslashes($title).'\')"><i class="fas fa-download"></i> Export to HTML</div>';
                    echo '<div class="dropdown-item" onclick="showNoteInfo(\''.$row['id'].'\', \''.addslashes($row['created']).'\', \''.addslashes($row['updated']).'\')"><i class="fas fa-info-circle"></i> Information</div>';
                    echo '<div class="dropdown-item dropdown-delete" onclick="deleteNote(\''.$row['id'].'\')"><i class="fas fa-trash"></i> Delete</div>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    // Boutons individuels pour mobile
                    // Calculer le nombre d'attachments pour le bouton mobile
                    $attachments_count = 0;
                    if (!empty($row['attachments'])) {
                        $attachments_data = json_decode($row['attachments'], true);
                        if (is_array($attachments_data)) {
                            $attachments_count = count($attachments_data);
                        }
                    }
                    
                    // Bouton favoris avec icône étoile
                    $is_favorite = $row['favorite'] ?? 0;
                    $star_class = $is_favorite ? 'fas' : 'far';
                    $favorite_title = $is_favorite ? 'Remove from favorites' : 'Add to favorites';
                    echo '<button type="button" class="toolbar-btn btn-favorite" title="'.$favorite_title.'" onclick="toggleFavorite(\''.$row['id'].'\')"><i class="'.$star_class.' fa-star" style="color:#007DB8;"></i></button>';
                    
                    echo '<button type="button" class="toolbar-btn btn-folder" title="Move to folder" onclick="showMoveFolderDialog(\''.$row['id'].'\')"><i class="fas fa-folder"></i></button>';
                    echo '<button type="button" class="toolbar-btn btn-attachment'.($attachments_count > 0 ? ' has-attachments' : '').'" title="Attachments" onclick="showAttachmentDialog(\''.$row['id'].'\')"><i class="fas fa-paperclip"></i></button>';
                    echo '<a href="'.$filename.'" download="'.$title.'" class="toolbar-btn btn-download" title="Export to HTML"><i class="fas fa-download"></i></a>';
                    echo '<button type="button" class="toolbar-btn btn-info" title="Information" onclick="showNoteInfo(\''.$row['id'].'\', \''.addslashes($row['created']).'\', \''.addslashes($row['updated']).'\')"><i class="fas fa-info-circle"></i></button>';
                    echo '<button type="button" class="toolbar-btn btn-trash" title="Delete" onclick="deleteNote(\''.$row['id'].'\')"><i class="fas fa-trash"></i></button>';
                }
                
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
            // Display welcome message when no note is selected - page blanche
            echo '<div class="welcome-message" style="height: 100%; width: 100%;"></div>';
        }
        ?>        
    </div>
        
    </div>
</body>
<script src="js/script.js"></script>
</html>