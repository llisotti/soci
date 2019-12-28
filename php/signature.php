<?php
require "member.php";

if(isset($_POST['firma'])) { //Se passo la firma sto salvando l'immagine della firma
    $uri=base64_decode($_POST['firma']); //convert base64($_POST['firma']);
    //$uri=base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['firma']));
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
elseif (isset($_POST['tessera'])){ //Se passo il numero tessera oppure tessera=0 sto inserendo/eliminando una tessera
    
    /* Setto la sessione di 5 ore */
    ini_set('session.gc_maxlifetime', 18000);
    //echo ini_get('session.gc_maxlifetime');
    
    /* Abilito il garbage collector con la probabilita' dell'1% di girare ad ogni session_start() */
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    /* Definisco un mio path dove dovra' essere creato il file della sessione */
    session_save_path(LOGFILE_PATH);
    
    /* Inizio sessione */
    session_start();
    
    $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
    
    if($_POST['tessera']) { //Se passo un numero di tessera (quindi diverso da 0) significa che voglio inserire una tessera
        $prepared=$dbh->prepare("UPDATE socio SET numero_tessera=?, data_tessera=? WHERE cf=?");
        $prepared->execute([$_POST['tessera'], date("Y-m-d"), $_POST['cf']]);
    }
    else { //Se cancello il numero tessera, lo richiedo per sapere quale sia...
        $prepared=$dbh->prepare("SELECT numero_tessera FROM socio WHERE cf=?");
        $prepared->execute([$_POST['cf']]);
        $tessera=$prepared->fetch(PDO::FETCH_COLUMN);
        if (($key = array_search($tessera, $_SESSION['members_evening'])) !== false) { //..in quanto se e' stato inserito in questa sessione devo conteggiare un socio in meno nella sessione stessa
            unset($_SESSION['members_evening'][$key]);
            $_SESSION['members_evening'] = array_values($_SESSION['members_evening']); //Reimposto l'array
        }
        $prepared=$dbh->prepare("UPDATE socio SET numero_tessera=?, data_tessera=? WHERE cf=?"); //Metto NULL il numero tessera e la data tessera
        $prepared->execute([NULL, NULL, $_POST['cf']]);
    }
        
    switch ($prepared->rowCount())
    {
        case -1: //Se la query restituisce -1...
        case 0: //...oppure le righe affette dalla quesry sono 0 (impossibile)...
            echo "ko"; //...allora errore
            break;
        default:            
            if($_POST['tessera']) { //Se ho inserito una tessera la aggiungo nella variabile di sessione del conteggio soci serata
                array_push($_SESSION['members_evening'], $_POST['tessera']);
                //$mylog->logInfo("Soci inseriti per questa sessione: ".++$maxkey);
            }
            echo "ok";
            break;
    }
    die();
}
?>