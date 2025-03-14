<?php
@ob_start();
session_start(); 
?>
<?php
    require 'config.php';
    include 'functions.php';
    require 'config.php';
    $pass=$_SESSION['pass'];
    $search = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');
    $tags_search_from_list = $_GET['tags_search_from_list'];  // if we clicked on a tag in the tag list
    $tags_search = isset($_POST['tags_search']) ? $_POST['tags_search'] : (isset($_GET['tags_search']) ? $_GET['tags_search'] : '');
    $note = $_GET['note'];

    $limit_display_right = 1;
    $limit_display_right_all_notes = 1;


    if (isset($tags_search_from_list))
    {
        $tags_search = $tags_search_from_list;
    }
    
	include 'db_connect.php';	
    /*$updateQuery = "UPDATE entries SET tags =  REPLACE(tags,' ',',')";
    $update = $con->query($updateQuery);*/
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
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css" />
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
    <style>
        .tagify{
            border: 0;
            --tag-bg: #F1F1F1;
        }
    </style>
	<script>
		var app_pass = '<?php echo APP_PASSWORD;?>';
	</script>
    
</head>

<body>   

    <!-- Notification popup -->
    <div id="notificationPopup" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%, -50%); padding:20px; color: #fff; background-color:#007DB8; border:1px solid #fff; z-index:1000; font-size: 1.5em; text-align: center;"></div>
    
    <!-- LEFT COLUMN -->	
    <div id="left_col">

    <!-- Depending on the cases, we create the queries. -->  
        
    <?php
        
        if($tags_search!='') // It's a search within the tags, so we only want to display notes that contain the tags.
        {
            // Break the string into individual words.
            $tags_search_terms = explode(' ', $tags_search);

            // Construct the SQL query for left column.
            $query_left = 'SELECT heading FROM entries WHERE trash = 0';
            foreach ($tags_search_terms as $tag_term) {
                $query_left .= ' AND tags LIKE \'%' . htmlspecialchars($tag_term, ENT_QUOTES) . '%\'';
            }
            $query_left .= ' ORDER BY updated DESC';

            // Construct the SQL query for right column.
            $query_right = 'SELECT * FROM entries WHERE trash = 0';
            foreach ($tags_search_terms as $tag_term) {
                $query_right .= ' AND tags LIKE \'%' . htmlspecialchars($tag_term, ENT_QUOTES) . '%\'';
            }
            $query_right .= ' ORDER BY updated DESC LIMIT ' . $limit_display_right_all_notes;
        }
        else // Otherwise, it's a search within the notes, so we only want to display notes that contain the searched words.
        {
            // Break the string into individual words.
            $search_terms = explode(' ', $search);

            // Construct the SQL query for left column.
            $query_left = 'SELECT heading FROM entries WHERE trash = 0';
            foreach ($search_terms as $term) {
                $query_left .= ' AND (heading LIKE \'%' . htmlspecialchars($term, ENT_QUOTES) . '%\' OR entry LIKE \'%' . htmlspecialchars($term, ENT_QUOTES) . '%\')';
            }
            $query_left .= ' ORDER BY updated DESC';

            // Construct the SQL query for right column.
            $query_right = 'SELECT * FROM entries WHERE trash = 0';
            foreach ($search_terms as $term) {
                $query_right .= ' AND (heading LIKE \'%' . htmlspecialchars($term, ENT_QUOTES) . '%\' OR entry LIKE \'%' . htmlspecialchars($term, ENT_QUOTES) . '%\')';
            }
            $query_right .= ' ORDER BY updated DESC LIMIT ' . $limit_display_right_all_notes;
        }
        
    ?>
    
    <!-- MENU -->

    <div class="containbuttons">
        <div class="newbutton" onclick="newnote();"><span style="text-align:center;"><span title="Create a new note" class="fas fa-file-medical"></span></span></div>
        <div class="list_tags" onclick="window.location = 'listtags.php';"><span style="text-align:center;"><span title="List the tags" class="fas fa-tags"></span></span></div>       
        <div class="trashnotebutton" onclick="window.location = 'trash.php';"><span style="text-align:center;"><span title="Go to the trash" class="fas fa-trash-alt"></span></span></div>

    </div> 
        
    <hr><br>
         	
    <?php
  
        if($note!='') // If the note is not empty, it means we have just clicked on a note.
        {          
	        $note = str_replace("&#039;", "'", $note); // We replace the single quotes `'` because they are stored in the database as `&#039;`.
	        $note = str_replace("&quot;", "\"", $note); // We replace the single quotes `"` because they are stored in the database as `&quot;`.	
            $query_right = 'SELECT * FROM entries WHERE trash = 0 AND (heading = \''.htmlspecialchars($note,ENT_QUOTES).'\')';     
        }
		
        $res_query_left = $con->query($query_left);
 		
        while($row1 = mysqli_fetch_array($res_query_left, MYSQLI_ASSOC)) 
        {       
            // Check if note is selected
            $isSelected = ($note === $row1["heading"]) ? 'selected-note' : '';

            if($tags_search != '') // If we have searched within the tags, we want to display the notes that contain those tags.
            {
                echo "<form action=index.php><input type=hidden name=note>                        
                <a class='links_arbo_left  $isSelected' href='index.php?note=" . urlencode($row1["heading"]) . "&tags_search=" . urlencode("$tags_search") . "' style='text-decoration:none; color:#333'><div id=icon_notes; style='padding-right: 7px;padding-left: 8px; font-size:11px; color:#007DB8;' class='far fa-file'></div>" . $row1["heading"] . "</a>
                </form>";
            }

            if($search != '') // If we have searched within the notes, we want to display the notes that contain the searched words.
            {
                echo "<form action=index.php><input type=hidden name=note>                        
                <a class='links_arbo_left  $isSelected' href='index.php?note=" . urlencode($row1["heading"]) . "&search=" . urlencode("$search") . "' style='text-decoration:none; color:#333'><div id=icon_notes; style='padding-right: 7px;padding-left: 8px; font-size:11px; color:#007DB8;' class='far fa-file'></div>" . $row1["heading"] . "</a>
                </form>";
            }

            if($tags_search == '' && $search == '') // If we were viewing all notes and click on a note
            {
                echo "<form action=index.php><input type=hidden name=note>                        
                <a class='links_arbo_left  $isSelected' href='index.php?note=" . urlencode($row1["heading"]) . "' style='text-decoration:none; color:#333'><div id=icon_notes; style='padding-right: 7px;padding-left: 8px; font-size:11px; color:#007DB8;' class='far fa-file'></div>" . $row1["heading"] . "</a>
                </form>";
            }

            echo "<div id=pxbetweennotes; style='height: 0px'></div>";  // To adjust the distance between the notes	 
        }
        		 
    ?>
    </div>
	
    <!-- RIGHT COLUMN -->	
    <div id="right_col" style="background:#FFFFF;">
    
        <!-- Search -->

        <div class="contains_forms_search">
			<form class="form_search" action="index.php" method="POST">          
				<div class="right-inner-addon">
					<i class="fas fa-search icon_grey"></i>
                    <input autocomplete="off" autocapitalize="off" spellcheck="false" id="note-search" type="search" name="search" class="search form-control" placeholder="Search for one or more words within the notes" onfocus="updateidsearch(this);" value="<?php echo htmlspecialchars($search); ?>"/>
                </div>
			</form>

            <?php
            if($search!='') // We arrive here after a search; it will display the search results along with a small cross icon to exit the search.
            { 
                echo '<span style="cursor:pointer;font-weight:00;" onclick="window.location=\'index.php\'"><span style="color:#007DB8" class="fa fa-times"></span></span>';
            }
            else if($tags_search!='') // We arrive here after a tag search; it will display the search results along with a small cross icon to exit the search.
            {
                echo '<span style="cursor:pointer;font-weight:700;" onclick="window.location=\'index.php\'"><span style="color:#007DB8" class="fa fa-times"></span></span>';
            }
			else // Otherwise, it means it's not a search, so we simply display a message indicating that the first X notes have been displayed on the right.
			{
				//echo '<br><div style="text-align:center; font-weight:300;">Only the '.$limit_display_right.' first notes are displayed below.</span></span></div><br>';
			}
			?>

			<form class="form_search_tags" action="index.php" method="POST">          
				<div class="right-inner-addon">
					<i class="fas fa-tags icon_grey"></i>
                    <input autocomplete="off" autocapitalize="off" spellcheck="false" id="tags-search" type="search" name="tags_search" class="search form-control" placeholder="Search for one or more words in the tags" onfocus="updateidsearch(this);" value="<?php echo htmlspecialchars($tags_search); ?>"/>
                </div>  
			</form>                 
		</div> 
        
        <?php        
			
			// Right-side list based on the query created earlier //		
		
            $res_right = $con->query($query_right);
           
            while($row = mysqli_fetch_array($res_right, MYSQLI_ASSOC))
            {
            
                $filename = "entries/".$row["id"].".html";
                $title = $row['heading'];             
                $handle = fopen($filename, "r");
                $contents = fread($handle, filesize($filename));
                $entryfinal = $contents;
                fclose($handle);
           
                // Display the notes
                    echo '<div id="note'.$row['id'].'" class="notecard">
                    <div class="innernote">';

                        // Don't display trash icon if note is the demo note
                        if ($row['heading'] != "Welcome to CnoT") {
                            echo '<span style="cursor:pointer" title="Delete this note" onclick="deleteNote(\''.$row['id'].'\')" class="fas fa-trash pull-right icon_trash"></span>';
                        }

                        echo '<span style="cursor:pointer" title="Show note number" onclick="alert(\'Note file: '.$row['id'].'.html\nCreated on: '.formatDateTime(strtotime($row['created'])).'\nLast updated: '.formatDateTime(strtotime($row['updated'])).'\')" class="fas fa-info-circle pull-right icon_info"></span>';

                        echo '<a href="'.$filename.'" download="'.$title.'"><span style="cursor:pointer" title="Export this note" class="fas fa-download pull-right icon_download"></span></a>';

                        // Don't display save icon and last updated text if note is the demo note
                        if ($row['heading'] != "Welcome to CnoT") {
                            echo '<span style="cursor:pointer" title="Save this note" onclick="saveFocusedNoteJS()" class="fas fa-save pull-right icon_save"></span>';
                            echo '<div id="lastupdated'.$row['id'].'" class="lastupdated">'.formatDateTime(strtotime($row['updated'])).'</div>';
                        }

                        echo '<div class="contain_doss_tags">

                            <!--<span>Note '.$row['id'].' </span>-->

                            <div class="icon_tag" style="margin-left: 10px;"><span style="text-align:center; font-size:12px;" class="fa fa-tag"></div>
                            <div class="name_tags"><span><input class="add-margin-left tag-clsss" size="150px" autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="Tags" data-id="'.$row['id'].'" id="tags'.$row['id'].'" type="text" placeholder="Tags ?" value="'.$row['tags'].'"></input></span></div>
                        </div>

                        <!--<hr>-->
                        <!--<hr>-->

                    <h4><input class="css-title" autocomplete="off" autocapitalize="off" spellcheck="false" onfocus="updateidhead(this);" id="inp'.$row['id'].'" type="text" placeholder="Title ?" value="'.$row['heading'].'"></input></h4>

                        <div class="noteentry" autocomplete="off" autocapitalize="off" spellcheck="false" onload="initials(this);" onfocus="updateident(this);" id="entry'.$row['id'].'" data-ph="Enter text or paste images" contenteditable="true">'.$entryfinal.'</div>

                        <div style="height:30px;"></div>
                    </div>
                    </div>';
            }
        ?>        
    </div>
</body>
	
<!-- Do not place this block at the top, otherwise Popline will no longer work -->
<script src="js/script.js"></script>
<script> $(".noteentry").popline(); </script>  <!-- When selecting text, it displays the floating editing menu in the .noteentry area (i.e., note content) above / It must be 'contenteditable="true"' -->
<script src="https://unpkg.com/@yaireo/tagify"></script>
<script>
   
    $('.tag-clsss').each(function () {
        var id = $(this).data('id');
        const tagifyInstance = new Tagify(document.getElementById('tags' + id), {
            placeholder: "Type something",
        });
        tagifyInstance.on('add', (e) => {
            noteid = tagifyInstance.DOM.originalInput.closest('div.name_tags').querySelector('input').dataset.id;
            update();
        });
        tagifyInstance.on('remove', (e) => {
            noteid = tagifyInstance.DOM.originalInput.closest('div.name_tags').querySelector('input').dataset.id;
            update();
        });
        tagifyInstance.on('input', (e) => {
            const searchTerm = e.detail.value;
            tagifyInstance.whitelist = null;
            tagifyInstance.loading(true);
            mockAjax(searchTerm)
                .then(function(result){
                    tagifyInstance.settings.whitelist = result.concat(tagifyInstance.value)
                    tagifyInstance
                        .loading(false)
                        .dropdown.show(e.detail.value);
                })
                .catch(err => tagifyInstance.dropdown.hide())
        });
    })
    //just a moment
    
    var mockAjax = function(searchTerm) {
        return new Promise(function(resolve, reject) {
            fetch('tags.php?search='+ encodeURIComponent(searchTerm))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => resolve(data))
                .catch(err => reject(err));
        });
    };
</script>
</html>