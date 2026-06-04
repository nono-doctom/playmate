<?php
// Démarre la session utilisateur (nécessaire pour récupérer l'utilisateur connecté)
session_start();

// Fichiers nécessaires : sécurité + connexion DB
require_once 'auth.php';
require_once 'db.php';

// On renvoie du JSON (utilisé par fetch() côté JS)
header('Content-Type: application/json');

try {

    /* =========================
       RÉCUPÉRATION UTILISATEUR
    ========================= */

    // Récupère l'ID de l'utilisateur connecté
    $id = getUserId();

    // Sécurité : si pas connecté -> erreur
    if (!$id) {
        throw new Exception("Utilisateur non connecté");
    }

    /* =========================
       RÉCUPÉRATION FORMULAIRE
    ========================= */

    // Pseudo utilisateur (nouveau champ ajouté)
    $pseudo = $_POST['pseudo'] ?? null;

    // Date de naissance (séparée en 3 selects)
    $day = $_POST['day'] ?? null;
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;

    // Infos profil
    $bio = $_POST['bio'] ?? '';
    $humeur = $_POST['humeur'] ?? '';
    $personnageNom = $_POST['personnage'] ?? null;

    // Jeux sélectionnés (tableau multiple select)
    $jeux = $_POST['jeu'] ?? [];

    // Plateforme choisie
    $plateforme = $_POST['platform'] ?? 1;

    /* =========================
       CONSTRUCTION DATE NAISSANCE
    ========================= */

    // Si les 3 champs sont remplis on crée une date SQL
    $date = null;
    if ($day && $month && $year) {
        $date = "$year-$month-$day";
    }

    /* =========================
       RÉCUPÉRATION PHOTO EXISTANTE
    ========================= */

    // On récupère l'ancienne photo pour éviter de la perdre si pas de nouvelle image
    $stmt = $conn->prepare("SELECT photo FROM Utilisateur WHERE id_user=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    $photoPath = $user['photo'] ?? null;

    /* =========================
       UPLOAD PHOTO (SI FOURNIE)
    ========================= */

    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {

        // Types autorisés
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];

        // Vérifie le format
        if (!in_array($_FILES['photo']['type'], $allowed)) {
            throw new Exception("Format image invalide");
        }

        // Vérifie taille max (10MB)
        if ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
            throw new Exception("Image trop lourde (max 10MB)");
        }

        // Dossier upload
        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Nom unique du fichier
        $fileName = time() . "_" . basename($_FILES['photo']['name']);

        // Chemin final
        $photoPath = $uploadDir . $fileName;

        // Déplacement du fichier
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            throw new Exception("Erreur upload image");
        }
    }

    /* =========================
       VÉRIFICATION PERSONNAGE
    ========================= */

    // On vérifie que le personnage existe en base
    if ($personnageNom) {

        $stmt = $conn->prepare("
            SELECT nom_personnage
            FROM personnage
            WHERE nom_personnage=?
        ");

        $stmt->bind_param("s", $personnageNom);
        $stmt->execute();

        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception("Personnage non trouvé");
        }
    }

    /* =========================
       UPDATE UTILISATEUR
    ========================= */

    // Mise à jour des infos principales du profil
    $stmt = $conn->prepare("
        UPDATE Utilisateur
        SET pseudo=?,              -- pseudo modifiable
            date_naissance=?,      -- date naissance
            bio=?,                 -- bio utilisateur
            humeur=?,              -- humeur choisie
            nom_personnage=?,      -- personnage choisi
            photo=?                -- photo profil
        WHERE id_user=?
    ");

    // Liaison des paramètres
    $stmt->bind_param(
        "ssssssi",
        $pseudo,
        $date,
        $bio,
        $humeur,
        $personnageNom,
        $photoPath,
        $id
    );

    // Exécution + sécurité erreur
    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    /* =========================
       JEUX : RESET + INSERT
    ========================= */

    // On supprime les anciens jeux de l'utilisateur
    $stmt = $conn->prepare("DELETE FROM jouer WHERE id_user=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Récup id jeu par nom
    $stmtJeu = $conn->prepare("SELECT id_jeu FROM jeu WHERE nom=?");

    // Insertion relation user-jeu
    $insertJeu = $conn->prepare("
        INSERT INTO jouer (id_jeu, id_user, id_plateforme, nb_heure_joue)
        VALUES (?, ?, ?, 0)
    ");

    // Parcours des jeux sélectionnés
    foreach ($jeux as $nomJeu) {

        $stmtJeu->bind_param("s", $nomJeu);
        $stmtJeu->execute();
        $res = $stmtJeu->get_result();

        if ($row = $res->fetch_assoc()) {

            $id_jeu = $row['id_jeu'];

            $insertJeu->bind_param(
                "iii",
                $id_jeu,
                $id,
                $plateforme
            );

            $insertJeu->execute();
        }
    }

    /* =========================
       PLATEFORME : RESET + INSERT
    ========================= */

    // Supprime ancienne plateforme liée à l'utilisateur
    $stmt = $conn->prepare("DELETE FROM utiliser WHERE id_user=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Ajoute nouvelle plateforme
    $stmt = $conn->prepare("
        INSERT INTO utiliser (id_user, id_plateforme)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $id, $plateforme);
    $stmt->execute();

    /* =========================
       RÉPONSE SUCCESS
    ========================= */

    echo json_encode([
        "success" => true
    ]);

} catch (Exception $e) {

    /* =========================
       GESTION ERREUR
    ========================= */

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
