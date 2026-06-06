<?php

session_start();

require_once 'auth.php';
requireLogin();

require_once 'db.php';

// On dit au navigateur que la réponse sera en JSON (pour fetch côté JS)
header('Content-Type: application/json');

try {

    // Récupère l'ID de l'utilisateur connecté
    $id = getUserId();

    // Cherche la photo de l'utilisateur dans la base
    $stmt = $conn->prepare("
        SELECT photo
        FROM Utilisateur
        WHERE id_user = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    // Si aucun utilisateur trouvé dans la base
    if (!$user) {
        throw new Exception("Utilisateur introuvable");
    }

    // Récupère le chemin de la photo
    $photo = $user['photo'];

    // Si aucune photo enregistrée
    if (empty($photo)) {
        throw new Exception("Aucune photo enregistrée");
    }

    // Supprime le fichier photo du serveur si il existe
    if (file_exists($photo)) {
        unlink($photo);
    }

    // Met à jour la base pour supprimer la référence de la photo
    $stmt = $conn->prepare("
        UPDATE Utilisateur
        SET photo = NULL
        WHERE id_user = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Réponse en cas de succès
    echo json_encode([
        "success" => true
    ]);

} catch (Exception $e) {

    // Réponse en cas d'erreur
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);

}