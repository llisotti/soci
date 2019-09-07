<?php
require "member.php";

if(isset($_POST['firma'])) {
    $uri=base64_decode($_POST['firma']);
    $birthday=str_replace("/", "", $_POST['birthday']);
    $ret=file_put_contents(SIGNATURE_IMAGE_PATH.ucfirst(strtolower(($_POST['cognome']))).ucfirst(strtolower(($_POST['nome'])))."-".$birthday.".png", $uri);
    if($ret===FALSE)
        echo "ko";
    else
        echo "ok";
    die();
}
?>