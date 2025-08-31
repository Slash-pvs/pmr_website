<?php
session_start();
header('Content-Type: application/json');

if (!isset($_POST['id'], $_POST['nom'], $_POST['prix'], $_POST['quantite'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$id = $_POST['id'];
$nom = $_POST['nom'];
$prix = floatval($_POST['prix']);
$quantite = intval($_POST['quantite']);

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if (!isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id] = [
        'nom' => $nom,
        'prix' => $prix,
        'quantite' => $quantite
    ];
} else {
    $_SESSION['panier'][$id]['quantite'] += $quantite;
}

// Total d'articles :
$total = array_sum(array_column($_SESSION['panier'], 'quantite'));

echo json_encode(['success' => true, 'total' => $total]);
