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
    <link rel="stylesheet" href="css/mobile.css" media="(max-width: 800px)">
    <link rel="stylesheet" href="css/font-awesome.css" />
    <!-- Popline supprimé -->
</head>

<body<?php echo ($is_mobile && $note != '') ? ' class="note-open"' : ''; ?>>   



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
            echo "<a class='links_arbo_left $isSelected' href='$link'><div id='icon_notes' class='far fa-file'></div>" . ($row1["heading"] ?: 'Untitled note') . "</a>";

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
                echo '<div class="note-header">';
                // Date à gauche
                echo '<div class="note-header-left"><div id="lastupdated'.$row['id'].'" class="lastupdated">'.formatDateTime(strtotime($row['updated'])).'</div></div>';
                // Barre d’édition centrée
                if (!$is_mobile) {
                    echo '<div class="note-edit-toolbar">';
                    echo '<button type="button" class="toolbar-btn" title="Gras" onclick="document.execCommand(\'bold\')"><i class="fas fa-bold"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Italique" onclick="document.execCommand(\'italic\')"><i class="fas fa-italic"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Souligné" onclick="document.execCommand(\'underline\')"><i class="fas fa-underline"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Barré" onclick="document.execCommand(\'strikeThrough\')"><i class="fas fa-strikethrough"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Lien" onclick="addLinkToNote()"><i class="fas fa-link"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Supprimer le lien" onclick="document.execCommand(\'unlink\')"><i class="fas fa-unlink"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Couleur texte" onclick="toggleRedColor()"><i class="fas fa-palette" style="color:#ff2222;"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Surlignage" onclick="toggleYellowHighlight()"><i class="fas fa-fill-drip" style="color:#ffe066;"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Liste à puces" onclick="document.execCommand(\'insertUnorderedList\')"><i class="fas fa-list-ul"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Liste numérotée" onclick="document.execCommand(\'insertOrderedList\')"><i class="fas fa-list-ol"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Taille police" onclick="changeFontSize()"><i class="fas fa-text-height"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Bloc code" onclick="toggleCodeBlock()"><i class="fas fa-code"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Effacer formatage" onclick="document.execCommand(\'removeFormat\')"><i class="fas fa-eraser"></i></button>';
                    // Bouton séparateur
                    echo '<button type="button" class="toolbar-btn" title="Ajouter un séparateur" onclick="insertSeparator()"><i class="fas fa-minus"></i></button>';
                    // Boutons action note (enregistrer, exporter, info, supprimer)
                    echo '<button type="button" class="toolbar-btn" title="Enregistrer la note" onclick="saveFocusedNoteJS()"><i class="fas fa-save"></i></button>';
                    echo '<a href="'.$filename.'" download="'.$title.'" class="toolbar-btn" title="Exporter la note"><i class="fas fa-download"></i></a>';
                    echo '<button type="button" class="toolbar-btn" title="Infos note" onclick="alert(\'Note file: '.$row['id'].'.html\\nCreated on: '.formatDateTime(strtotime($row['created'])).'\\nLast updated: '.formatDateTime(strtotime($row['updated'])).'\')"><i class="fas fa-info-circle"></i></button>';
                    echo '<button type="button" class="toolbar-btn" title="Supprimer la note" onclick="deleteNote(\''.$row['id'].'\')"><i class="fas fa-trash"></i></button>';
                    echo '</div>';
                }
                echo '</div>';
                // Ligne 2 : icône tag + tags
                echo '<div class="note-tags-row">';
                echo '<span class="fa fa-tag icon_tag"></span>';
                echo '<span class="name_tags">'
                    .'<input class="add-margin" size="70px" autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="Tags?" onfocus="updateidtags(this);" id="tags'.$row['id'].'" type="text" placeholder="Tags ?" value="'.htmlspecialchars(str_replace(',', ' ', $row['tags']), ENT_QUOTES).'"'.($is_mobile ? ' readonly' : '').'/>'
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
    
<script src="js/script.js"></script>
<script>
// Fonctions pour les boutons d’édition (reprennent la logique Popline)
function addLinkToNote() {
  const url = prompt('Entrer l’URL du lien:', 'https://');
  if (url) document.execCommand('createLink', false, url);
}
function toggleRedColor() {
  document.execCommand('styleWithCSS', false, true);
  const sel = window.getSelection();
  if (sel.rangeCount > 0) {
    const range = sel.getRangeAt(0);
    let allRed = true, hasText = false;
    const treeWalker = document.createTreeWalker(range.commonAncestorContainer, NodeFilter.SHOW_TEXT, {
      acceptNode: function(node) {
        if (!range.intersectsNode(node)) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });
    let node = treeWalker.currentNode;
    while(node) {
      if (node.nodeType === 3 && node.nodeValue.trim() !== '') {
        hasText = true;
        let parent = node.parentNode;
        let color = '';
        if (parent && parent.style && parent.style.color) color = parent.style.color.replace(/\s/g, '').toLowerCase();
        if (color !== '#ff2222' && color !== 'rgb(255,34,34)') allRed = false;
      }
      node = treeWalker.nextNode();
    }
    if (hasText && allRed) {
      document.execCommand('foreColor', false, 'black');
    } else {
      document.execCommand('foreColor', false, '#ff2222');
    }
  }
  document.execCommand('styleWithCSS', false, false);
}
function toggleYellowHighlight() {
  const sel = window.getSelection();
  if (sel.rangeCount > 0) {
    const range = sel.getRangeAt(0);
    let allYellow = true, hasText = false;
    const treeWalker = document.createTreeWalker(range.commonAncestorContainer, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT, {
      acceptNode: function(node) {
        if (!range.intersectsNode(node)) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });
    let node = treeWalker.currentNode;
    while(node) {
      if (node.nodeType === 3 && node.nodeValue.trim() !== '') {
        hasText = true;
        let parent = node.parentNode;
        let bg = '';
        if (parent && parent.style && parent.style.backgroundColor) bg = parent.style.backgroundColor.replace(/\s/g, '').toLowerCase();
        if (bg !== '#ffe066' && bg !== 'rgb(255,224,102)') allYellow = false;
      }
      node = treeWalker.nextNode();
    }
    document.execCommand('styleWithCSS', false, true);
    if (hasText && allYellow) {
      document.execCommand('hiliteColor', false, 'inherit');
    } else {
      document.execCommand('hiliteColor', false, '#ffe066');
    }
    document.execCommand('styleWithCSS', false, false);
  }
}
function changeFontSize() {
  const size = prompt('Taille de police (1-7):', '3');
  if (size) document.execCommand('fontSize', false, size);
}
function toggleCodeBlock() {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  let container = range.commonAncestorContainer;
  if (container.nodeType === 3) container = container.parentNode;
  // Si déjà dans un bloc code, on le retire
  if (container.closest && container.closest('pre')) {
    const pre = container.closest('pre');
    // Remplacer le <pre> par son contenu sous forme de <div> et <br>
    const text = pre.textContent;
    const div = document.createElement('div');
    const lines = text.split('\n');
    lines.forEach((line, index) => {
      if (index > 0) div.appendChild(document.createElement('br'));
      if (line.length > 0) div.appendChild(document.createTextNode(line));
    });
    pre.parentNode.replaceChild(div, pre);
    return;
  }
  // Sinon, transformer la sélection en bloc code
  if (sel.isCollapsed) return; // rien à faire si pas de sélection
  // On clone le contenu sélectionné
  const fragment = range.cloneContents();
  if (!fragment.textContent.trim()) return;
  let content = '';
  // On récupère le texte avec sa structure
  const processNode = (node) => {
    if (node.nodeType === Node.TEXT_NODE) {
      content += node.textContent;
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      if (node.nodeName ==='P' || node.nodeName === 'DIV') {
        if (!content.endsWith('\n')) content += '\n';
      } else if (node.nodeName === 'BR') {
        content += '\n';
      }
      for (const child of node.childNodes) processNode(child);
    }
  };
  for (const node of fragment.childNodes) processNode(node);
  content = content.replace(/\n{3,}/g, '\n\n');
  // Crée le bloc <pre> avec style inline pour garantir l'apparence
  const pre = document.createElement('pre');
  pre.textContent = content;
  pre.style.background = '#F7F6F3';
  pre.style.color = 'rgb(55, 53, 47)';
  pre.style.padding = '34px 16px 32px 32px';
  pre.style.borderRadius = '4px';
  pre.style.fontFamily = 'Consolas, monospace';
  pre.style.fontSize = '90%';
  pre.style.margin = '1em 0';
  // Remplace la sélection par le bloc code
  range.deleteContents();
  range.insertNode(pre);
  // Place le curseur après le bloc code
  sel.removeAllRanges();
  const newRange = document.createRange();
  newRange.setStartAfter(pre);
  newRange.setEndAfter(pre);
  sel.addRange(newRange);
}
</script>
<script>
// Fonction pour insérer un séparateur à la position du curseur dans la note active
function insertSeparator() {
  const sel = window.getSelection();
  if (!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  // Crée un élément <hr>
  const hr = document.createElement('hr');
  hr.style.border = 'none';
  hr.style.borderTop = '1px solid #bbb';
  hr.style.margin = '12px 0';
  // Insère le <hr> à la position du curseur d'édition
  if (!range.collapsed) {
    range.deleteContents();
  }
  range.insertNode(hr);
  // Place le curseur après le <hr>
  range.setStartAfter(hr);
  range.setEndAfter(hr);
  sel.removeAllRanges();
  sel.addRange(range);
  // Déclenche un événement input sur la noteentry active
  let container = range.commonAncestorContainer;
  if (container.nodeType === 3) container = container.parentNode;
  const noteentry = container.closest && container.closest('.noteentry');
  if (noteentry) noteentry.dispatchEvent(new Event('input', {bubbles:true}));
}
</script>
</html>