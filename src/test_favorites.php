<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';
include 'db_connect.php';

echo "<h2>Test des favoris</h2>";

// Test 1: Vérifier que la colonne favorite existe
echo "<h3>1. Vérification de la structure de la table</h3>";
$result = $con->query("SHOW COLUMNS FROM entries");
echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Default']}</td></tr>";
}
echo "</table>";

// Test 2: Lister quelques notes avec leur statut favori
echo "<h3>2. Notes existantes (5 premières)</h3>";
$result = $con->query("SELECT id, heading, folder, favorite FROM entries WHERE trash = 0 LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Titre</th><th>Dossier</th><th>Favori</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['heading']}</td><td>{$row['folder']}</td><td>{$row['favorite']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "Aucune note trouvée.";
}

// Test 3: Tester manuellement l'API
echo "<h3>3. Test de l'API</h3>";
if (isset($_POST['test_api'])) {
    $noteId = $_POST['note_id'];
    echo "Test avec note ID: $noteId<br>";
    
    // Simuler la requête POST
    $_POST['action'] = 'toggle_favorite';
    $_POST['note_id'] = $noteId;
    
    // Inclure l'API
    ob_start();
    include 'api_favorites.php';
    $response = ob_get_clean();
    
    echo "Réponse de l'API: <pre>$response</pre>";
}

// Formulaire de test
echo "<form method='post'>";
echo "<input type='hidden' name='test_api' value='1'>";
echo "ID de note à tester: <input type='text' name='note_id' value='1'>";
echo "<input type='submit' value='Tester API'>";
echo "</form>";
?>
