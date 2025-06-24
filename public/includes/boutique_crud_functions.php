<?php

// Créer un produit
function createProduct(PDO $pdo, string $nom, string $categorie, float $prix, int $stock): ?int {
    $sql = "INSERT INTO produits (nom, categorie, prix, stock) VALUES (:nom, :categorie, :prix, :stock)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        ':nom'       => $nom,
        ':categorie' => $categorie,
        ':prix'      => $prix,
        ':stock'     => $stock
    ])) {
        return (int)$pdo->lastInsertId();
    }

    return null;
}

// Récupérer tous les produits
function getAllProducts(PDO $pdo): array {
    $sql = "SELECT * FROM produits ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer toutes les catégories de produits (uniques)
function getAllProductCategories(PDO $pdo): array {
    $stmt = $pdo->query("SELECT DISTINCT categorie FROM produits ORDER BY categorie ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Récupérer un produit avec ses versions
function getProductById(PDO $pdo, int $id): ?array {
    $sql = "SELECT * FROM produits WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        return null;
    }

    $product['versions'] = getProductVersionsByProductId($pdo, $id);
    return $product;
}

// Récupérer les versions d’un produit
function getProductVersionsByProductId(PDO $pdo, int $produit_id): array {
    $sql = "SELECT format, size, path FROM produit_versions WHERE produit_id = :produit_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':produit_id' => $produit_id]);

    $versions = [];
    while ($version = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $size = (int)$version['size'];
        $versions[$size] = $version['path'];
    }

    return $versions;
}

// Mettre à jour un produit
function updateProduct(PDO $pdo, int $id, string $nom, string $categorie, float $prix): bool {
    $sql = "UPDATE produits SET nom = :nom, categorie = :categorie, prix = :prix WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':id'        => $id,
        ':nom'       => $nom,
        ':categorie' => $categorie,
        ':prix'      => $prix
    ]);
}

// Supprimer un produit et ses versions
function deleteProduct(PDO $pdo, int $id): bool {
    try {
        $pdo->beginTransaction();

        $pdo->prepare("DELETE FROM produit_versions WHERE produit_id = :id")
            ->execute([':id' => $id]);

        $pdo->prepare("DELETE FROM produits WHERE id = :id")
            ->execute([':id' => $id]);

        return $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erreur suppression produit : " . $e->getMessage());
        return false;
    }
}

// Upsert d’une version d’image (création ou mise à jour)
function upsertProductVersion(PDO $pdo, int $produit_id, string $format, int $size, string $path): bool {
    $sql = "
        INSERT INTO produit_versions (produit_id, format, size, path)
        VALUES (:produit_id, :format, :size, :path)
        ON DUPLICATE KEY UPDATE path = VALUES(path)
    ";
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':produit_id' => $produit_id,
        ':format'     => $format,
        ':size'       => $size,
        ':path'       => $path
    ]);
}

// Fonction de compatibilité : update ou insert version
function updateOrCreateProductVersion(PDO $pdo, int $produit_id, string $format, string $imagePath): bool {
    $formatSize = (int)$format;
    return upsertProductVersion($pdo, $produit_id, $format, $formatSize, $imagePath);
}

// Insérer une version d'image sans mise à jour (insertion pure)
function createProductVersion(PDO $pdo, int $produit_id, string $format, string $imagePath): bool {
    $formatSize = (int)$format;
    $sql = "INSERT INTO produit_versions (produit_id, format, size, path)
            VALUES (:produit_id, :format, :size, :path)";
    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':produit_id' => $produit_id,
        ':format'     => $format,
        ':size'       => $formatSize,
        ':path'       => $imagePath
    ]);
}
function fieldValue($postValue, $dbValue) {
    return htmlspecialchars($postValue ?? $dbValue ?? '');
}
    function formatImagePath($imageName) {
        return '/img/boutique/' . basename($imageName);
    }
function getAvailableImages(): array {
    $baseDir = '/home/fvhvyig/www/public/img/boutique/';
    $formats = ['320', '768', '1200'];
    $images = [];

    foreach ($formats as $format) {
        $dir = $baseDir . 'x' . $format . '/';
        $images[$format] = [];

        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if (preg_match('/\.webp$/i', $file)) {
                    // Chemin relatif web pour afficher l'image
                    $images[$format][] = '/img/boutique/x' . $format . '/' . $file;
                }
            }
        }
    }

    return $images; // tableau associatif : ['320' => [...], '768' => [...], '1200' => [...]]
}