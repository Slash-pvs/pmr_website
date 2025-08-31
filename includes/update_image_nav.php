<?php
require_once __DIR__ . '/../functions.php';
$conn = require __DIR__ . '/../db.php';

$id = $_POST['id'];
$image_path = trim($_POST['image_path']);

// Mise à jour de l'image originale dans image_nav
$updateNavSql = "UPDATE image_nav SET image_path = ? WHERE id = ?";
$stmt = $conn->prepare($updateNavSql);
$stmt->bind_param("si", $image_path, $id);
$stmt->execute();

// Liste des formats et tailles associées
$formats = [
    'x320' => 320,
    'x768' => 768,
    'x1200' => 1200
];

// Nom de fichier uniquement
$imageFilename = basename($image_path);

// Mise à jour ou insertion des versions
foreach ($formats as $format => $size) {
    $versionPath = "/img/image_nav/$format/" . $imageFilename;

    // Vérifie si une version existe déjà pour ce format
    $checkSql = "SELECT id FROM image_nav_versions WHERE image_nav_id = ? AND format = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $id, $format);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Met à jour si elle existe
        $versionId = $row['id'];
        $updateVersionSql = "UPDATE image_nav_versions SET size = ?, path = ? WHERE id = ?";
        $stmt = $conn->prepare($updateVersionSql);
        $stmt->bind_param("isi", $size, $versionPath, $versionId);
        $stmt->execute();
    } else {
        // Sinon, l'insère
        $insertSql = "INSERT INTO image_nav_versions (image_nav_id, format, size, path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("isis", $id, $format, $size, $versionPath);
        $stmt->execute();
    }
}
echo "Mise à jour réussie.";
?>

