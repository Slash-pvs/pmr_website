<?php
session_start();
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';
header('Content-Type: application/json');

// Vérification du token CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit;
}

// Vérification des champs obligatoires
if (!isset($_POST['id'], $_POST['nom'], $_POST['prix'], $_POST['quantite'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

// Sanitation et conversion des valeurs
$id = intval($_POST['id']);
$nom = trim($_POST['nom']);
$prix = floatval($_POST['prix']);
$quantite = intval($_POST['quantite']);

// Validation stricte
if ($id <= 0 || $prix < 0 || $quantite < 1 || strlen($nom) > 255) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

// Initialisation du panier en session si nécessaire
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajout ou mise à jour du produit dans le panier
if (!isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id] = [
        'nom' => htmlspecialchars($nom, ENT_QUOTES),
        'prix' => $prix,
        'quantite' => $quantite
    ];
} else {
    $_SESSION['panier'][$id]['quantite'] += $quantite;
}

// Calcul du total d’articles
$total = array_sum(array_column($_SESSION['panier'], 'quantite'));

// Réponse JSON
echo json_encode(['success' => true, 'total' => $total]);
