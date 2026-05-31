<?php
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'auth.php';
requireLogin();
require_once 'db.php';

$id = getUserId();

header('Content-Type: application/json');

try {

    // récupérer ancienne photo
    $stmt = $conn->prepare("SELECT photo FROM Utilisateur WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if (!$user || empty($user['photo'])) {
        echo json_encode(["success" => false, "error" => "Aucune photo"]);
        exit;
    }

    $photoPath = $user['photo'];

    // supprimer fichier serveur si existe
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    // reset DB
    $stmt = $conn->prepare("UPDATE Utilisateur SET photo = NULL WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
