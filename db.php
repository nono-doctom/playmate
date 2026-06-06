<?php
//$host = "mysql-playmate1.alwaysdata.net";
//$dbname = "playmate1_db";
//$username = "playmate1";
//$password = "Killian13013@!";
$host = "localhost";
$dbname = "playmate";
$username ="root";
$password = "";
$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur connexion BDD");
}
?>