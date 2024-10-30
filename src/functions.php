<?php
date_default_timezone_set('UTC');
function formatDate($t)
{
	$parsedt = date('j M Y',$t);
	return $parsedt;
}

function formatDateTime($t)
{
	return formatDate($t)." à ".date('H:i',$t);
}
?>
