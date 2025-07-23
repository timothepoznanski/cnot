<?php
// Test du système de versioning simplifié
include 'functions.php';

echo "=== Test du système de versioning simple ===\n\n";

// Test 1: Version timestamp
echo "1. Test version timestamp (202501241530):\n";
file_put_contents(__DIR__ . '/version.txt', '202501241530');
$result = getDeploymentVersion();
echo "   Version: " . $result['version'] . "\n";
echo "   Formaté: " . $result['formatted'] . "\n";
echo "   Interface: Version - " . $result['formatted'] . "\n\n";

// Test 2: Version développement
echo "2. Test version développement:\n";
file_put_contents(__DIR__ . '/version.txt', 'dev');
$result = getDeploymentVersion();
echo "   Version: " . $result['version'] . "\n";
echo "   Formaté: " . $result['formatted'] . "\n";
echo "   Interface: Version - " . $result['formatted'] . "\n\n";

echo "✅ Tests terminés!\n";
echo "💡 Format timestamp: YYYYMMDDHHMM (ex: 202501241530)\n";
?>
