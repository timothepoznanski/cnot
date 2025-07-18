<?php
@ob_start();
include 'functions.php';
require 'config.php';
include 'db_connect.php';

$search = trim($_POST['search'] ?? $_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1"/>
	<title><?php echo JOURNAL_NAME; ?> - Corbeille</title>
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link rel="stylesheet" href="css/font-awesome.css" />
	<link type="text/css" rel="stylesheet" href="css/mobile.css"/>
	<link type="text/css" rel="stylesheet" href="css/trash.css"/>
	<link type="text/css" rel="stylesheet" href="css/trash-mobile.css"/>
</head>
<body class="trash-page">
	<div class="trash-container">
		<h2 class="trash-header">Corbeille</h2>
		
		<?php if (!empty($search)): ?>
			<div class="trash-search-notice">
				Résultats pour "<?php echo htmlspecialchars($search); ?>"
				<span class="trash-clear-search" onclick="window.location='trash.php'">
					<i class="fas fa-times"></i>
				</span>
			</div>
		<?php endif; ?>
		
		<form action="trash.php" method="POST" class="trash-search-form">
			<input 
				type="text" 
				name="search" 
				id="searchInput"
				class="trash-search-input"
				placeholder="Rechercher dans la corbeille..." 
				value="<?php echo htmlspecialchars($search); ?>"
				autocomplete="off"
			>
		</form>
		
		<div class="trash-buttons-container">
			<div class="trash-button trash-back-button" onclick="window.location = 'index.php';" title="Retour aux notes">
				<i class="fas fa-arrow-circle-left trash-button-icon"></i>
			</div>
			<div class="trash-button trash-empty-button" id="emptyTrashBtn" title="Vider la corbeille">
				<i class="fa fa-trash-alt trash-button-icon"></i>
			</div>
		</div>
		
		<div class="trash-content">
		<?php
		$search_condition = $search ? " AND (heading LIKE '%$search%' OR entry LIKE '%$search%')" : '';
		$res = $con->query("SELECT * FROM entries WHERE trash = 1$search_condition ORDER BY updated DESC LIMIT 50");
		
		if ($res && $res->num_rows > 0) {
			while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
				$id = $row['id'];
				$filename = "./entries/" . $id . ".html";
				$entryfinal = file_exists($filename) ? file_get_contents($filename) : '';
				$heading = $row['heading'];
				$updated = formatDateTime(strtotime($row['updated']));
				
				echo '<div id="note'.$id.'" class="trash-notecard">
					<div class="trash-innernote">
						<div class="trash-action-icons">
							<i title="Restaurer cette note" class="fa fa-trash-restore-alt icon_restore_trash" data-noteid="'.$id.'"></i>
							<i title="Supprimer définitivement" class="fas fa-trash icon_trash_trash" data-noteid="'.$id.'"></i>
						</div>
						<div class="lastupdated">Dernière modification le '.$updated.'</div>
						<h3 class="css-title">'.htmlspecialchars($heading, ENT_QUOTES).'</h3>
						<hr>
						<div class="noteentry">'.$entryfinal.'</div>
					</div>
				</div>';
			}
		} else {
			echo '<div class="trash-no-notes">Aucune note dans la corbeille.</div>';
		}
		?>
		</div>
	</div>
	
	<script src="js/script.js"></script>
	<script src="js/trash.js"></script>
</body>
</html>
