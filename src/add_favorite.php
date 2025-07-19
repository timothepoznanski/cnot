<?php
// Simple script to add favorite column
require 'config.php';
include 'db_connect.php';

// Add the favorite column directly
$sql = "ALTER TABLE entries ADD COLUMN favorite TINYINT(1) DEFAULT 0";

if ($con->query($sql) === TRUE) {
    echo "Colonne 'favorite' ajoutée avec succès!\n";
} else {
    if (strpos($con->error, "Duplicate column name") !== false) {
        echo "La colonne 'favorite' existe déjà.\n";
    } else {
        echo "Erreur: " . $con->error . "\n";
    }
}

$con->close();
?>
