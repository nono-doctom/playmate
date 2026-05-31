1. Fonction calcul du score
<?php
function calculScore($A, $B) {

    $score = 0;

    if ($A['genre'] == $B['genre']) {
        $score += 30;
    } else {
        $score -= 30;
    }
    if ($A['plateforme'] == $B['plateforme']) {
        $score += 20;
    } else {
        $score -= 20;
    }

    $ecart = abs($A['heuresJeu'] - $B['heuresJeu']);

    if ($ecart < 5) {
        $score += 25;
    } elseif ($ecart < 10) {
        $score += 10;
    } else {
        $score -= 25;
    }

    $ecartJour = abs($A['heuresParJour'] - $B['heuresParJour']);

    if ($ecartJour < 1) {
        $score += 15;
    } elseif ($ecartJour < 2) {
        $score += 5;
    } else {
        $score -= 15;
    }

    if ($A['style'] == $B['style']) {
        $score += 10;
    } else {
        $score -= 10;
    }

    if ($score < 0) $score = 0;
    if ($score > 100) $score = 100;

    return $score;
}
?>
2. Trouver des matchs
<?php
function trouverMatchs($pdo, $id_user) {

    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_user = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM Utilisateur WHERE id_user != $id_user");
    $autres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matchs = [];

    foreach ($autres as $u) {

        $score = calculScore($user, $u);

        if ($score >= 50) {
            $matchs[] = [
                'user' => $u,
                'score' => $score
            ];
        }
    }

    usort($matchs, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $matchs;
}
?>
<?php
function liker($pdo, $A, $B, $avis) {

    $check = $pdo->prepare("SELECT * FROM Matcher WHERE id_user = ? AND id_user_1 = ?");
    $check->execute([$A, $B]);

    if ($check->rowCount() == 0) {

        $stmt = $pdo->prepare("INSERT INTO Matcher (id_user, id_user_1, avis) VALUES (?, ?, ?)");
        $stmt->execute([$A, $B, $avis]);

        $checkMatch = $pdo->prepare("SELECT * FROM Matcher WHERE id_user = ? AND id_user_1 = ? AND avis = 'like'");
        $checkMatch->execute([$B, $A]);

        if ($avis == "like" && $checkMatch->rowCount() > 0) {
            return "MATCH ! ";
        }

        return "Avis enregistré";
    }

    return "Déjà voté";
}
?>
<?php
function envoyerMessage($pdo, $expediteur, $destinataire, $contenu) {

    $stmt = $pdo->prepare("
        SELECT * FROM Matcher 
        WHERE id_user = ? AND id_user_1 = ? AND avis = 'like'
    ");
    $stmt->execute([$expediteur, $destinataire]);

    $stmt2 = $pdo->prepare("
        SELECT * FROM Matcher 
        WHERE id_user = ? AND id_user_1 = ? AND avis = 'like'
    ");
    $stmt2->execute([$destinataire, $expediteur]);

    if ($stmt->rowCount() > 0 && $stmt2->rowCount() > 0) {

        $insert = $pdo->prepare("
            INSERT INTO Message (contenu, date_mess, id_expediteur, id_destinataire)
            VALUES (?, NOW(), ?, ?)
        ");

        $insert->execute([$contenu, $expediteur, $destinataire]);

        return "Message envoyé ";
    }

    return "Pas de match ";
}
?>
Exemple d’utilisation
<?php
require 'connexion.php';

$matchs = trouverMatchs($pdo, 1);

foreach ($matchs as $m) {
    echo $m['user']['pseudo'] . " - Score: " . $m['score'] . "<br>";
}

echo liker($pdo, 1, 2, "like");
echo envoyerMessage($pdo, 1, 2, "Salut !");
?>