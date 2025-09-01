<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// Vérification accès utilisateur
if (!isset($_SESSION['user_id'])) {
    die("⛔ Accès non autorisé.");
}

// Vérification CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("🔒 Jeton CSRF invalide.");
}

// Récupération des données POST
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nomFichier = trim($_POST['nom_fichier'] ?? '');
$lienSite = trim($_POST['lien_site'] ?? '');
$versions = $_POST['versions'] ?? [];

// Validation minimale pour create et update
if (in_array($action, ['create', 'update']) && empty($nomFichier)) {
    die("Le nom du fichier est obligatoire.");
}

try {
    $pdo->beginTransaction();

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO partenaires (nom_fichier, lien_site, date_ajout, visible) VALUES (?, ?, NOW(), 1)");
        $stmt->execute([$nomFichier, $lienSite]);
        $id = $pdo->lastInsertId();

    } elseif ($action === 'update' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE partenaires SET nom_fichier = ?, lien_site = ? WHERE id = ?");
        $stmt->execute([$nomFichier, $lienSite, $id]);

        // Supprime les anciennes versions
        $stmt = $pdo->prepare("DELETE FROM partenaire_versions WHERE partenaire_id = ?");
        $stmt->execute([$id]);

    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM partenaire_versions WHERE partenaire_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM partenaires WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        header("Location: /partenaires_crud.php?deleted=1");
        exit;
    } else {
        throw new Exception("Action inconnue ou ID invalide.");
    }

    // Gestion des images uploadées
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

        // Créer les dossiers si besoin
        foreach ($sizes as $size) {
            $dir = __DIR__ . "/../public/img/partenaire/x$size";
            if (!is_dir($dir)) mkdir($dir, 0755, true);
        }

        // Créer la ressource image selon l'extension
        switch ($ext) {
            case 'jpg': case 'jpeg': $imgSrc = @imagecreatefromjpeg($tmpName); break;
            case 'png': $imgSrc = @imagecreatefrompng($tmpName); break;
            case 'gif': $imgSrc = @imagecreatefromgif($tmpName); break;
            case 'webp': $imgSrc = @imagecreatefromwebp($tmpName); break;
            default: throw new Exception("Extension non supportée.");
        }
        if (!$imgSrc) throw new Exception("Impossible de traiter l'image.");

        $widthOrig = imagesx($imgSrc);
        $heightOrig = imagesy($imgSrc);
        $chemin1200 = null;

        foreach ($sizes as $size) {
            $ratio = $widthOrig / $heightOrig;
            $newWidth = $size;
            $newHeight = intval($newWidth / $ratio);

            $tmpImg = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($tmpImg, false);
            imagesavealpha($tmpImg, true);
            imagecopyresampled($tmpImg, $imgSrc, 0, 0, 0, 0, $newWidth, $newHeight, $widthOrig, $heightOrig);

            $newFileName = $baseName . "_$size.webp";
            $pathRelative = "/img/partenaire/x$size/$newFileName";
            $pathAbsolute = __DIR__ . "/../public$pathRelative";

            imagewebp($tmpImg, $pathAbsolute, 80);
            imagedestroy($tmpImg);

            // Insérer version en base
            $stmt = $pdo->prepare("INSERT INTO partenaire_versions (partenaire_id, size, path) VALUES (?, ?, ?)");
            $stmt->execute([$id, $size, $pathRelative]);

            if ($size === 1200) $chemin1200 = $pathRelative;
        }

        imagedestroy($imgSrc);

        // Mettre à jour le chemin principal
        if ($chemin1200) {
            $stmt = $pdo->prepare("UPDATE partenaires SET chemin = ? WHERE id = ?");
            $stmt->execute([$chemin1200, $id]);
        }

    } elseif (in_array($action, ['create', 'update'])) {
        // Versions existantes envoyées via $versions
        foreach ($versions as $size => $fileName) {
            $sizeInt = (int)$size;
            if (!in_array($sizeInt, [320, 768, 1200])) continue;

            $fileName = basename($fileName);
            $pathRelative = "/img/partenaire/x$size/$fileName";

            $stmt = $pdo->prepare("INSERT INTO partenaire_versions (partenaire_id, size, path) VALUES (?, ?, ?)");
            $stmt->execute([$id, $sizeInt, $pathRelative]);

            if ($sizeInt === 1200) {
                $stmt = $pdo->prepare("UPDATE partenaires SET chemin = ? WHERE id = ?");
                $stmt->execute([$pathRelative, $id]);
            }
        }
    }

    $pdo->commit();
    header("Location: /partenaires_crud.php?success=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur : " . $e->getMessage());
}
