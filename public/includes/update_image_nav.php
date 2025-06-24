<?php
$id = $_POST['id'];
$image_path = trim($_POST['image_path']);

// Mise à jour de image_nav
$updateNavSql = "UPDATE image_nav SET image_path = ? WHERE id = ?";
$stmt = $conn->prepare($updateNavSql);
$stmt->bind_param("si", $image_path, $id);
$stmt->execute();

// Mise à jour des versions
foreach ($_POST['versions'] as $version) {
    $versionId = $version['id'];
    $format = $version['format'];
    $size = $version['size'];
    $path = $version['path'];

    $updateVersionSql = "UPDATE image_nav_versions SET format = ?, size = ?, path = ? WHERE id = ?";
    $stmt = $conn->prepare($updateVersionSql);
    $stmt->bind_param("sisi", $format, $size, $path, $versionId);
    $stmt->execute();
}

echo "Mise à jour réussie.";
?>
