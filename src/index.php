
<?php
// Détection mobile par user agent
$is_mobile = false;
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_mobile = preg_match('/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/', $user_agent);
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
    <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
    <!-- <link href='https://fonts.googleapis.com/css?family=Roboto:100,100italic,300,300italic,400,400italic,500,500italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'> -->
    <link type="text/css" rel="stylesheet" href="css/style.css"/> 
    <link rel="stylesheet" href="css/font-awesome.css" />
    <link rel="stylesheet" type="text/css" href="css/page.css" />
    <!-- jQuery Popline library for the bar that appears when selecting text in a note / see in "js/plugins" -->
    <link rel="stylesheet" type="text/css" href="css/popline.css" /> <!-- CSS for the Popline -->
    <!-- Remove the lines for the functions that we do not want to appear in the bar. -->
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.popline.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.link.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.decoration.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.blockquote.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.blockcode.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.list.js"></script>
    <!--<script type="text/javascript" src="js/plugins/jquery.popline.justify.js"></script> -->
    <!--<script type="text/javascript" src="js/plugins/jquery.popline.blockformat.js"></script> -->
    <script type="text/javascript" src="js/plugins/jquery.popline.social.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.textcolor.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.backgroundcolor.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.popline.fontsize.js"></script>
</head>

<body<?php echo ($note != '') ? ' class="note-selected"' : ''; ?>>   



    <!-- Notification popup -->
    <div id="notificationPopup" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%, -50%); padding:20px; color: #fff; background-color:#007DB8; border:1px solid #fff; z-index:1000; font-size: 1.5em; text-align: center;"></div>
    
    <!-- LEFT COLUMN -->	
    <div id="left_col">

        <!-- Search forms for mobile - displayed at top of left column -->
        <div class="mobile-search-container">
            <div class="contains_forms_search">
                <form class="form_search" action="index.php" method="POST" style="display:inline-block;width:48%;vertical-align:top;">          
                    <div class="right-inner-addon">
                        <i class="fas fa-search icon_grey"></i>
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="note-search-left" type="search" name="search" class="search form-control" placeholder="Search for one or more words within the notes" value="<?php echo $search; ?>" style="width: 230px; max-width: 100%; min-width: 120px;"/>
                    </div>
                </form>
                <form class="form_search_tags" action="index.php" method="POST" style="display:inline-block;width:48%;vertical-align:top;">          
                    <div class="right-inner-addon">
                        <i class="fas fa-tags icon_grey"></i>
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="tags-search-left" type="search" name="tags_search" class="search form-control" placeholder="Search for one or more words in the tags" value="<?php echo $tags_search; ?>" style="width: 210px; max-width: 100%; min-width: 120px;"/>
                    </div>  
                </form>
            </div>
        </div>

    <!-- Depending on the cases, we create the queries. -->  
        
    <?php
    // Build search conditions
    $search_condition = '';
    if ($tags_search) {
        $terms = explode(' ', trim($tags_search));
        foreach ($terms as $term) {
            if (!empty(trim($term))) {
                $search_condition .= " AND tags LIKE '%" . trim($term) . "%'";
            }
        }
    } elseif ($search) {
        $terms = explode(' ', trim($search));
        foreach ($terms as $term) {
            if (!empty(trim($term))) {
                $search_condition .= " AND (heading LIKE '%" . trim($term) . "%' OR entry LIKE '%" . trim($term) . "%')";
            }
        }
    }
    
    $query_left = "SELECT heading FROM entries WHERE trash = 0$search_condition ORDER BY updated DESC";
    $query_right = "SELECT * FROM entries WHERE trash = 0$search_condition ORDER BY updated DESC LIMIT 1";
    ?>
    
    <!-- MENU -->

    <?php if (!$is_mobile): ?>
    <div class="containbuttons">
        <div class="newbutton" onclick="newnote();"><span style="text-align:center;"><span title="Create a new note" class="fas fa-file-medical"></span></span></div>
        <div class="list_tags" onclick="window.location = 'listtags.php';"><span style="text-align:center;"><span title="List the tags" class="fas fa-tags"></span></span></div>
        <!-- Button to export all notes -->
        <div class="exportAllButton" onclick="startDownload();">
            <span style="text-align:center;">
                <span title="Export all notes as a zip file for offline viewing" class="fas fa-download"></span>
            </span>
        </div>
        <!-- Download popup -->
        <div id="downloadPopup" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%, -50%); padding:20px; color: #FFF; background-color:#007DB8; border:1px solid #FFF; z-index:1000;font-size: 1.5em;">
           Please wait while the archive is being created...
        </div>
        <script>
            function startDownload() {
                // Show the popup
                document.getElementById('downloadPopup').style.display = 'block';
                // Start the download
                window.location = 'exportEntries.php';
                // Hide the popup after a certain amount of time
                setTimeout(function() {
                    document.getElementById('downloadPopup').style.display = 'none';
                }, 4000); // Hide after 4 seconds
            }
        </script>
        <div class="trashnotebutton" onclick="window.location = 'trash.php';"><span style="text-align:center;"><span title="Go to the trash" class="fas fa-trash-alt"></span></span></div>
        <?php
        if($search != '' || $tags_search != '') {
            // Use a FontAwesome solid times-circle for better alignment and size
            echo '<div class="newbutton" style="cursor:pointer;margin-left:8px;" onclick="window.location=\'index.php\'" title="Clear search">'
                .'<span style="color:#d32f2f;font-size:22px;display:flex;align-items:center;justify-content:center;" class="fas fa-times-circle"></span>'
                .'</div>';
        }
        ?>
    </div>
    <?php endif; ?>

    <?php if ($is_mobile && $note != ''): ?>
    <div class="mobile-menu-bar" style="display:flex;justify-content:center;margin:10px 0;">
        <div class="btn-menu" style="background:#007DB8;color:#fff;border-radius:8px;padding:7px 16px;font-size:1.1em;display:flex;align-items:center;gap:8px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.08);" onclick="window.location='index.php'" title="Retour à la liste des notes">
            <span class="fa fa-home" style="font-size:1.2em;"></span>
        </div>
    </div>
    <script>
    // Corrige le comportement du bouton maison pour supprimer tous les paramètres d'URL
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.querySelector('.mobile-menu-bar .btn-menu');
        if(btn) {
            btn.onclick = function(e) {
                e.preventDefault();
                window.location.href = 'index.php';
            };
        }
    });
    </script>
    <?php endif; ?>
        
    <hr><br>
            
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
            $link_params = $tags_search ? "&tags_search=" . urlencode($tags_search) : ($search ? "&search=" . urlencode($search) : '');
            
            echo "<form action=index.php><input type=hidden name=note>                        
            <a class='links_arbo_left  $isSelected' href='index.php?note=" . urlencode($row1["heading"]) . $link_params . "' style='text-decoration:none; color:#333'><div id=icon_notes; style='padding-right: 7px;padding-left: 8px; font-size:11px; color:#007DB8;' class='far fa-file'></div>" . ($row1["heading"] ?: 'Untitled note') . "</a>
            </form>";

            echo "<div id=pxbetweennotes; style='height: 0px'></div>";
        }
                 
    ?>
    </div>
    
    <!-- RIGHT COLUMN -->	
    <div id="right_col" style="background:#FFFFF;">
    
        <!-- Search -->

        <div class="contains_forms_search">
            <div style="display:flex;align-items:center;gap:12px;justify-content:center;">
                <form class="form_search" action="index.php" method="POST" style="display:inline-block;vertical-align:top;">          
                    <div class="right-inner-addon">
                        <i class="fas fa-search icon_grey"></i>
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="note-search" type="search" name="search" class="search form-control" placeholder="Search for one or more words within the notes" onfocus="updateidsearch(this);" value="<?php echo $search; ?>" style="width:350px;"/>
                    </div>
                </form>
                <form class="form_search_tags" action="index.php" method="POST" style="display:inline-block;vertical-align:top;">          
                    <div class="right-inner-addon">
                        <i class="fas fa-tags icon_grey"></i>
                        <input autocomplete="off" autocapitalize="off" spellcheck="false" id="tags-search" type="search" name="tags_search" class="search form-control" placeholder="Search for one or more words in the tags" onfocus="updateidsearch(this);" value="<?php echo $tags_search; ?>" style="width:350px;"/>
                    </div>  
                </form>
            </div>
        </div> 
        
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
                    echo '<span style="cursor:pointer" title="Delete this note" onclick="deleteNote(\''.$row['id'].'\')" class="fas fa-trash icon_trash"></span>';
                    echo '<span style="cursor:pointer" title="Show note number" onclick="alert(\'Note file: '.$row['id'].'.html\nCreated on: '.formatDateTime(strtotime($row['created'])).'\nLast updated: '.formatDateTime(strtotime($row['updated'])).'\')" class="fas fa-info-circle icon_info"></span>';
                    echo '<a href="'.$filename.'" download="'.$title.'"><span style="cursor:pointer" title="Export this note" class="fas fa-download icon_download"></span></a>';
                    echo '<span style="cursor:pointer" title="Save this note" onclick="saveFocusedNoteJS()" class="fas fa-save icon_save"></span>';
                    echo '</div>';
                } else {
                    // Bouton maison sur mobile, à la place des actions
                    echo '<div class="note-icons-mobile">';
                    echo '<span style="background:#007DB8;color:#fff;border-radius:7px;padding:4px 11px;font-size:0.95em;display:inline-flex;align-items:center;gap:6px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin:0 auto;" onclick="window.location=\'index.php\'" title="Retour à la liste des notes">';
                    echo '<span class="fa fa-home" style="font-size:1em;"></span>';
                    echo '</span>';
                    echo '</div>';
                }
                echo '</div>';
                // Ligne 2 : icône tag + tags
                echo '<div class="note-tags-row" style="display:flex;align-items:center;gap:8px;overflow:hidden;margin-top:12px;">';
                echo '<span class="fa fa-tag icon_tag" style="font-size:15px;margin-right:8px;flex-shrink:0;"></span>';
                echo '<span class="name_tags" style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">'
                    .'<input class="add-margin-left" style="width:100%;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" size="70px" autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="Tags" onfocus="updateidtags(this);" id="tags'.$row['id'].'" type="text" placeholder="Tags ?" value="'.htmlspecialchars(str_replace(',', ' ', $row['tags']), ENT_QUOTES).'"'.($is_mobile ? ' readonly' : '').'/>'
                .'</span>';
                echo '</div>';
                // Titre
                echo '<h4><input class="css-title" autocomplete="off" autocapitalize="off" spellcheck="false" onfocus="updateidhead(this);" id="inp'.$row['id'].'" type="text" placeholder="Title ?" value="'.htmlspecialchars($row['heading'] ?: 'Untitled note', ENT_QUOTES).'"'.($is_mobile ? ' readonly' : '').'/></h4>';
                // Contenu de la note
                echo '<div class="noteentry" autocomplete="off" autocapitalize="off" spellcheck="false" onload="initials(this);" onfocus="updateident(this);" id="entry'.$row['id'].'" data-ph="Enter text or paste images" contenteditable="'.($is_mobile ? 'false' : 'true').'">'.$entryfinal.'</div>';
                echo '<div style="height:30px;"></div>';
                echo '</div>';
                echo '</div>';
            }
        ?>        
    </div>
</body>
    
<!-- Do not place this block at the top, otherwise Popline will no longer work -->
<script src="js/script.js"></script>
<script> $(".noteentry").popline(); </script>  <!-- When selecting text, it displays the floating editing menu in the .noteentry area (i.e., note content) above / It must be 'contenteditable="true"' -->
</html>