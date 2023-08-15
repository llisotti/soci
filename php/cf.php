<?php
require "member.php";

$dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");

/* Controllo se l'identita' esiste */
$prepared=$dbh->prepare("SELECT COUNT(*) FROM anagrafica WHERE cognome = ? AND nome = ? AND data_nascita = STR_TO_DATE(?, '%d/%m/%Y')");
$prepared->execute([$_POST['cognome'], $_POST['nome'], $_POST['data_nascita']]);
$counter=$prepared->fetch(PDO::FETCH_COLUMN);
if(!$counter) {
    echo "ko";
}
else {
    /* L'identità esiste => ottengo l'id */
    $prepared=$dbh->prepare("SELECT id FROM anagrafica WHERE cognome = ? AND nome = ? AND data_nascita = STR_TO_DATE(?, '%d/%m/%Y')");
    $prepared->execute([$_POST['cognome'], $_POST['nome'], $_POST['data_nascita']]);
    $id=$prepared->fetch(PDO::FETCH_COLUMN);

    /* Verifico se è tesserato o meno */
    $prepared=$dbh->prepare("SELECT numero_tessera FROM socio WHERE id = ?");
    $prepared->execute([$id]);
    $tessera=$prepared->fetch(PDO::FETCH_COLUMN);
    if(!$tessera)
        echo "0";
    else
        echo $tessera;
}
?>