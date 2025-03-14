<?php
@ob_start();
session_start();
?>
<?php
	include 'functions.php';
	require 'config.php';
	$search = $_POST['search'];

	include 'db_connect.php';	

	$query_tags = 'SELECT tags FROM entries';	
	$res = $con->query($query_tags);

	$tags_list = array();

	$count_tags = 0;

	while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
	{   
		$delimiter = ',';
		$words = explode($delimiter, $row[tags]);

		foreach($words as $word)
		{
			$count_tags++;

			if (!in_array($word, $tags_list))
			{
				$tags_list[] = $word;
			}		
		}

	}

	sort($tags_list, SORT_NATURAL | SORT_FLAG_CASE);

	?><input type="text" id="myInputFiltrerTags" onkeyup="myFunctionFiltrerTags()" placeholder="Filter tags list..." title="Filter on tags"><br><?php

	echo"<br>There are $count_tags tags :<br>";

	echo "<ul id='myULFiltrerTags' style='list-style-type:none'>";

	foreach($tags_list as $tag)
	{
		echo "<li><a href='index.php?tags_search_from_list=".$tag."' style='text-decoration:none; color:#333'>".$tag."</a></li>";
	}
?>

<script>
	function myFunctionFiltrerTags() {
		var input, filter, ul, li, a, i;
		input = document.getElementById("myInputFiltrerTags");
		filter = input.value.toUpperCase();
		ul = document.getElementById("myULFiltrerTags");
		li = ul.getElementsByTagName("li");
		for (i = 0; i < li.length; i++) {
			a = li[i].getElementsByTagName("a")[0];
			if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
				li[i].style.display = "";
			} else {
				li[i].style.display = "none";
			}
		}
	}
	</script>
