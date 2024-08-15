<?php
	require 'config.php';
?>

<html>
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1"/>
	<title>CNOT - <?php echo JOURNAL_NAME;?></title>
	<link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
	<link href='https://fonts.googleapis.com/css?family=Roboto:100,100italic,300,300italic,400,400italic,500,500italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<script src="js/jquery.min.js"></script>
</head>
<body onload="$('#pass').focus();">
	<br>
	<br>
	<h1 style="text-align:center"><img width="30" height="30" class="imagelogin" src="favicon.ico" alt="favicon cnot3"></h1>
	<h1 style="text-align:center"><?php echo JOURNAL_NAME;?></h1>
	<hr>
	<br>
	<form action="loginAction.php" method="POST">
		<h2><input autocomplete="off" spellcheck="false" id="pass" style="text-align:center" name="pass" type="password" placeholder="Password?"></input></h2>
	</form>
</body>
</html>
