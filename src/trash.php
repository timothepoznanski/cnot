<?php
@ob_start();
session_start();
?>
<?php
	include 'functions.php';
	require 'config.php';
	$search = $_POST['search'];
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
	<link rel="stylesheet" type="text/css" href="css/popeline.css" />
	<link rel="stylesheet" type="text/css" href="css/page.css" />
</head>
<body style="background:#007DB8;">
	<h2 style="text-align:center; font-weight:500; color: #fff">Trash</h2>
	<?php
		if($search!='')
		{
			echo '<h4 style="text-align:center; font-weight:300;"> Results for '.$search.'. <span style="cursor:pointer;font-weight:700;" onclick="window.location=\'trash.php\'"><span class="fas fa-times"></span></span></h4>';
		}
	?>
	<form action="trash.php" method="POST">
		<h5 style="text-align:center; font-weight:300;"><input autocomplete="off" onfocus="updateidhead(this);" class="searchtrash" style="background:inherit; text-align:center; width:25%;" name="search" id="search" type="text" placeholder="Search for notes in the trash by clicking here"></h5>
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
		$query = 'SELECT * FROM entries WHERE trash = 1 AND (heading like \'%'.htmlspecialchars($search,ENT_QUOTES).'%\' OR entry like \'%'.htmlspecialchars($search,ENT_QUOTES).'%\') ORDER by updated DESC LIMIT 50';
		$res = $con->query($query);
		while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
		{
			$filename = "./entries/".$row["id"].".html";
			$handle = fopen($filename, "r");
			$contents = fread($handle, filesize($filename));
			$entryfinal = $contents;
			fclose($handle);
			echo '<div id="note'.$row['id'].'" class="notecard">
            <div class="innernote">
                <span title="Permanently delete" onclick="deletePermanent(\''.$row['id'].'\')" class="fas fa-trash pull-right icon_trash_trash" style="cursor: pointer;"></span>
                <span title="Restore this note" onclick="putBack(\''.$row['id'].'\')" class="fa fa-trash-restore-alt pull-right icon_restore_trash" style="margin-right:20px; cursor: pointer;"></span>
                <div id="lastupdated'.$row['id'].'" class="lastupdated">Last modified on '.formatDateTime(strtotime($row['updated'])).'</div>
                <h3><input id="inp'.$row['id'].'" type="text" placeholder="Title ?" value="'.$row['heading'].'"></input> </h3>
                <hr>
                <div class="noteentry" onload="initials(this);" id="entry'.$row['id'].'" data-ph="Enter text or images here" contenteditable="true">'.$entryfinal.'</div>
                <div style="height:30px;"></div>
            </div>
            </div>';
		}
	?>
</body>
<script src="js/script.js"></script>
</html>
