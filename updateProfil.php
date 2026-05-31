<?php
session_start();
include 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $id = getUserId();

    if (!$id) {
        throw new Exception("Utilisateur inexistant");
    }

    /* =========================
       récupérer ancienne photo
    ========================= */
    $stmtPhoto = mysqli_prepare($conn,"
        SELECT photo
        FROM Utilisateur
        WHERE id_user=?
    ");

    mysqli_stmt_bind_param($stmtPhoto,"i",$id);
    mysqli_stmt_execute($stmtPhoto);

    $resPhoto = mysqli_stmt_get_result($stmtPhoto);
    $user = mysqli_fetch_assoc($resPhoto);

    $photoPath = $user['photo'] ?? null;


    /* =========================
       upload nouvelle photo
    ========================= */
    if(
        isset($_FILES['photo']) &&
        $_FILES['photo']['error'] == 0
    ){

        $allowed = [
            'image/jpeg',
            'image/png',
            'image/jpg'
        ];

        if(!in_array($_FILES['photo']['type'],$allowed)){
            throw new Exception("Format image invalide");
        }

        if($_FILES['photo']['size'] > 2000000){
            throw new Exception("Image trop lourde (max 2Mo)");
        }

        $uploadDir = "uploads/";

        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0777,true);
        }

        $fileName =
            time() . "_" .
            basename($_FILES['photo']['name']);

        $photoPath =
            $uploadDir . $fileName;

        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            $photoPath
        );
    }


    /* =========================
       récupérer données form
    ========================= */
    $day = $_POST['day'] ?? null;
    $month = $_POST['month'] ?? null;
    $year = $_POST['year'] ?? null;

    $bio = $_POST['bio'] ?? null;
    $humeur = $_POST['humeur'] ?? null;
    $personnageNom = $_POST['personnage'] ?? null;

    $jeux = $_POST['jeu'] ?? [];
    $plateforme = $_POST['platform'] ?? 1;


    /* =========================
       date naissance
    ========================= */
    $date = null;

    if ($day && $month && $year) {
        $date = "$year-$month-$day";
    }


    /* =========================
       vérifier personnage
    ========================= */
    $stmt = mysqli_prepare($conn,"
        SELECT nom_personnage
        FROM personnage
        WHERE nom_personnage=?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $personnageNom
    );

    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    if(!mysqli_fetch_assoc($res)){
        throw new Exception("Personnage non trouvé");
    }


    /* =========================
       update utilisateur
    ========================= */
    $stmt = mysqli_prepare($conn,"
        UPDATE Utilisateur
        SET
            date_naissance=?,
            bio=?,
            humeur=?,
            nom_personnage=?,
            photo=?
        WHERE id_user=?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $date,
        $bio,
        $humeur,
        $personnageNom,
        $photoPath,
        $id
    );

    mysqli_stmt_execute($stmt);


    /* =========================
       supprimer anciens jeux
    ========================= */
    $deleteJeux = mysqli_prepare($conn,"
        DELETE FROM jouer
        WHERE id_user=?
    ");

    mysqli_stmt_bind_param(
        $deleteJeux,
        "i",
        $id
    );

    mysqli_stmt_execute($deleteJeux);


    /* =========================
       récupérer id plateforme
    ========================= */
    $stmtPlat = mysqli_prepare($conn,"
        SELECT id_plateforme
        FROM plateforme
        WHERE id_plateforme=?
    ");

    mysqli_stmt_bind_param(
        $stmtPlat,
        "i",
        $plateforme
    );

    mysqli_stmt_execute($stmtPlat);

    $resPlat = mysqli_stmt_get_result($stmtPlat);

    if($rowPlat = mysqli_fetch_assoc($resPlat)){
        $id_plateforme = $rowPlat['id_plateforme'];
    } else {
        $id_plateforme = 1;
    }


    /* =========================
       ajouter nouveaux jeux
    ========================= */
    $stmtJeu = mysqli_prepare($conn,"
        SELECT id_jeu
        FROM jeu
        WHERE nom=?
    ");

    $insertJeu = mysqli_prepare($conn,"
        INSERT INTO jouer
        (id_jeu,id_user,id_plateforme,nb_heure_joue)
        VALUES (?, ?, ?, 0)
    ");

    foreach($jeux as $nomJeu){

        mysqli_stmt_bind_param(
            $stmtJeu,
            "s",
            $nomJeu
        );

        mysqli_stmt_execute($stmtJeu);

        $resJeu =
            mysqli_stmt_get_result($stmtJeu);

        if($row = mysqli_fetch_assoc($resJeu)){

            $id_jeu = $row['id_jeu'];

            mysqli_stmt_bind_param(
                $insertJeu,
                "iii",
                $id_jeu,
                $id,
                $id_plateforme
            );

            mysqli_stmt_execute($insertJeu);
        }
    }


    /* =========================
       update table utiliser
    ========================= */
    $deletePlat = mysqli_prepare($conn,"
        DELETE FROM utiliser
        WHERE id_user=?
    ");

    mysqli_stmt_bind_param(
        $deletePlat,
        "i",
        $id
    );

    mysqli_stmt_execute($deletePlat);


    $insertPlat = mysqli_prepare($conn,"
        INSERT INTO utiliser
        (id_user,id_plateforme)
        VALUES (?,?)
    ");

    mysqli_stmt_bind_param(
        $insertPlat,
        "ii",
        $id,
        $id_plateforme
    );

    mysqli_stmt_execute($insertPlat);


    echo json_encode([
        "success"=>true
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success"=>false,
        "error"=>$e->getMessage()
    ]);
}
?>