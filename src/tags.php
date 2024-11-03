<?php
require 'config.php';
require 'db_connect.php';

$search = isset($_POST['search']) ? $_POST['search'] : '';
$tags = [];
$tagSql = "SELECT tags FROM entries WHERE tags IS NOT NULL AND tags != ''";
if (!empty($search)) {
    $search = $con->real_escape_string($search);
    $tagSql .= " AND tags LIKE '%$search%'";
}

$tagRs = $con->query($tagSql);
if ($tagRs && $tagRs->num_rows > 0) {
    while ($row = mysqli_fetch_array($tagRs, MYSQLI_ASSOC)) {
        $explode = explode(',', $row['tags']);
        $tags = array_merge($tags, $explode);
    }
}

if (!empty($tags)) {
    $tags = array_unique($tags);
    $tags = array_values($tags);
}

header('Content-Type: application/json');
echo json_encode(array_values($tags)); 
