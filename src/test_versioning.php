<?php
// Test du nouveau système de versioning
include 'functions.php';

echo "=== Test du système de versioning intelligent ===\n\n";

// Test 1: Version release
echo "1. Test version release (v1.2.0):\n";
file_put_contents(__DIR__ . '/version.txt', 'v1.2.0');
$result = getDeploymentVersion();
echo "   Version: " . $result['version'] . "\n";
echo "   Formaté: " . $result['formatted'] . "\n";
echo "   Type: " . $result['type'] . "\n";
echo "   Label interface: " . (($result['type'] === 'release') ? 'Release' : 'Version') . "\n\n";

// Test 2: Version timestamp
echo "2. Test version timestamp (202501241530):\n";
file_put_contents(__DIR__ . '/version.txt', '202501241530');
$result = getDeploymentVersion();
echo "   Version: " . $result['version'] . "\n";
echo "   Formaté: " . $result['formatted'] . "\n";
echo "   Type: " . $result['type'] . "\n";
echo "   Label interface: " . (($result['type'] === 'release') ? 'Release' : 'Version') . "\n\n";

// Test 3: Version développement
echo "3. Test version développement:\n";
file_put_contents(__DIR__ . '/version.txt', 'dev');
$result = getDeploymentVersion();
echo "   Version: " . $result['version'] . "\n";
echo "   Formaté: " . $result['formatted'] . "\n";
echo "   Type: " . $result['type'] . "\n";
echo "   Label interface: " . (($result['type'] === 'release') ? 'Release' : 'Version') . "\n\n";

echo "✅ Tous les tests terminés!\n";
?>
