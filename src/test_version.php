<?php
// Test de la fonction getDeploymentVersion
include 'functions.php';

echo "=== Test de la fonction getDeploymentVersion ===\n";

// Test avec version.txt existant (dev)
$result = getDeploymentVersion();
echo "Version actuelle: " . $result['version'] . "\n";
echo "Formaté: " . $result['formatted'] . "\n\n";

// Test avec un timestamp simulé
file_put_contents(__DIR__ . '/version.txt', '202501241530');
$result = getDeploymentVersion();
echo "Version timestamp: " . $result['version'] . "\n";
echo "Formaté: " . $result['formatted'] . "\n\n";

// Restaurer la version dev
file_put_contents(__DIR__ . '/version.txt', 'dev');
$result = getDeploymentVersion();
echo "Version restaurée: " . $result['version'] . "\n";
echo "Formaté: " . $result['formatted'] . "\n";
?>
