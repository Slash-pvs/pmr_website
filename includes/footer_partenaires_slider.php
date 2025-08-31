<?php
$pdo = require __DIR__ . '/../db.php';
function getAllPartners(PDO $pdo): array {
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
