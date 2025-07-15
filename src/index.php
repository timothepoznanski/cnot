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
?>

<html>

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1"/>
    <title><?php echo JOURNAL_NAME;?></title>
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/font-awesome.css" />
    <link rel="stylesheet" type="text/css" href="css/popline.css" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.popline.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.link.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.decoration.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.blockquote.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.blockcode.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.list.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.social.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.textcolor.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.backgroundcolor.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.fontsize.js"></script>
</head>

<body<?php echo ($note != '') ? ' class="note-selected"' : ''; ?>>   



    <!-- Notification popup -->
    <div id="notificationPopup"></div>
    
    <!-- LEFT COLUMN -->	
    <div id="left_col">

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
    $query_left = "SELECT heading FROM entries WHERE trash = 0$search_condition ORDER BY updated DESC";
    $query_right = "SELECT * FROM entries WHERE trash = 0$search_condition ORDER BY updated DESC LIMIT 1";
    ?>
    
    <!-- MENU -->

    <?php if (!$is_mobile): ?>
    <div class="containbuttons">
        <div class="newbutton" onclick="newnote();"><span><span title="Create a new note" class="fas fa-file-medical"></span></span></div>
        <div class="list_tags" onclick="window.location = 'listtags.php';"><span><span title="List the tags" class="fas fa-tags"></span></span></div>
        <div class="exportAllButton" onclick="startDownload();">
            <span><span title="Export all notes as a zip file for offline viewing" class="fas fa-download"></span></span>
        </div>
        <div id="downloadPopup">Please wait while the archive is being created...</div>
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

    <?php if ($is_mobile && $note != ''): ?>
    <div class="mobile-menu-bar">
        <div class="btn-menu" onclick="window.location='index.php'" title="Retour à la liste des notes">
            <span class="fa fa-home"></span>
        </div>
    </div>
    <?php endif; ?>
        
    <br><hr><br>
            
    <?php
  
        if($note!='') // If the note is not empty, it means we have just clicked on a note.
        {          
            $query_note = "SELECT * FROM entries WHERE trash = 0 AND heading = '" . mysqli_real_escape_string($con, $note) . "'";
            $res_right = $con->query($query_note);
        }
        
        // Exécution de la requête pour la colonne de gauche
        $res_query_left = $con->query($query_left);
        
        while($row1 = mysqli_fetch_array($res_query_left, MYSQLI_ASSOC)) {       
            $isSelected = ($note === $row1["heading"]) ? 'selected-note' : '';
            // Préserver l'état de recherche dans les liens de notes
            $params = [];
            if (!empty($search)) $params[] = 'search=' . urlencode($search);
            if (!empty($tags_search)) $params[] = 'tags_search=' . urlencode($tags_search);
            $params[] = 'note=' . urlencode($row1["heading"]);
            $link = 'index.php?' . implode('&', $params);
            echo "<form action=index.php><input type=hidden name=note>";
            echo "<a class='links_arbo_left  $isSelected' href='$link'><div id='icon_notes' class='far fa-file'></div>" . ($row1["heading"] ?: 'Untitled note') . "</a>";
            echo "</form>";
            echo "<div id=pxbetweennotes></div>";
        }
                 
    ?>
    </div>
    
    <!-- RIGHT COLUMN -->	
    <div id="right_col">
    
        <!-- Barre de recherche supprimée de la colonne de droite (desktop) -->
        
        <?php        
            
            // Right-side list based on the query created earlier //		
            
            // Exécution de la requête pour la colonne de droite
            if (!isset($res_right)) {
                $res_right = $con->query($query_right);
            }
           
            while($row = mysqli_fetch_array($res_right, MYSQLI_ASSOC))
            {
            
                $filename = "entries/".$row["id"].".html";
                $title = $row['heading'];             
                $entryfinal = file_exists($filename) ? file_get_contents($filename) : '';
           
                // Affichage harmonisé desktop/mobile :
                echo '<div id="note'.$row['id'].'" class="notecard">';
                echo '<div class="innernote">';
                // Ligne 1 : date à gauche, boutons à droite
                echo '<div class="note-header-mobile">';
                echo '<div id="lastupdated'.$row['id'].'" class="lastupdated">'.formatDateTime(strtotime($row['updated'])).'</div>';
                if (!$is_mobile) {
                    echo '<div class="note-icons-mobile">';
                    echo '<span class="fas fa-save icon_save" title="Save this note" onclick="saveFocusedNoteJS()"></span>';
                    echo '<a href="'.$filename.'" download="'.$title.'"><span class="fas fa-download icon_download" title="Export this note"></span></a>';
                    echo '<span class="fas fa-info-circle icon_info" title="Show note number" onclick="alert(\'Note file: '.$row['id'].'.html\nCreated on: '.formatDateTime(strtotime($row['created'])).'\nLast updated: '.formatDateTime(strtotime($row['updated'])).'\')"></span>';
                    echo '<span class="fas fa-trash icon_trash" title="Delete this note" onclick="deleteNote(\''.$row['id'].'\')"></span>';
                    echo '</div>';
                }
                echo '</div>';
                // Ligne 2 : icône tag + tags
                echo '<div class="note-tags-row">';
                echo '<span class="fa fa-tag icon_tag"></span>';
                echo '<span class="name_tags">'
                    .'<input class="add-margin-left" size="70px" autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="Tags" onfocus="updateidtags(this);" id="tags'.$row['id'].'" type="text" placeholder="Tags ?" value="'.htmlspecialchars(str_replace(',', ' ', $row['tags']), ENT_QUOTES).'"'.($is_mobile ? ' readonly' : '').'/>'
                .'</span>';
                echo '</div>';
                // Titre
                echo '<h4><input class="css-title" autocomplete="off" autocapitalize="off" spellcheck="false" onfocus="updateidhead(this);" id="inp'.$row['id'].'" type="text" placeholder="Title ?" value="'.htmlspecialchars(htmlspecialchars_decode($row['heading'] ?: 'Untitled note'), ENT_QUOTES).'"'.($is_mobile ? ' readonly' : '').'/></h4>';
                // Contenu de la note
                echo '<div class="noteentry" autocomplete="off" autocapitalize="off" spellcheck="false" onload="initials(this);" onfocus="updateident(this);" id="entry'.$row['id'].'" data-ph="Enter text or paste images" contenteditable="'.($is_mobile ? 'false' : 'true').'">'.$entryfinal.'</div>';
                echo '<div class="note-bottom-space"></div>';
                echo '</div>';
                echo '</div>';
            }
        ?>        
    </div>
</body>
    
<!-- Do not place this block at the top, otherwise Popline will no longer work -->
<script src="js/script.js"></script>
<script>
// L'appel à Popline dépend de jQuery et doit rester ici tant que les plugins ne sont pas migrés
$(".noteentry").popline();
</script>  <!-- When selecting text, it displays the floating editing menu in the .noteentry area (i.e., note content) above / It must be 'contenteditable="true"' -->
</html>