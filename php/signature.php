<?php
require "member.php";

if(isset($_POST['firma'])) { //Se passo la firma sto salvando l'immagine della firma
    $uri=base64_decode($_POST['firma']);
    $birthday=str_replace("/", "", $_POST['birthday']);
    $filename=SIGNATURE_IMAGE_PATH.ucfirst(strtolower(($_POST['cognome']))).ucfirst(strtolower(($_POST['nome'])))."-".$birthday.".png";
    $ret="0";
    if(!file_exists($filename))
        $ret=file_put_contents($filename, $uri);
    if($ret===FALSE) //Errore scrittura file
        echo "ko";
    elseif($ret=="0") //Il file gia' esiste
        echo "0";
    else //File scritto correttamente
        echo "ok";
    die();
}
?>