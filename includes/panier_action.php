<?php
session_start();
header('Content-Type: application/json');

// Initialisation du panier si nécessaire
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$response = [
    'success' => false,
    'panier'  => $_SESSION['panier'],
    'error'   => ''
];

// Vérifie la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'remove':
            $index = isset($_POST['index']) ? (int)$_POST['index'] : null;
            if ($index !== null && isset($_SESSION['panier'][$index])) {
                unset($_SESSION['panier'][$index]);
                $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexe
                $response['success'] = true;
            } else {
                $response['error'] = 'Article introuvable dans le panier.';
            }
            break;

        case 'clear':
            $_SESSION['panier'] = [];
            $response['success'] = true;
            break;

        default:
            $response['error'] = 'Action non reconnue.';
            break;
    }

    $response['panier'] = $_SESSION['panier'];
} else {
    $response['error'] = 'Méthode non autorisée.';
}

echo json_encode($response);
exit;
