<?php
// Add favorite column to entries table
require 'config.php';
include 'db_connect.php';

// Check if the favorite column already exists
$check_query = "SHOW COLUMNS FROM entries LIKE 'favorite'";
$result = $con->query($check_query);

if ($result->num_rows == 0) {
    // Column doesn't exist, add it
    $alter_query = "ALTER TABLE entries ADD COLUMN favorite TINYINT(1) DEFAULT 0";
    
    if ($con->query($alter_query) === TRUE) {
        echo "✅ Colonne 'favorite' ajoutée avec succès à la table entries!\n";
    } else {
        echo "❌ Erreur lors de l'ajout de la colonne 'favorite': " . $con->error . "\n";
    }
} else {
    echo "ℹ️ La colonne 'favorite' existe déjà dans la table entries.\n";
}

$con->close();
?>
