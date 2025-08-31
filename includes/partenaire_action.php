<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../db.php';       // Connexion PDO dans $pdo
require_once __DIR__ . '/../functions.php'; // Fonctions utiles

// Vérifier accès utilisateur
if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

// Vérifier CSRF token
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Jeton CSRF invalide.");
}

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nomFichier = trim($_POST['nom_fichier'] ?? '');
$lienSite = trim($_POST['lien_site'] ?? '');
$versions = $_POST['versions'] ?? [];

// Validation pour create et update
if (in_array($action, ['create', 'update'])) {
    if (empty($nomFichier)) {
        die("Le nom du fichier est obligatoire.");
    }
}

$pdo->beginTransaction();

try {
    if ($action === 'create') {
        // Insert sans chemin (sera mis à jour après upload)
        $stmt = $pdo->prepare("INSERT INTO partenaires (nom_fichier, lien_site, date_ajout, visible) VALUES (?, ?, NOW(), 1)");
        $stmt->execute([$nomFichier, $lienSite]);
        $id = $pdo->lastInsertId();

    } elseif ($action === 'update' && $id > 0) {
        // Update texte (sans virgule en trop)
        $stmt = $pdo->prepare("UPDATE partenaires SET nom_fichier = ?, lien_site = ? WHERE id = ?");
        $stmt->execute([$nomFichier, $lienSite, $id]);

        // Supprimer anciennes versions
        $stmt = $pdo->prepare("DELETE FROM partenaire_versions WHERE partenaire_id = ?");
        $stmt->execute([$id]);

    } elseif ($action === 'delete' && $id > 0) {
        // Supprimer versions associées
        $stmt = $pdo->prepare("DELETE FROM partenaire_versions WHERE partenaire_id = ?");
        $stmt->execute([$id]);

        // Supprimer partenaire
        $stmt = $pdo->prepare("DELETE FROM partenaires WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();

        // Redirection après suppression
        header("Location: /partenaires_crud.php?deleted=1");
        exit;

    } else {
        throw new Exception("Action inconnue ou ID invalide.");
    }

    // Gestion upload image (create ou update)
    if (in_array($action, ['create', 'update']) && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['logo']['tmp_name'];
        $originalName = basename($_FILES['logo']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            throw new Exception("Type de fichier non autorisé.");
        }

        $baseName = pathinfo($nomFichier, PATHINFO_FILENAME);
        $sizes = [320, 768, 1200];

        // Créer dossiers si non existants
        foreach ($sizes as $size) {
            $dir = __DIR__ . "/../public/img/partenaire/x$size";
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Charger image source
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $imgSrc = @imagecreatefromjpeg($tmpName);
                break;
            case 'png':
                $imgSrc = @imagecreatefrompng($tmpName);
                break;
            case 'gif':
                $imgSrc = @imagecreatefromgif($tmpName);
                break;
            case 'webp':
                $imgSrc = @imagecreatefromwebp($tmpName);
                break;
            default:
                throw new Exception("Extension non supportée.");
        }

        if (!$imgSrc) {
            throw new Exception("Impossible de traiter l'image.");
        }

        $widthOrig = imagesx($imgSrc);
        $heightOrig = imagesy($imgSrc);
        $chemin1200 = null;

        foreach ($sizes as $size) {
            $ratio = $widthOrig / $heightOrig;
            $newWidth = $size;
            $newHeight = intval($newWidth / $ratio);

            $tmpImg = imagecreatetruecolor($newWidth, $newHeight);

            // Gestion transparence (PNG/GIF)
            imagealphablending($tmpImg, false);
            imagesavealpha($tmpImg, true);

            imagecopyresampled($tmpImg, $imgSrc, 0, 0, 0, 0, $newWidth, $newHeight, $widthOrig, $heightOrig);

            $newFileName = $baseName . "_$size.webp";
            $pathRelative = "/img/partenaire/x$size/$newFileName";
            $pathAbsolute = __DIR__ . "/../public/img/partenaire/x$size/$newFileName";

            imagewebp($tmpImg, $pathAbsolute, 80);
            imagedestroy($tmpImg);

            // Enregistrer dans partenaire_versions
            $stmt = $pdo->prepare("INSERT INTO partenaire_versions (partenaire_id, size, path) VALUES (?, ?, ?)");
            $stmt->execute([$id, $size, $pathRelative]);

            if ($size === 1200) {
                $chemin1200 = $pathRelative;
            }
        }

        imagedestroy($imgSrc);

        // Mettre à jour chemin 1200 dans partenaires
        if ($chemin1200 !== null) {
            $stmt = $pdo->prepare("UPDATE partenaires SET chemin = ? WHERE id = ?");
            $stmt->execute([$chemin1200, $id]);
        }

    } elseif (in_array($action, ['create', 'update'])) {
        // Pas d'upload mais gérer les versions envoyées (ex: existantes)
        foreach ($versions as $size => $fileName) {
            $sizeInt = (int)$size;
            if (!in_array($sizeInt, [320, 768, 1200])) continue;

            $fileName = basename($fileName);
            $pathRelative = "/img/partenaire/x$size/$fileName";

            $stmt = $pdo->prepare("INSERT INTO partenaire_versions (partenaire_id, size, path) VALUES (?, ?, ?)");
            $stmt->execute([$id, $sizeInt, $pathRelative]);

            if ($sizeInt === 1200) {
                // Mettre à jour chemin 1200 dans partenaires
                $stmt = $pdo->prepare("UPDATE partenaires SET chemin = ? WHERE id = ?");
                $stmt->execute([$pathRelative, $id]);
            }
        }
    }

    $pdo->commit();

    // Redirection succès
    header("Location: /partenaires_crud.php?success=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur : " . $e->getMessage());
}
