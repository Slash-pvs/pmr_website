<?php
require_once __DIR__ . '/../functions.php';
$pdo = require __DIR__ . '/../db.php';

$id = (int)($_POST['id'] ?? 0);
$image_path = trim($_POST['image_path'] ?? '');

if ($id <= 0 || empty($image_path)) {
    exit("ID ou image_path invalide.");
}

try {
    // Démarre la transaction
    $pdo->beginTransaction();

    // Mise à jour de l'image originale
    $updateNavSql = "UPDATE image_nav SET image_path = :image_path WHERE id = :id";
    $stmt = $pdo->prepare($updateNavSql);
    $stmt->execute([
        ':image_path' => $image_path,
        ':id' => $id
    ]);

    $formats = [
        'x320' => 320,
        'x768' => 768,
        'x1200' => 1200
    ];

    $imageFilename = basename($image_path);

    foreach ($formats as $format => $size) {
        $versionPath = "/img/image_nav/$format/$imageFilename";

        // Vérifie si une version existe déjà
        $checkSql = "SELECT id FROM image_nav_versions WHERE image_nav_id = :id AND format = :format";
        $stmt = $pdo->prepare($checkSql);
        $stmt->execute([
            ':id' => $id,
            ':format' => $format
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Met à jour
            $updateVersionSql = "UPDATE image_nav_versions SET size = :size, path = :path WHERE id = :vid";
            $stmtUpdate = $pdo->prepare($updateVersionSql);
            $stmtUpdate->execute([
                ':size' => $size,
                ':path' => $versionPath,
                ':vid' => $row['id']
            ]);
        } else {
            // Insert
            $insertSql = "INSERT INTO image_nav_versions (image_nav_id, format, size, path) VALUES (:id, :format, :size, :path)";
            $stmtInsert = $pdo->prepare($insertSql);
            $stmtInsert->execute([
                ':id' => $id,
                ':format' => $format,
                ':size' => $size,
                ':path' => $versionPath
            ]);
        }
    }

    // Valide la transaction
    $pdo->commit();

    echo "Mise à jour réussie.";

} catch (Exception $e) {
    // Annule la transaction en cas d'erreur
    $pdo->rollBack();
    echo "Erreur lors de la mise à jour : " . $e->getMessage();
}
