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

<style>
  #myInputFiltrerTags {
	width: 96vw;
	max-width: 700px;
	margin: 0 auto 16px auto;
	display: block;
	padding: 14px 18px;
	border: 1px solid #ccc;
	border-radius: 6px;
	font-size: 1.25em;
	box-sizing: border-box;
  }
  @media (max-width: 600px) {
	#myInputFiltrerTags {
	  max-width: 98vw;
	  margin: 8px 1vw 10px 1vw;
	  font-size: 1em;
	  padding: 10px 8px;
	}
  }
</style>
<style>
  .tags-list-container {
	max-width: 100vw;
	margin: 0 auto;
	padding: 8px 0;
	background: #fff;
	border-radius: 10px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  }
  #myInputFiltrerTags {
	width: 100%;
	padding: 14px 18px;
	margin-bottom: 16px;
	border: 1px solid #ccc;
	border-radius: 6px;
	font-size: 1.25em;
	box-sizing: border-box;
  }
  .tags-list-info {
	margin-bottom: 10px;
	font-size: 1em;
	color: #007DB8;
	text-align: center;
  }
  #myULFiltrerTags {
	list-style-type: none;
	padding: 0;
	margin: 0;
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	justify-content: center;
  }
  #myULFiltrerTags li {
	margin: 0;
  }
  #myULFiltrerTags a {
	display: inline-block;
	padding: 10px 20px;
	border-radius: 16px;
	background: #f2f2f2;
	color: #333;
	text-decoration: none;
	font-size: 1.18em;
	transition: background 0.2s, color 0.2s;
  }
  #myULFiltrerTags a:hover {
	background: #007DB8;
	color: #fff;
  }
  @media (max-width: 600px) {
	.tags-list-container {
	  max-width: 100vw;
	  padding: 4px;
	}
	#myInputFiltrerTags {
	  font-size: 1.15em;
	  padding: 12px 10px;
	}
	#myULFiltrerTags a {
	  font-size: 1.08em;
	  padding: 10px 10px;
	}
  }
</style>
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
