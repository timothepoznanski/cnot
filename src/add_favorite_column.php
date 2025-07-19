<?php
// Script pour ajouter la colonne favorite à la table entries
require 'config.php';
include 'db_connect.php';

// Vérifier si la colonne favorite existe déjà
$check_column = "SHOW COLUMNS FROM entries LIKE 'favorite'";
$result = $con->query($check_column);

if ($result->num_rows == 0) {
    // La colonne n'existe pas, on l'ajoute
    $add_column = "ALTER TABLE entries ADD COLUMN favorite TINYINT(1) DEFAULT 0";
    if ($con->query($add_column) === TRUE) {
        echo "Colonne 'favorite' ajoutée avec succès à la table entries.\n";
    } else {
        echo "Erreur lors de l'ajout de la colonne 'favorite': " . $con->error . "\n";
    }
} else {
    echo "La colonne 'favorite' existe déjà dans la table entries.\n";
}

$con->close();
?>
