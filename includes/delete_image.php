<?php
session_start();
header('Content-Type: application/json');

// Vérification de l'utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit;
}

// Récupération des données JSON
$input = json_decode(file_get_contents('php://input'), true);
$imagePaths = $input['image_paths'] ?? [];

// Validation
if (!is_array($imagePaths) || empty($imagePaths)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => "Chemins d'image invalides"]);
    exit;
}

// Chemin absolu du dossier images
$imgRoot = realpath(__DIR__ . '/../img');
$errors = [];

foreach ($imagePaths as $path) {
    // Nettoyage pour éviter les traversals
    $cleanPath = ltrim(str_replace(['../', './'], '', $path), '/\\');

    $fullPath = $imgRoot . DIRECTORY_SEPARATOR . $cleanPath;
    $realPath = realpath($fullPath);

    // Vérification que le fichier existe et est bien dans le dossier img
    if (!$realPath || strpos($realPath, $imgRoot) !== 0) {
        $errors[] = "Fichier non autorisé : " . htmlspecialchars($path);
        continue;
    }

    if (!unlink($realPath)) {
        $errors[] = "Échec de suppression : " . htmlspecialchars(basename($realPath));
    }
}

// Réponse JSON
if (empty($errors)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
}
