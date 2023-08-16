<?php
require "member.php";

$dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");

/* Controllo se l'identita' esiste */
$prepared=$dbh->prepare("DELETE FROM anagrafica WHERE id = ?");
$prepared->execute([$_POST['id']]);
$count = $prepared->rowCount();
if(!$count) {
    echo "koAnagrafica";
}
else
{
    /* Controllo se l'identita' esiste */
    $prepared=$dbh->prepare("DELETE FROM socio WHERE id = ?");
    $prepared->execute([$_POST['id']]);
    $count = $prepared->rowCount();
    if(!$count) {
        echo "koSocio";
    }
    else
        echo $count;
}
?>