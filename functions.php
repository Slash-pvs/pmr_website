<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Fonction générique pour exécuter une requête préparée
function db_query($sql, $params = [], $fetchAll = true) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $fetchAll ? $stmt->fetchAll() : $stmt;
}
// Vérifie si l'utilisateur est connecté
function isUserLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isUserRole($pdo, $role)
{
    if (!isUserLoggedIn()) {
        return false;
    }

    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($user && isset($user['role']) && $user['role'] === $role);
}

function isUserAdmin($pdo)
{
    return isUserRole($pdo, 'admin');
}

function getUserInfo($pdo)
{
    if (!isUserLoggedIn()) {
        return null;
    }

    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}
function getNavImageWithVersions($pdo)
{
    if (!($pdo instanceof PDO)) {
        throw new InvalidArgumentException("Le paramètre \$pdo doit être une instance de PDO.");
    }

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
        $fullPath = __DIR__ . $versionPath;

        if (file_exists($fullPath)) {
            $versions[$res] = $versionPath;
        }
    }

    return [
        'original' => $originalPath,
        'versions' => $versions
    ];
}
    

function getContactInfo($pdo)
{
    $stmt = $pdo->prepare("SELECT email, numero_tel, lieu FROM contacts WHERE id = 1");
    $stmt->execute();
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);

    return $contact ?: null;
}

function safeRequire($file, $vars = array())
{
    $filename = basename($file);
    $includeDir = __DIR__;
    $filePath = $includeDir . '/' . $filename;

    if (file_exists($filePath) && is_file($filePath)) {
        extract($vars);
        require_once $filePath;
    } else {
        error_log("Fichier introuvable ou non sécurisé : " . $filePath);
        http_response_code(500);
        exit;
    }
}

function getAllImages($pdo)
{
    $stmt = $pdo->query("SELECT id, image_path, category FROM gallerie ORDER BY created_at DESC");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return enrichImagesWithVersions($pdo, $images);
}

function getImagesByCategory($pdo, $category)
{
    if (!($pdo instanceof PDO)) {
        throw new InvalidArgumentException("Le paramètre \$pdo doit être une instance de PDO.");
    }

    $stmt = $pdo->prepare("SELECT id, image_path, category FROM gallerie WHERE category = :category ORDER BY created_at DESC");
    $stmt->execute([':category' => $category]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return enrichImagesWithVersions($pdo, $images);
}

    function enrichImagesWithVersions($pdo, $images)
    {
        // Vérifie que $pdo est bien un objet PDO
        if (!($pdo instanceof PDO)) {
            throw new Exception("PDO non initialisé");
        }
        $allowedSizes = array(320, 768, 1200);
        $result = array();

        foreach ($images as $img) {
            $gallerieId = $img['id'];
            $originalPath = str_replace('/public', '', $img['image_path']);

            $stmtVersions = $pdo->prepare("SELECT size, path FROM gallerie_versions WHERE gallerie_id = :id");
            $stmtVersions->execute(array(':id' => $gallerieId));
            $versionsData = $stmtVersions->fetchAll(PDO::FETCH_ASSOC);

            $versions = array();
            foreach ($versionsData as $v) {
                $size = (int) $v['size'];
                if (in_array($size, $allowedSizes, true)) {
                    $versions[$size] = $v['path'];
                }
            }

            $result[] = array(
                'original' => $originalPath,
                'category' => $img['category'],
                'versions' => $versions
            );
        }

        return $result;
    }

function getNavImage($pdo)
{
    $stmt = $pdo->prepare("SELECT image_path FROM image_nav LIMIT 1");
    $stmt->execute();
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    return isset($image['image_path']) ? $image['image_path'] : 'default_image_path.jpg';
}

function getImageNavVersions($pdo, $imageNavId)
{
    $stmt = $pdo->prepare("
        SELECT size, path 
        FROM image_nav_versions 
        WHERE image_nav_id = :id 
        ORDER BY size ASC
    ");
    $stmt->execute(array(':id' => $imageNavId));
    $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return json_encode($versions, JSON_UNESCAPED_SLASHES);
}

function includeFooter($contact, $partenaires)
{
    $filePath = __DIR__ . '/footer.php';

    if (file_exists($filePath)) {
        renderFooter($contact, $partenaires);
    } else {
        error_log("Fichier introuvable : footer.php");
        http_response_code(500);
        exit;
    }
}

function renderFooter($contact, $partenaires)
{
    extract(compact('contact', 'partenaires'));
    include __DIR__ . '/footer.php';
}


function imageExists($pdo, $imagePath)
{
    $stmt = $pdo->prepare("SELECT 1 FROM gallerie WHERE image_path = :image_path LIMIT 1");
    $stmt->execute(array(':image_path' => $imagePath));
    return (bool) $stmt->fetchColumn();
}

function getAllPosts($pdo)
{
    $stmt = $pdo->prepare("SELECT id, title, content, category, user_id, created_at, image_path FROM articles ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPostsByCategory($pdo, $category)
{
    $stmt = $pdo->prepare("SELECT id, title, content, category, user_id, created_at, image_path FROM articles WHERE category = :category ORDER BY created_at DESC");
    $stmt->execute(array(':category' => $category));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function AllProducts($pdo)
{
    $stmt = $pdo->query("SELECT id, nom, categorie, prix, stock, image_path FROM produits ORDER BY nom ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return enrichProductsWithVersions($pdo, $products);
}




function getTeamDescription($pdo, $teamName)
{
    $stmt = $pdo->prepare("SELECT description FROM presentation WHERE team_name = :team_name LIMIT 1");
    $stmt->execute(array(':team_name' => $teamName));
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return isset($data['description']) ? $data['description'] : null;
}

function getAllPartners($pdo)
{
    $sql = "
        SELECT id, nom_fichier AS nom, lien_site AS site, chemin AS logo, visible, description
        FROM partenaires
        WHERE visible = 1
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function enrichProductsWithVersions($pdo, $products)
{
    if (!($pdo instanceof PDO)) {
        throw new Exception("PDO non initialisé");
    }

    $allowedSizes = [320, 768, 1200];
    $result = [];

    foreach ($products as $product) {
        $productId = $product['id'];

        $stmtVersions = $pdo->prepare("SELECT size, path FROM produit_versions WHERE produit_id = :id");
        $stmtVersions->execute([':id' => $productId]);
        $versionsData = $stmtVersions->fetchAll(PDO::FETCH_ASSOC);

        $versions = [];
        foreach ($versionsData as $v) {
            $size = (int) $v['size'];
            if (in_array($size, $allowedSizes, true)) {
                $versions[$size] = $v['path'];
            }
        }

        // On ajoute les versions trouvées au produit
        $product['versions'] = $versions;

        // Fallback : si aucune version trouvée mais image_path existe
        if (empty($versions) && !empty($product['image_path'])) {
            $product['versions'][320] = $product['image_path'];
        }

        $result[] = $product;
    }

    return $result;
}

function enrichPartnersWithVersions($pdo, $partners)
{
    if (!($pdo instanceof PDO)) {
        throw new Exception("PDO non initialisé");
    }

    $allowedSizes = [320, 768, 1200];
    $result = [];

    foreach ($partners as $partner) {
        $partnerId = $partner['id'];

        $stmtVersions = $pdo->prepare("SELECT size, path FROM partenaire_versions WHERE partenaire_id = :id");
        $stmtVersions->execute([':id' => $partnerId]);
        $versionsData = $stmtVersions->fetchAll(PDO::FETCH_ASSOC);

        $versions = [];
        foreach ($versionsData as $v) {
            $size = (int) $v['size'];
            if (in_array($size, $allowedSizes, true)) {
                $versions[$size] = $v['path'];
            }
        }

        $partner['versions'] = $versions;
        $result[] = $partner;
    }

    return $result;
}

/**
 * Génère un token CSRF et le stocke en session
 * @return string Le token CSRF
 */
function generateCsrfToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le token CSRF envoyé par le formulaire est valide
 * @param string|null $token Token reçu
 * @return bool true si valide, false sinon
 */
function verifyCsrfToken($token)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

// Génère le champ input à insérer dans un formulaire HTML

function csrfInputField()
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
