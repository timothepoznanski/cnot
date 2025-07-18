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
	<link type="text/css" rel="stylesheet" href="css/style.css"/>	
	<link rel="stylesheet" href="css/font-awesome.css" />
	<link type="text/css" rel="stylesheet" href="css/style-mobile.css"/>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/style-mobile.css" />

</head>
<body class="trash-page">
	<h2 style="text-align:center; font-weight:500; color: #333; margin-top: 40px;">Trash</h2>
	<?php
		if(!empty($search))
		{
			echo '<h4 style="text-align:center; font-weight:300;"> Results for '.$search.'. <span style="cursor:pointer;font-weight:700;" onclick="window.location=\'trash.php\'"><span class="fas fa-times"></span></span></h4>';
		}
	?>
	<form action="trash.php" method="POST">
		<h5 style="text-align:center; font-weight:300;"><input autocomplete="off" onfocus="updateidhead(this);" class="searchtrash" style="background:#f8f8f8; text-align:center; width:25%; border: 1px solid #ddd; border-radius: 6px; padding: 8px;" name="search" id="search" type="text" placeholder="Search for notes in the trash by clicking here" value="<?php echo $search; ?>"></h5>
	</form>
	
	<div id="containbuttonsstrash">
		<div class="backbutton" onclick="window.location = 'index.php';" style="margin-left: 30px;">
			<span style="text-align:center; font-size:20px; color:#007DB8;">
				<span title="Back to notes" class="fas fa-arrow-circle-left"></span>
			</span>
		</div>
		<div class="emptytrash" onclick="emptytrash();"><span style="text-align:center; font-size:20px; color:#007DB8;"><span title="Empty the trash" class="fa fa-trash-alt"></span></span></div>
	</div>
	
	<br>
	<?php
		$search = trim($_POST['search'] ?? $_GET['search'] ?? '');
		$search_condition = $search ? " AND (heading LIKE '%$search%' OR entry LIKE '%$search%')" : '';
		$res = $con->query("SELECT * FROM entries WHERE trash = 1$search_condition ORDER BY updated DESC LIMIT 50");
		
		if ($res && $res->num_rows > 0) {
		while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))		{
			$id = $row['id'];
			$filename = "./entries/" . $id . ".html";
			$entryfinal = file_exists($filename) ? file_get_contents($filename) : '';
			$heading = $row['heading'];
			$updated = formatDateTime(strtotime($row['updated']));
			
			echo '<div id="note'.$id.'" class="notecard">
			<div class="innernote">
				<div class="trash-action-icons">
					<span title="Restore this note" onclick="putBack(\''.$id.'\')" class="fa fa-trash-restore-alt icon_restore_trash"></span>
					<span title="Permanently delete" onclick="deletePermanent(\''.$id.'\')" class="fas fa-trash icon_trash_trash"></span>
				</div>
				<div id="lastupdated'.$id.'" class="lastupdated">Last modified on '.$updated.'</div>
				<h3><input id="inp'.$id.'" class="css-title" type="text" placeholder="Title ?" value="'.htmlspecialchars($heading, ENT_QUOTES).'"></input> </h3>
				<hr>
				<div class="noteentry" onload="initials(this);" id="entry'.$id.'" data-ph="Enter text or images here" contenteditable="true">'.$entryfinal.'</div>
				<div style="height:30px;"></div>
			</div>
			</div>';
		}
		} else {
			echo '<div style="text-align:center; color:#666; margin-top:50px;"><h4>No notes found in trash.</h4></div>';
		}
	?>
</body>
<script src="js/script.js"></script>
</html>
