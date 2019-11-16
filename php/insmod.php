<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !

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
session_cache_limiter('private,must-revalidate');

?>
<!DOCTYPE html>
<html>
<head>
<title>Gruppo Astrofili "N. Copernico"</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/insmod.css" media="all"/>
</head>
<body>
<?php
/* Richiedo il logger */
$mylog=$_SESSION['logger'];


/* Mi connetto al database FIXME Occhio se sbaglio connessione a non far apparire in chiaro l'errore*/
try {
    $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "",[PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}
catch (PDOException $e) {               
    ?>
    <div style="display: flex; justify-content: center;">
		<img src="../img/check_ko.png" height="256" width="256">
	</div>
	<?php
	errorMessage($e);
	die();
}
?>
<div id="main">
<div id="header">
<a class="logo"><img src="../img/logo_copernico.jpg" width="300" height="54" alt="" /></a>
<a class="version" alt="<?php echo $_SESSION['local_commit_hash']; ?>" title="<?php echo $_SESSION['local_commit_hash']; ?>"><?php echo VERSION; ?></a>
<ul id="top-navigation">
    <li><span><span><a href= 'http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/index.php'>Home</a></span></span></li>
    <li class="active"><span><span><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/php/profile_editor.php">Profilo</a></span></span></li>
    <li><span><span><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/php/newsletter.php">Newsletter</a></span></span></li>
    <!--
    <li><span><span><a href="#">Statistiche</a></span></span></li>
    <li><span><span><a href="#">Opzioni</a></span></span></li>
    <li><span><span><a href="#">Statistics</a></span></span></li>
    <li><span><span><a href="#">Design</a></span></span></li>
    <li><span><span><a href="#">Contents</a></span></span></li>
    -->
</ul>
</div>
<div id="middle">
<div id="left-column">
<h3>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Funzionalità</h3>
<ul class="nav">
    <?php

        echo "<li><a href='http://{$_SERVER['HTTP_HOST']}/soci/index.php'>Visualizza elenco iscritti ma non tesserati</a></li>";
    ?>
    <li><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/index.php?show=allidentities">Visualizza elenco iscritti completo</a></li>
    <li><a id="esporta_soci" href="#">Esporta soci</a></li>
    <li><a id="esporta_identita" href="#">Esporta identità</a></li>
    <li><a target="_blank" rel="noopener noreferrer" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/php/eXtplorer_2.1.13/index.php">Documenti</a></li>
    <li><a id="DB_functions" href="#">Operazioni su database</a></li>
    <?php
    if($_SESSION['update']) {
        ?>
        <li class="last"><a href="./root_functions.php?action=update">Aggiornamento sw *</a></li>
        <?php
    }
    else {
        ?>
        <li class="last"><a href="#">Aggiornamento sw</a></li>
        <?php
    }
    ?>
</ul>
<table class="counter">
    <tr>
        <td colspan="2">N° SOCI SERATA</td>
    </tr>
    <tr>
        <td style="width: 137px; text-align: center" colspan="2">
        <h1>
        <span style="color: #F70">
        <?php
        /* Visualizzo la chiave dell'array che corrisponde al numero di soci inseriti */
        $maxkey=max(array_keys($_SESSION['members_evening']));
        echo "<a id='view' href='#' style='color:#F70'/>$maxkey</a>";
        ?>
        </span>
        </h1>
        </td>
    </tr>
    <tr>
        <td colspan="2"><br/><br/></td>
    </tr>
    <tr>
        <td style="width: 137; text-align: center" colspan="2">N° SOCI <?php $time=getdate(); echo $time['year'] ?></td>
    </tr>
    <tr>
    <?php
    /* Conto i soci ovvero le righe di anagrafica che hanno la tessera per l'anno corrente */
    $members=$dbh->query("SELECT COUNT(*) FROM socio WHERE numero_tessera IS NOT NULL");
    $counter=$members->fetchColumn();
    ?>
        <td id="view_drop_cards" style="width: 137px; text-align: center" colspan="2"><h1><a href="#"><span style="color: #F70"><?php echo $counter; if (!empty($_SESSION['breakCards'])) { echo "<sup>+".count($_SESSION['breakCards'])."</sup>";}?></span></h1></a></td>
    </tr>
    <tr>
        <td colspan="2"><br/><br/><br/><br/></td>
    </tr>
    <tr>
    <td>   
        <img style="border:0;width:32px;height:32px" src="../img/HTML5_Logo.png" alt="HTML5 compliance code!" title="HTML5 compliance code!" />
    </td> 
    <td>   
        <img style="border:0;width:88px;height:31px" src="../img/vcss.gif" alt="CSS Valido!" title="CSS Valido!" />
    </td>        
    </tr>
</table>
</div>
<div id="center-column">
<div class="top-bar"> <h1><?php if(isset($_GET['id'])) echo "MODIFICA SOCIO"; else echo "INSERIMENTO NUOVO SOCIO"; ?></h1><br/>
</div>
<br />
<div class="select-bar">
<input type="hidden" name="" value=""/>
<br/>
<?php
/* Richiedo il socio salvato in profile_editor: mi serve per diversi dati */
$member=$_SESSION['member'];

/* Ricreo la data di nascita dai campi gg_nascita, mm_nascita, aaaa_nascita */
$data_nascita=$_POST['aaaa_nascita']."-".$_POST['mm_nascita']."-".$_POST['gg_nascita'];

/* Inizializzo un array di formattazione */
$format_input=array("cognome" => "FUC",
                    "nome" => "FUC",
                    "cf" => "UC",
                    "comune_nascita" => "FUC",
                    "provincia_nascita" => "UC",
                    "stato_nascita" => "UC",
                    "sesso" => "UC",
                    "indirizzo" => "FUC",
                    "citta" => "FUC",
                    "cap" => "UC",
                    "provincia" => "UC",
                    "stato" => "UC",
                    "email" => "LC");                               

/* Formatto i dati provenienti da $_POST */
$formatter= new InputFormat($format_input);
$formatter->format($_POST);

/* Prelevo i numeri di tessera ed i member_id
$results=$dbh->query("SELECT member_id, tessera FROM anagrafica");
$rows=$results->fetchAll();*/

/* Inserisco i dati in anagrafica
 * ATTENZIONE: con REPLACE se la primary key (codice fiscale) gia' esiste allora aggiorno la riga
 * altrimenti creo una riga nuova. In pratica se cambio il codice fiscale creo una nuova riga
 * => devo cancellare la vecchia riga */
/**
 * Aggiorno i dati in anagrafica
 * ATTENZIONE: con REPLACE se la primary key (codice fiscale) gia' esiste allora aggiorno la riga
 * altrimenti creo una riga nuova. In pratica se cambio il codice fiscale creo una nuova riga
 * Quindi se cambio il codice fiscale creo una nuova riga => devo cancellare la vecchia riga!!
 * ATTENZIONE: se aggiorno il nome oppure il cognome oppure la data di nascita
 * devo aggiornare anche il nome della firma tramite file manager altrimenti non mi carica la firma in profile_editor
 */
try {
    $prepared=$dbh->prepare("REPLACE INTO anagrafica (cognome,
                                                                nome,
                                                                data_nascita,
                                                                cf,
                                                                comune_nascita,
                                                                provincia_nascita,
                                                                stato_nascita,
                                                                sesso,
                                                                indirizzo,
                                                                citta,
                                                                cap,
                                                                provincia,
                                                                stato,
                                                                telefono,
                                                                email)
                                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $prepared->execute([$_POST['cognome'],
        $_POST['nome'],
        $data_nascita,
        $_POST['cf'],
        $_POST['comune_nascita'],
        $_POST['provincia_nascita'],
        $_POST['stato_nascita'],
        NULL,
        $_POST['indirizzo'],
        $_POST['citta'],
        $_POST['cap'],
        $_POST['provincia'],
        $_POST['stato'],
        $_POST['telefono'],
        $_POST['email'],
    ]);
}
catch (PDOException $e) {
    ?>
    <div style="display: flex; justify-content: center;">
		<img src="../img/check_ko.png" height="256" width="256">
	</div>
	<?php
	errorMessage($e);
	die();
}
/* Sto aggiornando i dati di un socio oppure da identità diventa socio
if (isset($_POST['tessera']) && $_POST['tessera'] != NULL) //Se ha la tessera aggiorno i dati del socio o aggiorno il numero tessera
{   
    $member->cognome=$dbh->quote($_POST['cognome']); //Quoto il cognome
    $member->nome=$_POST['nome'];
}
/* Sto inserendo un nuovo socio
else //Se non ha la tessera aggiorno i dati di una identita'
{
    try
    {
        $member=new Socio_Copernico($_POST['cognome'], $_POST['nome']); 
    } catch (MyException $ex) {
        die($ex->show());
    }
    
    $member->cognome=$dbh->quote($_POST['cognome']); //Quoto il cognome
}
*/
/* Sia che aggiorno sia che inserisco un nuovo socio popolo il socio con i campi passati in POST da profile_editor dopo aver eseguito gli opportuni controlli
$member->luogo_nascita=$_POST['luogo_nascita'];
if (isset($_POST['sesso']))
    $member->sesso=$_POST['sesso'];
else
    $member->sesso="";      
$member->codice_fiscale=$_POST['cf'];
$member->indirizzo=$dbh->quote($_POST['indirizzo']); //Quoto l'indirizzo
$member->cap=$_POST['cap'];
$member->citta=$dbh->quote($_POST['citta']); //Quoto la città
$member->provincia=$_POST['provincia'];
$member->stato=$_POST['stato'];
$member->telefono=$_POST['phone'];
$member->email=$_POST['email'];
*/

/* Creazione data di scadenza identità (anno corrente+5 - 12 - 31) */
$data=new DateTime(); //Data attuale (riciclo cariabile $data)
$data->format('Y');
$date_drop_identity=new DateTime();
$date_drop_identity->setDate($data->format('Y'), '12', '31');
$date_drop_identity->modify(DROP_IDENTITY); //Data scadenza identità
  
//$member->scadenza_id=$date_drop_identity->format('Y-m-d');

/* Gestione data tessera e data iscrizione
$date_ins=DateTime::createFromFormat('Y-m-d', "$this_year-$_POST[mm_inserimento]-$_POST[gg_inserimento]");
if(!isset($_GET['id'])) { //Se inserisco un nuovo socio... 
    $member->data_tessera=$date_ins->format('Y-m-d');
    $member->data_iscrizione=$date_ins->format('Y-m-d');
    $aaaa_iscrizione=$this_year;
}
else { //...oppure da identità a socio oppure sto aggiornando un socio
    $member->data_tessera=$date_ins->format('Y-m-d');; 
    $aaaa_iscrizione=substr($member->data_iscrizione, '6','4');
}

/* Sentinella socio cambio tessera e da identità a socio
$update_card=FALSE;
$id_to_member=FALSE;
if($member->tessera!=NULL && $member->tessera!=$_POST['tessera']) //Un socio cambia numero di tessera
    $update_card=TRUE;
elseif($member->tessera==NULL && $_POST['tessera']!=NULL) //Un' identità vuole diventare socio
    $id_to_member=TRUE;


/* Controllo che in caso di cambio tessera o inserimento nuovo socio il numero tessera già non esista
if($update_card || $id_to_member) {
    foreach ($rows as $card) {
        if($_POST['tessera']==$card['tessera'] && $member->id!=$card['member_id']) {  //Se la tessera è uguale e il socio non è lo stesso
            if(isset($_GET['id']))
                $mylog->logError("Tentativo di aggiornamento socio fallito (ID:".$_GET['id']."), numero di tessera già esistente (TESSERA:".$_POST['tessera'].")");
            else
                $mylog->logError("Tentativo inserimento nuovo socio fallito (COGNOME:".$_POST['cognome']." NOME:".$_POST['nome']."), numero di tessera già esistente (TESSERA:".$_POST['tessera'].")");
            die("Numero di tessera già esistente");
        }
    }
}

$vecchia_tessera=$member->tessera; //Prima di sovrascrivere recupero la vecchia tessera per il log
$member->tessera=$_POST['tessera'];
*/
/* Leggo i flag di adesione */
try {
    $prepared=$dbh->prepare("SELECT CAST(adesioni AS unsigned integer) FROM socio WHERE cf=?");
    $prepared->execute([$_POST['cf']]);
}
catch (PDOException $e) {
    ?>
                <div style="display: flex; justify-content: center;">
            		<img src="../img/check_ko.png" height="256" width="256">
        		</div>
	<?php 
	errorMessage($e);
	die();
}
            
/* Trasformo la stringa in unsigned char e faccio la verifica bit a bit */
$adesioni=pack('C', $prepared->fetch(PDO::FETCH_COLUMN));                                      
isset($_POST['diffusione_nominativo']) ? $adesioni|=1 : $adesioni&=254;
isset($_POST['newsletter']) ? $adesioni|=2 : $adesioni&=253;
$_POST['etaconsenso'] == "minorenne" ?  $adesioni|=4 : $adesioni&=251;

/* Inserisco i dati in socio */
try {
    //$dbh->query("UPDATE socio SET cf='$_POST[cf]', adesioni='$adesioni' WHERE cf='$member->codice_fiscale'");                
//$_POST['cognome'].$_POST['nome']."-".str_replace("/", "", $_POST['gg_nascita'].$_POST['mm_nascita'].$_POST['aaaa_nascita']).".png
    }
catch (PDOException $e) {               
    ?>
    <div style="display: flex;">
		<img src="../img/check_ko.png" height="256" width="256">
	</div>
	<?php
	/* Se ho inserito correttamente i dati in anagrafica li devo cancellare */
	$prepared=$dbh->prepare("DELETE FROM anagrafica WHERE cf=?");
	$prepared->execute([$_POST['cf']]);
	errorMessage($e);
	die();
}

try {
    /**
     * Se era gia' un socio (ha la tessera) allora ne aggiorno i dati
     * Se da identita' diventa socio (scrivo io la tessera) aggiungo la tessera, la data tessera ed eventualmente aggiorno i dati
     * ATTENZIONE: se aggiorno il nome oppure il cognome oppure la data di nascita nella tabella anagrafica
     * devo aggiornare anche il nome della firma tramite file manager altrimenti non mi carica la firma in profile_editor
     */
    if($_POST['tessera'] != NULL) {
        $prepared=$dbh->prepare("REPLACE INTO socio (cf,
                                                        scadenza,
                                                        data_tessera,
                                                        numero_tessera,
                                                        adesioni,
                                                        firma)
                                                        VALUES(?, DATE_ADD(LAST_DAY(DATE_ADD(NOW(), INTERVAL 12-MONTH(NOW()) MONTH)),".DROP_IDENTITY_MYSQL."), ?, ?, ?, ?)");
        $prepared->execute([$_POST['cf'], date('Y')."-".$_POST['mm_inserimento']."-".$_POST['gg_inserimento'], $_POST['tessera'], $adesioni, $_POST['cognome'].$_POST['nome']."-".$_POST['gg_nascita'].$_POST['mm_nascita'].$_POST['aaaa_nascita'].".png"]);
    }
    /* Se non e' un socio allora voglio solo aggiornare i dati dell'identita' */
    else {
        $prepared=$dbh->prepare("REPLACE INTO socio (cf,
                                                        scadenza,
                                                        adesioni,
                                                        firma)
                                                        VALUES(?, STR_TO_DATE(?, '%d/%m/%Y'), ?, ?)");
        $prepared->execute([$_POST['cf'], $member->scadenza, $adesioni, $_POST['cognome'].$_POST['nome']."-".$_POST['gg_nascita'].$_POST['mm_nascita'].$_POST['aaaa_nascita'].".png"]);  
    }
}
catch (PDOException $e) {
    ?>
    <div style="display: flex;">
		<img src="../img/check_ko.png" height="256" width="256">
	</div>
	<?php
	/* Se avevo inserito correttamente i dati in anagrafica li devo cancellare */
	if($member->cf != $_POST['cf']) //Se ho cambiato il codice fiscale devo cancellare 2 righe (REPLACE mi ha aggiunto una riga)
	   $dbh->query("DELETE FROM anagrafica WHERE cf= '$member->codice_fiscale' AND cf='$_POST[cf]'");
	else //altrimenti cancello solo una riga
	    $dbh->query("DELETE FROM anagrafica WHERE cf='$member->codice_fiscale'");
	errorMessage($e);
	die();
}
?>
<div style="display: flex;">
	<img src="../img/check_ok.png" height="256" width="256">
</div>
<?php
/* Se ho cambiato il codice fiscale devo cancellare la riga con il vecchio codice fiscale in quanto REPLACE ne crea una nuova */
if($member->codice_fiscale != $_POST['cf']) {
    $dbh->query("DELETE FROM anagrafica WHERE cf='$member->codice_fiscale'");
    $dbh->query("DELETE FROM socio WHERE cf='$member->codice_fiscale'");
}    
successMessage();
        	  


/* Sto aggiornando i dati di un socio oppure da identità a socio 
if (isset($_GET['id']))
{   
    $membersobj=$dbh->query("UPDATE anagrafica SET cognome=$member->cognome
                                                    , nome='$member->nome'
                                                    , luogo_nascita='$member->luogo_nascita'
                                                    , sesso='$member->sesso'
                                                    , cf='$member->codice_fiscale'
                                                    , indirizzo=$member->indirizzo
                                                    , cap='$member->cap'
                                                    , citta=$member->citta
                                                    , provincia='$member->provincia'
                                                    , stato='$member->stato'
                                                    , telefono='$member->telefono'
                                                    , email='$member->email' WHERE member_id='$_GET[id]' 
                            ");
    $mylog->logInfo("Tentativo di aggiornare un socio (ID:".$_GET['id'].")");
}
/* Sto inserendo un nuovo socio 
else
{
    $membersobj=$dbh->query("INSERT INTO anagrafica (cognome
                                                    , nome
                                                    , luogo_nascita
                                                    , sesso
                                                    , cf
                                                    , indirizzo
                                                    , cap
                                                    , citta
                                                    , provincia
                                                    , stato
                                                    , telefono
                                                    , email
                                                    , tessera
                                                    , scadenza)
                                            VALUES ($member->cognome
                                                    , '$member->nome'
                                                    , '$member->luogo_nascita'
                                                    , '$member->sesso'
                                                    , '$member->codice_fiscale'
                                                    , $member->indirizzo
                                                    , '$member->cap'
                                                    , $member->citta
                                                    , '$member->provincia'
                                                    , '$member->stato'
                                                    , '$member->telefono'
                                                    , '$member->email'
                                                    , '$member->tessera'
                                                    , '$member->scadenza_id')
                            ");
}

/* Metto nuovamente i campi senza quote 
$member->cognome=$_POST['cognome'];
$member->indirizzo=$_POST['indirizzo'];
$member->citta=$_POST['citta'];

/* Se query precedente OK 
if ($membersobj != FALSE)
{
    /* Chiedo l'ultimo member_id inserito: se nuovo socio lo utilizzo altrimento no (non andrebbe neanche bene) 
    $last_member_id=$dbh->lastInsertId();
   
    /* Sto aggiornando un socio oppure identità diventa socio 
    if (isset($_GET['id']))
    {
        /* Gestione cambio data tessera 
        if ($aaaa_iscrizione!=$this_year) //Se l'anno della prima iscrizione è diverso dall'anno corrente (era un'identità) aggiorno solo la data della tessera
            $membersobj=$dbh->query("UPDATE presenze SET data='$member->data_tessera' WHERE member_id='$_GET[id]'");
        else //Se l'anno della prima iscrizione è uguale all'anno corrente aggiorno la data della tessera e la data di iscrizione
            $membersobj=$dbh->query("UPDATE presenze SET data='$member->data_tessera', iscrizione='$member->data_tessera' WHERE member_id='$_GET[id]'");
        
        /* La data di nascita non è stata definita nel form quindi la metto NULL 
        if ($_POST['gg_nascita']=="GG" || $_POST['mm_nascita']=="MM" || empty($_POST['aaaa_nascita']))
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita=NULL WHERE member_id='$_GET[id]'");
        /* la data di nascita è definita, la metto nel formato per MySql: AAAA-MM-GG e poi la rimetto nel formato GG/MM/AAAA 
        else
        {
            $date=DateTime::createFromFormat('Y-m-d', "$_POST[aaaa_nascita]-$_POST[mm_nascita]-$_POST[gg_nascita]"); //RICICLO $date
            $member->data_nascita=$date->format('Y-m-d');
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita='$member->data_nascita' WHERE member_id='$_GET[id]'");
            $member->data_nascita=$date->format('d/m/Y'); //rimetto il formato data di nascita GG/MM/AAA in oggetto $member !!

        }
        if(isset($_POST['tessera']) && $update_card) { //Il socio cambia numero tessera
            $membersobj=$dbh->query("UPDATE anagrafica SET tessera='$member->tessera' WHERE member_id='$_GET[id]'");
            $mylog->loginfo("Tentativo cambio tessera socio (ID:".$_GET['id']." VECCHIA TESSERA:".$vecchia_tessera." NUOVA TESSERA:".$member->tessera.")");
        }
        elseif(isset($_POST['tessera']) && $id_to_member) //Da identità diventa socio
        {
            $last_member_id=$date_drop_identity->format('Y-m-d'); //RICICLO $last_member_id
            $membersobj=$dbh->query("UPDATE anagrafica SET scadenza='$last_member_id', tessera='$member->tessera' WHERE member_id='$_GET[id]'");
            //$membersobj=$dbh->query("UPDATE presenze SET data='$member->data_tessera' WHERE member_id='$_GET[id]'");
            $mylog->loginfo("Tentativo identità diventa socio (ID:".$_GET['id']." TESSERA:".$member->tessera.")");
        }
        
        if (!$membersobj) {
            echo '<img src="../img/check_ko.png" height="256" width="256" alt="check_ko">';
            $mylog->logError("Tentativo fallito");
        }
         else {
            echo '<img src="../img/check_ok.png" height="256" width="256" alt="check_ok">';
            $mylog->logInfo("Tentativo riuscito");
         }
    }
    /* Sto inserendo un nuovo socio (in pratica aggiorno quello appena inserito) 
    else
    {
        $member->id=$last_member_id;
        $membersobj=$dbh->query("INSERT INTO presenze (data, iscrizione, member_id) VALUES ('$member->data_tessera', '$member->data_iscrizione', '$member->id')");
        /* La data di nascita non è stata definita nel form quindi la metto NULL 
        if ($_POST['gg_nascita']=="GG" || $_POST['mm_nascita']=="MM" || empty($_POST['aaaa_nascita']))
        {
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita=NULL WHERE member_id='$$member->id'");
        }
        /* la data di nascita è definita, la metto nel formato per MySql: AAAA-MM-GG e poi la rimetto nel formato GG/MM/AAAA
        else
        {
            $date=DateTime::createFromFormat('Y-m-d', "$_POST[aaaa_nascita]-$_POST[mm_nascita]-$_POST[gg_nascita]"); //RICICLO $date
            $member->data_nascita=$date->format('Y-m-d');
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita='$member->data_nascita' WHERE member_id='$member->id'");
            $member->data_nascita=$date->format('d/m/Y'); //rimetto il formato data di nascita GG/MM/AAA in oggetto $member !!

        }
        if (!$membersobj) {
            echo '<img src="../img/check_ko.png" height="256" width="256" alt="check_ko">';          
            $mylog->logError("Tentativo inserimento nuovo socio fallito (".$member->cognome." ".$member->nome." con tessera ".$member->tessera.")");
        }
        else {
            echo '<img src="../img/check_ok.png" height="256" width="256" alt="check_ok">';
            $mylog->logInfo("Inserito nuovo socio (ID:".$member->id." COGNOME:".$member->cognome." NOME:".$member->nome." TESSERA:".$member->tessera.")");
        }
    }

    /* Aggiorno il contatore di soci inseriti nella serata 
    if(!isset($_GET['id']) || $id_to_member) {
        //$_SESSION['members_evening']++;
        array_push($_SESSION['members_evening'], $member->tessera);
        $mylog->logInfo("Soci inseriti per questa sessione: ".++$maxkey);
    }
}
else {
    echo '<img src="../img/check_ko.png" height="256" width="256" alt="check_ko">';
    if(isset($_GET['id']))
        $mylog->logError("Tentativo inserimento/aggiornamento socio fallito (".$_POST['cognome']." ".$_POST['nome'].")");
    else
        $mylog->logError("Tentativo inserimento nuovo socio fallito (".$_POST['cognome']." ".$_POST['nome'].")");
}
*/
//unset($_SESSION['socio']);
?>

<form action="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/index.php" method="get" > 
<span style="margin-left: 270px">
<input name="insert_member" value="Home page" type="submit" style="display: inline" >    
</span>
</form>
<?php 
function errorMessage(PDOException $ex) {
    echo "La procedura di iscrizione e' fallita con codice errore: ".$ex->getMessage();
}

function successMessage() {
    echo "<br><ul style='font-family: Arial; font-size:15px align:center'>";
    echo "Operazione conclusa correttamente";
    echo "</ul><br>";
}
?>
</div>
</div>
<div id="footer">
</div>
</div>
</div>
<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<script type="text/javascript">
$(document).ready(function(){

     /* Funzione di gestione esportazione elenco soci */
    $("a#esporta_soci").click(function() {
        window.open('../php/root_functions.php?action=members_export','', "height=190,width=580");
    });
    
    
    /* Funzione di gestione esportazione elenco identità*/
    $("a#esporta_identita").click(function() {
        window.open('../php/root_functions.php?action=identities_export','', "height=190,width=580");
    });
    
    
    /* Funzione di creazione backup */
    $("a#DB_functions").click(function() {
        window.open('../php/root_functions.php?action=DB_functions','', "height=190,width=580");
    });
    
    
    /* Funzione visualizzazione tessere inserite nella sessione */
    $("a#view").click(function() {
        window.open('../php/root_functions.php?action=view_members_evening','', "height=190,width=580,scrollbars=1");
    });
        
    
    /* Funzione visualizzazione numeri di tessera mancanti */
    $("td#view_drop_cards").click(function() {
        window.open('../php/root_functions.php?action=view_drop_cards','', "height=190,width=580,scrollbars=1");
    });
    
    
    /* Funzione per ritornare alla pagina di inserimento nuovo socio premendo il tasto Enter
    $(document).keydown( function(e) {
    if (e.keyCode === 13) {
        window.location.href='http://192.168.1.4/soci/php/profile_editor.php';
    }
    });
    */
});
</script>
</body>
</html>