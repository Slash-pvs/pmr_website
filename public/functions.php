<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction : Vérifie si l'utilisateur est connecté
function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

// Fonction générique : Vérifie si l'utilisateur connecté a un rôle donné
function isUserRole(PDO $pdo, string $role): bool {
    if (!isUserLoggedIn()) return false;

    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user && $user['role'] === $role;
}

// Fonction spécifique : Vérifie si l'utilisateur est admin
function isUserAdmin(PDO $pdo): bool {
    return isUserRole($pdo, 'admin');
}

// Fonction : Récupère les infos utilisateur
function getUserInfo(PDO $pdo): ?array
{
    if (isUserLoggedIn()) {
        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            return null; // Pas trouvé
        }
        return $user;
    }
    return null; // Non connecté
}

// Fonction : Récupère les chemins d'images de la navigation
function getNavImageWithVersions(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT image_path FROM image_nav ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [];
    }

    $originalPath = $row['image_path'];
    $baseFilename = pathinfo($originalPath, PATHINFO_FILENAME);
    $directory = dirname($originalPath);

    $versions = [];
    $resolutions = ['320', '768', '1200'];
    foreach ($resolutions as $res) {
        $versionPath = "$directory/versions/x$res/{$baseFilename}.webp";

        // Corriger le chemin : on enlève '/public'
        $webPath = str_replace('/public', '', $versionPath);

        if (file_exists(__DIR__ . $versionPath)) {
            $versions[$res] = $webPath;
        }
    }

    // Enlever '/public' pour le chemin original aussi
    return [
        'original' => str_replace('/public', '', $originalPath),
        'versions' => $versions
    ];
}

// Fonction : Récupère les infos de contact
function getContactInfo($pdo)
{
    $stmt = $pdo->prepare("SELECT email, numero_tel, lieu FROM contacts WHERE id = 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction : Inclusion sécurisée de fichier
function safeRequire(string $file): void
{
    // Évite les traversals en récupérant seulement le nom de fichier
    $filename = basename($file);

    // Chemin absolu vers le dossier des includes (ajuste selon ton arborescence)
    $includeDir = __DIR__;

    $filePath = $includeDir . '/' . $filename;

    if (file_exists($filePath) && is_file($filePath)) {
        require_once $filePath;
    } else {
        error_log("Fichier introuvable ou non sécurisé : " . $filePath);
        echo "Erreur : fichier requis manquant ou non sécurisé.";
        exit; // pour éviter de continuer
    }
}

// Fonction : Récupère les images de la galerie (limit)
function getAllImages(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT id, image_path, category FROM gallerie ORDER BY created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return enrichImagesWithVersions($pdo, $images);
}

function getImagesByCategory(PDO $pdo, string $category): array
{
    $stmt = $pdo->prepare("SELECT id, image_path, category FROM gallerie WHERE category = :category ORDER BY created_at DESC");
    $stmt->execute([':category' => $category]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return enrichImagesWithVersions($pdo, $images);
}

function enrichImagesWithVersions(PDO $pdo, array $images): array
{
    $allowedSizes = [320, 768, 1200];
    $result = [];

    foreach ($images as $img) {
        $gallerieId = $img['id'];
        $originalPath = str_replace('/public', '', $img['image_path']);

        $stmtVersions = $pdo->prepare("SELECT size, path FROM gallerie_versions WHERE gallerie_id = :id");
        $stmtVersions->execute([':id' => $gallerieId]);
        $versionsData = $stmtVersions->fetchAll(PDO::FETCH_ASSOC);

        $versions = [];
        foreach ($versionsData as $v) {
            $size = (int) $v['size'];
            if (in_array($size, $allowedSizes)) {
                $versions[$size] = str_replace('/public', '', $v['path']);
            }
        }

        $result[] = [
            'original' => $originalPath,
            'category' => $img['category'],
            'versions' => $versions
        ];
    }

    return $result;
}

// Fonction : Récupère l'image de navigation
function getNavImage($pdo): string
{
    $stmt = $pdo->prepare("SELECT image_path FROM image_nav LIMIT 1");
    $stmt->execute();
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    return $image['image_path'] ?? 'default_image_path.jpg';
}
// Fonction : Récupère les versions de l'image de navigation
function getImageNavVersions(PDO $pdo, int $imageNavId): string
{
    $stmt = $pdo->prepare("
        SELECT size, path 
        FROM image_nav_versions 
        WHERE image_nav_id = :id 
        ORDER BY size ASC
    ");
    $stmt->execute(['id' => $imageNavId]);
    $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourne un tableau JSON utilisable dans un script JS
    return json_encode($versions, JSON_UNESCAPED_SLASHES);
}
// Fonction : Inclusion du footer
function includeFooter($contact, $partenaires)
{
    $filePath = __DIR__ . '/footer.php';

    if (file_exists($filePath)) {
        renderFooter($contact, $partenaires);
    } else {
        error_log("Fichier introuvable : footer.php");
        echo "Erreur : fichier requis manquant.";
    }
}

function renderFooter($contact, $partenaires)
{
    include __DIR__ . '/footer.php';
}

// Fonction : Vérifie si une image existe déjà
function imageExists(PDO $pdo, string $imagePath): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM gallerie WHERE image_path = :image_path LIMIT 1");
    $stmt->execute([':image_path' => $imagePath]);
    return (bool) $stmt->fetchColumn();
}

// Récupère tous les articles
function getAllPosts($pdo): array
{
    $stmt = $pdo->prepare("SELECT id, title, content, category, user_id, created_at, image_path FROM articles ORDER BY created_at DESC");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $articles;
}

// Articles par catégorie (avec validation recommandée)
function getPostsByCategory($pdo, $category): array
{
    $stmt = $pdo->prepare("SELECT id, title, content, category, user_id, created_at, image_path FROM articles WHERE category = :category ORDER BY created_at DESC");
    $stmt->execute([':category' => $category]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Présentation équipe
function getTeamDescription($pdo, string $teamName): ?string
{
    $stmt = $pdo->prepare("SELECT description FROM presentation WHERE team_name = :team_name LIMIT 1");
    $stmt->execute([':team_name' => $teamName]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data['description'] ?? null;
}
// Partenaires
function getAllPartners(PDO $pdo): array
{
    $sql = "
        SELECT 
            p.id, p.nom_fichier, p.lien_site,
            v.size, v.path
        FROM partenaires p
        LEFT JOIN partenaire_versions v ON v.partenaire_id = p.id
        WHERE p.visible = 1
        ORDER BY p.id
    ";

    $stmt = $pdo->query($sql);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $partenaires = [];

    foreach ($resultats as $row) {
        $id = $row['id'];
        if (!isset($partenaires[$id])) {
            $partenaires[$id] = [
                'id' => $id,
                'nom_fichier' => $row['nom_fichier'],
                'lien_site' => $row['lien_site'],
                'versions' => []
            ];
        }
        if ($row['size'] && $row['path']) {
            $partenaires[$id]['versions'][$row['size']] = $row['path'];
        }
    }

    return $partenaires;
}

// Génère un token CSRF
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
//récuperations des produits de la boutique 
function AllProducts(PDO $pdo): array
{
    $sth = $pdo->prepare("
        SELECT p.id, p.nom, p.categorie, p.prix, p.stock,
               v.size, v.path
        FROM produits p
        LEFT JOIN produit_versions v ON v.produit_id = p.id
        ORDER BY p.id, v.size
    ");
    $sth->execute();
    $rows = $sth->fetchAll(PDO::FETCH_ASSOC);

    // Regrouper par produit
    $produits = [];
    foreach ($rows as $row) {
        $id = $row['id'];
        if (!isset($produits[$id])) {
            $produits[$id] = [
                'id' => $id,
                'nom' => $row['nom'],
                'categorie' => $row['categorie'],
                'prix' => $row['prix'],
                'stock' => $row['stock'],
                'versions' => []
            ];
        }

        // Construire le chemin complet seulement pour les tailles 320, 768, 1200
        $allowedSizes = [320, 768, 1200];
        if ($row['size'] && in_array($row['size'], $allowedSizes)) {
            // Exemple : boutique/x320/nomfichier.webp
            $taille = $row['size'];
            $nomFichier = $row['path'];
            $cheminComplet = "boutique/x$taille/$nomFichier";

            $produits[$id]['versions'][$taille] = $cheminComplet;
        }
    }

    return array_values($produits);
}
function sanitizeFilename(string $filename): string
{
    // Ne garder que le nom du fichier sans chemin (évite ../)
    $basename = basename($filename);

    // Remplacer les caractères non alphanumériques par un tiret ou underscore
    $safeName = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $basename);

    return $safeName;
}

function verifyCsrfToken($token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
