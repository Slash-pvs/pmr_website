<?php
session_start();
header('Content-Type: application/json');

$response = [
    'success' => false,
    'panier' => $_SESSION['panier'] ?? []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'remove':
            if (isset($_POST['index']) && is_numeric($_POST['index'])) {
                $index = (int) $_POST['index'];
                if (isset($_SESSION['panier'][$index])) {
                    unset($_SESSION['panier'][$index]);
                    $_SESSION['panier'] = array_values($_SESSION['panier']); // Réindexer
                    $response['success'] = true;
                }
            }
            break;

        case 'clear':
            $_SESSION['panier'] = [];
            $response['success'] = true;
            break;
    }

    // Met à jour la clé panier dans la réponse
    $response['panier'] = $_SESSION['panier'];
}

echo json_encode($response);
exit;
