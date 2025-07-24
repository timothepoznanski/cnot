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
        
        // Check if it's a timestamp (YYYYMMDDHHMM)
        if (preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})$/', $version, $matches)) {
            return [
                'version' => $version,
                'formatted' => $version  // Display raw timestamp
            ];
        }
        
        // Fallback for any other format
        return [
            'version' => $version,
            'formatted' => $version
        ];
    }
    
    return [
        'version' => 'dev',
        'formatted' => 'dev'
    ];
}
?>
