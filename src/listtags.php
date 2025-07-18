<?php
require 'config.php';
include 'db_connect.php';

$res = $con->query('SELECT tags FROM entries');
$tags_list = [];
$count_tags = 0;

while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {   
	$words = explode(',', $row['tags']);
	foreach($words as $word) {
		$count_tags++;
		if (!in_array($word, $tags_list)) {
			$tags_list[] = $word;
		}		
	}
}

sort($tags_list, SORT_NATURAL | SORT_FLAG_CASE);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des tags</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/listtags.css">
</head>
<body>

<div class="tags-list-container">
  <input type="text" id="myInputFiltrerTags" onkeyup="myFunctionFiltrerTags()" placeholder="Filter tags list..." title="Filter on tags">
  <div class="tags-list-info">There are <?php echo $count_tags; ?> tags :</div>
  <ul id='myULFiltrerTags'>

<?php
foreach($tags_list as $tag) {
	echo "<li><a href='index.php?tags_search_from_list=$tag'>$tag</a></li>";
}
?>
  </ul>
</div>

<script src="js/listtags.js"></script>
</body>
</html>
