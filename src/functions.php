<?php
date_default_timezone_set('UTC');
function formatDate($t) {
	return date('j M Y',$t);
}
function formatDateTime($t) {
	return formatDate($t)." à ".date('H:i',$t);
}

function getDeploymentVersion() {
    $versionFile = __DIR__ . '/version.txt';
    if (file_exists($versionFile)) {
        $version = trim(file_get_contents($versionFile));
        if (preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})$/', $version, $matches)) {
            // Format YYYYMMDDHHMM en date lisible
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            return [
                'version' => $version,
                'formatted' => "$day/$month/$year $hour:$minute"
            ];
        }
        return [
            'version' => $version,
            'formatted' => $version
        ];
    }
    return [
        'version' => 'dev',
        'formatted' => 'Development'
    ];
}
?>
