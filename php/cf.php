<?php
require "member.php";

$dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");

/* Controllo se l'identita' esiste */
$prepared=$dbh->prepare("SELECT COUNT(*) FROM anagrafica WHERE cf = ?");
$prepared->execute([$_POST['cf']]);
$counter=$prepared->fetch(PDO::FETCH_COLUMN);
if(!$counter) {
    echo "ko";
}
else {
    $prepared=$dbh->prepare("SELECT numero_tessera FROM socio WHERE cf = ?");
    $prepared->execute([$_POST['cf']]);
    $tessera=$prepared->fetch(PDO::FETCH_COLUMN);
    if(!$tessera)
        echo "0";
    else
        echo $tessera;
}
?>