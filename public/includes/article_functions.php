<?php
//article_functions.php

// Récupère tous les articles
function getAllArticles(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupère un article par ID
function getArticleById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    return $article ?: null;
}

// Crée un article
function createArticle(PDO $pdo, string $title, string $content, string $category, int $userId, string $imagePath): bool {
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, category, user_id, created_at, image_path) VALUES (:title, :content, :category, :user_id, NOW(), :image_path)");
    return $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':category' => $category,
        ':user_id' => $userId,
        ':image_path' => $imagePath,
    ]);
}

// Met à jour un article
function updateArticle(PDO $pdo, int $id, string $title, string $content, string $category, string $imagePath): bool {
    $stmt = $pdo->prepare("UPDATE articles SET title = :title, content = :content, category = :category, image_path = :image_path WHERE id = :id");
    return $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':category' => $category,
        ':image_path' => $imagePath,
        ':id' => $id,
    ]);
}

// Supprime un article
function deleteArticle(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// Récupère la liste des images disponibles dans /img pour sélectionner
function getAvailableImages(): array {
    $baseDir = __DIR__ . '/../img/gallery'; // Peut-être besoin de corriger le chemin ici
    $images = [];

    if (!is_dir($baseDir)) {
        echo "Pas un dossier : $baseDir<br>";
        return $images;
    }

    $files = scandir($baseDir);
    foreach ($files as $file) {
        $filePath = $baseDir . '/' . $file;
        if (is_file($filePath) && preg_match('/\.(webp)$/i', $file)) {
            $images[] = $file;
        }
    }
    return $images;
}
// Récupère toutes les catégories uniques utilisées dans les articles
function getAllCategories(PDO $pdo): array {
    $stmt = $pdo->query("SELECT DISTINCT category FROM articles WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
