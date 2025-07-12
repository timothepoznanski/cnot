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

<input type="text" id="myInputFiltrerTags" onkeyup="myFunctionFiltrerTags()" placeholder="Filter tags list..." title="Filter on tags"><br>
<br>There are <?php echo $count_tags; ?> tags :<br>
<ul id='myULFiltrerTags' style='list-style-type:none'>
<?php
foreach($tags_list as $tag) {
    echo "<li><a href='index.php?tags_search_from_list=$tag' style='text-decoration:none; color:#333'>$tag</a></li>";
}
?>
</ul>

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
