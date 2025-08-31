<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$imagePaths = $input['image_paths'] ?? [];

if (!is_array($imagePaths) || empty($imagePaths)) {
    echo json_encode(['success' => false, 'error' => 'Chemins d\'image invalides']);
    exit;
}

$imgRoot = realpath(__DIR__ . '/../img');  // chemin absolu vers img
$errors = [];

foreach ($imagePaths as $path) {
    // Nettoyage simple pour éviter ../
    $cleanPath = str_replace(['../', './'], '', $path);

    $fullPath = $imgRoot . DIRECTORY_SEPARATOR . $cleanPath;
    $realPath = realpath($fullPath);

    // Vérifier que le fichier existe et est dans le dossier img
    if (!$realPath || strpos($realPath, $imgRoot) !== 0) {
        $errors[] = "Fichier non autorisé : " . htmlspecialchars($path);
        continue;
    }

    if (file_exists($realPath)) {
        if (!unlink($realPath)) {
            $errors[] = "Échec suppression : " . htmlspecialchars(basename($realPath));
        }
    }
}

if (empty($errors)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
}
