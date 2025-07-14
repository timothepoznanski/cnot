<?php
// API pour créer une nouvelle note
header('Content-Type: application/json');
require_once 'config.php';
require_once 'db_connect.php';

// Vérifie que la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupère les données JSON envoyées
$input = json_decode(file_get_contents('php://input'), true);

$heading = isset($input['heading']) ? trim($input['heading']) : '';
$tags = isset($input['tags']) ? trim($input['tags']) : '';

if ($heading === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Le champ heading est requis']);
    exit;
}

$stmt = $con->prepare("INSERT INTO entries (heading, tags, updated) VALUES (?, ?, NOW())");
$stmt->bind_param('ss', $heading, $tags);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la création']);
}
$stmt->close();
