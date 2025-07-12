<?php
@ob_start();
?>
<?php
	include 'functions.php';
	require 'config.php';
	include 'db_connect.php';	
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
	<link rel="stylesheet" type="text/css" href="css/popline.css" />
	<link rel="stylesheet" type="text/css" href="css/page.css" />

</head>
<body style="background:#007DB8;">
	<h2 style="text-align:center; font-weight:500; color: #fff">Trash</h2>
	<?php
		if(!empty($search))
		{
			echo '<h4 style="text-align:center; font-weight:300;"> Results for '.htmlspecialchars($search, ENT_QUOTES, 'UTF-8').'. <span style="cursor:pointer;font-weight:700;" onclick="window.location=\'trash.php\'"><span class="fas fa-times"></span></span></h4>';
		}
	?>
	<form action="trash.php" method="POST">
		<h5 style="text-align:center; font-weight:300;"><input autocomplete="off" onfocus="updateidhead(this);" class="searchtrash" style="background:inherit; text-align:center; width:25%;" name="search" id="search" type="text" placeholder="Search for notes in the trash by clicking here" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"></h5>
	</form>
    
    <div id="containbuttonsstrash">
		<div class="backbutton" onclick="window.location = 'index.php';" style="margin-left: 30px;">
			<span style="text-align:center; font-size:20px; color:white;">
				<span title="Back to notes" class="fas fa-arrow-circle-left"></span>
			</span>
		</div>
        <div class="emptytrash" onclick="emptytrash();"><span style="text-align:center; font-size:20px; color:white;"><span title="Empty the trash" class="fa fa-trash-alt"></span></span></div>
    </div>
    
	<br>
	<?php
		// Validation et sécurisation de la recherche
		$search = trim($_POST['search'] ?? $_GET['search'] ?? '');
		
		// Initialiser $res pour éviter les erreurs
		$res = null;
		
		if (!empty($search)) {
			// Requête préparée pour éviter l'injection SQL
			$stmt = $con->prepare('SELECT * FROM entries WHERE trash = 1 AND (heading LIKE ? OR entry LIKE ?) ORDER BY updated DESC LIMIT 50');
			if ($stmt) {
				$search_param = '%' . $search . '%';
				$stmt->bind_param("ss", $search_param, $search_param);
				$stmt->execute();
				$res = $stmt->get_result();
				$stmt->close();
			} else {
				// Gestion d'erreur si la préparation échoue
				error_log("Erreur de préparation de requête: " . $con->error);
				$res = false;
			}
		} else {
			// Si pas de recherche, afficher toutes les notes dans la corbeille
			$res = $con->query('SELECT * FROM entries WHERE trash = 1 ORDER BY updated DESC LIMIT 50');
			if (!$res) {
				error_log("Erreur de requête: " . $con->error);
			}
		}
		
		// Vérifier que la requête a réussi
		if ($res && $res->num_rows > 0) {
		
		while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
		{
			// Validation stricte de l'ID
			$safe_id = intval($row['id']);
			if ($safe_id <= 0) {
				continue; // Ignorer les IDs invalides
			}
			
			$filename = "./entries/" . $safe_id . ".html";
			
			// Vérification de sécurité du chemin
			$realpath = realpath($filename);
			$entries_dir = realpath("./entries/");
			if (!$realpath || !$entries_dir || strpos($realpath, $entries_dir) !== 0) {
				continue; // Ignorer si le fichier n'est pas dans le dossier autorisé
			}
			
			$entryfinal = '';
			if (file_exists($filename) && is_readable($filename)) {
				$contents = file_get_contents($filename);
				if ($contents !== false) {
					// Garder le HTML pour l'affichage des images et formatage
					// Mais filtrer les scripts dangereux
					$entryfinal = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $contents);
					$entryfinal = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $entryfinal);
					$entryfinal = preg_replace('/javascript:/i', '', $entryfinal);
				}
			}
			
			// Échappement des données pour éviter XSS
			$safe_heading = htmlspecialchars(html_entity_decode($row['heading'], ENT_QUOTES), ENT_QUOTES, 'UTF-8');
			$safe_updated = htmlspecialchars(formatDateTime(strtotime($row['updated'])), ENT_QUOTES, 'UTF-8');
			
            echo '<div id="note'.$safe_id.'" class="notecard">
            <div class="innernote">
                <span title="Permanently delete" onclick="deletePermanent(\''.$safe_id.'\')" class="fas fa-trash pull-right icon_trash_trash" style="cursor: pointer;"></span>
                <span title="Restore this note" onclick="putBack(\''.$safe_id.'\')" class="fa fa-trash-restore-alt pull-right icon_restore_trash" style="margin-right:20px; cursor: pointer;"></span>
                <div id="lastupdated'.$safe_id.'" class="lastupdated">Last modified on '.$safe_updated.'</div>
                <h3><input id="inp'.$safe_id.'" type="text" placeholder="Title ?" value="'.$safe_heading.'"></input> </h3>
                <hr>
                <div class="noteentry" onload="initials(this);" id="entry'.$safe_id.'" data-ph="Enter text or images here" contenteditable="true">'.$entryfinal.'</div>
                <div style="height:30px;"></div>
            </div>
            </div>';
		}
		} else {
			echo '<div style="text-align:center; color:white; margin-top:50px;"><h4>No notes found in trash.</h4></div>';
		}
	?>
</body>
<script src="js/script.js"></script>
</html>
