<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
require 'login.php';

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

/* Togliere commento per debug */
error_reporting(E_ALL);
ini_set("display_errors", 1);

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
    <li><span><span><a href= 'http://<?php echo $_SERVER['HTTP_HOST'] ?>/index.php'>Home</a></span></span></li>
    <li class="active"><span><span><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/php/profile_editor.php">Profilo</a></span></span></li>
    <li><span><span><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/php/newsletter.php">Newsletter</a></span></span></li>
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

        echo "<li><a href='http://{$_SERVER['HTTP_HOST']}/index.php'>Visualizza elenco iscritti ma non tesserati</a></li>";
    ?>
    <li><a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/index.php?show=allidentities">Visualizza elenco iscritti completo</a></li>
    <li><a id="DB_functions" href="" onclick="return false;" >Operazioni su DB</a></li> <!-- onclick="return false; evita che si ricarichi la pagina (altrimenti usare href="#" ma dopo mette il carattere # nella pagina e falsa eventuali variabili passate in GET) -->
    <!-- <li><a id="esporta_soci" href="#">Esporta soci</a></li> -->
    <!-- <li><a id="esporta_identita" href="#">Esporta identità</a></li> -->
    <li><a target="_blank" rel="noopener noreferrer" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/php/eXtplorer_2.1.13/index.php">Documenti</a></li>
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
	<!--
    <tr>
        <td colspan="2">N° SOCI SERATA</td>
    </tr>
    <tr>
        <td style="width: 137px; text-align: center" colspan="2">
        <h1>
        <span style="color: #F70">
        <?php
        /* Visualizzo la chiave dell'array che corrisponde al numero di soci inseriti */
        //$maxkey=max(array_keys($_SESSION['members_evening']));
        //echo "<a id='view' href='#' style='color:#F70'/>$maxkey</a>";
        ?>
        </span>
        </h1>
        </td>
    </tr>
    -->
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
<div class="top-bar">
<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/php/logout.php" class="button" title="<?php echo $_SESSION['username']?> - Clicca per uscire" /></a>
<h1><?php if(isset($_GET['id'])) echo "MODIFICA SOCIO"; else echo "INSERIMENTO NUOVO SOCIO"; ?></h1>
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

/**
 * Se si tenta di aggiornare il nummero tessera, controllo se esiste gia' quel numero tessera associato ad un altra persona
 */
try {  
    
    if($_POST['tessera'] != NULL) {
        
        /* Controllo se esiste gia' la tessera passata in POST */
        $prepared=$dbh->prepare("SELECT COUNT(*) FROM socio WHERE numero_tessera=?");
        $prepared->execute([$_POST['tessera']]);
        $counter=$prepared->fetch(PDO::FETCH_COLUMN);
        
        /* Se esiste, proseguo facendo altri controlli */
        if($counter) {
            $prepared=$dbh->prepare("SELECT cf FROM socio WHERE numero_tessera=?");
            $prepared->execute([$_POST['tessera']]);
            $cf=$prepared->fetch(PDO::FETCH_COLUMN);
            /**
             * Se c'e' il numero tessera ed il codice fiscale che passo in post cambia al massimo di 2 caratteri rispetto quello ottenuto dalla query
             * ipotizzo che il socio sia lo stesso in quanto potrei anche variare il codice fiscale
             * Se il codice fiscale cambia differisce piu' di 2 caratteri allora la tessera e' gia' occupata da un altro socio: lancio l'eccezione
             */
            if(similar_text($cf , $_POST['cf']) < 14)
                throw new Exception("Tessera gia' esistente");
        }
    }
}catch (Exception $e) {
    ?>
    <div style="display: flex;">
		<img src="../img/check_ko.png" height="256" width="256">
	</div>
	<?php
	errorMessage($e);
	die();
}


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

/* Creazione data di scadenza identità (anno corrente+5 - 12 - 31) */
$data=new DateTime(); //Data attuale (riciclo cariabile $data)
$data->format('Y');
$date_drop_identity=new DateTime();
$date_drop_identity->setDate($data->format('Y'), '12', '31');
$date_drop_identity->modify(DROP_IDENTITY); //Data scadenza identità
  
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

try {
    
    /* Se la tessera non esiste posso proseguire l'operazione */
    if($_POST['tessera'] != NULL) {
        $prepared=$dbh->prepare("REPLACE INTO socio (cf,
                                                        scadenza,
                                                        data_tessera,
                                                        numero_tessera,
                                                        adesioni,
                                                        firma)
                                                        VALUES(?, DATE_ADD(LAST_DAY(DATE_ADD(NOW(), INTERVAL 12-MONTH(NOW()) MONTH)),".DROP_IDENTITY_MYSQL."), ?, ?, ?, ?)");
        $prepared->execute([$_POST['cf'], date('Y')."-".$_POST['mm_inserimento']."-".$_POST['gg_inserimento'], $_POST['tessera'], $adesioni, ucfirst(strtolower(str_replace(" ","",$_POST['cognome']))).ucfirst(strtolower(str_replace(" ","",$_POST['nome'])))."-".$_POST['gg_nascita'].$_POST['mm_nascita'].$_POST['aaaa_nascita'].".png"]);
    }
    /**
     * Se non passo il numero tessera:
     * 1 - Se era socio allora cancello io la tessera: REPLACE vede cge esiste gia' la riga con quel codice fiscale quindi fallisce l'inserimento di una nuova riga perche' il codice fiscale deve essere univoco
     * REPLACE quindi cancella la riga con questo codice fiscale e ne inserisce una nuova mettendo NULL nelle colonne che io non passo nella query (ovvero data_tessera e numero_tessera)
     * In pratica e' proprio quello che voglio: cancellarne il tesseramento
     * 2 - Se non era socio aggiorno semplicemente i dati  
     */
    else {
        $prepared=$dbh->prepare("REPLACE INTO socio (cf,
                                                        scadenza,
                                                        adesioni,
                                                        firma)
                                                        VALUES(?, STR_TO_DATE(?, '%d/%m/%Y'), ?, ?)");
        $prepared->execute([$_POST['cf'], $member->scadenza, $adesioni, ucfirst(strtolower(str_replace(" ","",$_POST['cognome']))).ucfirst(strtolower(str_replace(" ","",$_POST['nome'])))."-".$_POST['gg_nascita'].$_POST['mm_nascita'].$_POST['aaaa_nascita'].".png"]);  
    }
}
catch (Exception $e) {
    ?>
    <div style="display: flex;">
		<img src="../img/check_ko.png" height="256" width="256">
	</div>
	<?php
	errorMessage($e);
	
	/* Se avevo inserito correttamente i dati in anagrafica li devo cancellare */
	if($member->cf != $_POST['cf']) //Se ho cambiato il codice fiscale devo cancellare 2 righe (REPLACE mi ha aggiunto una riga)
	   $dbh->query("DELETE FROM anagrafica WHERE cf= '$member->codice_fiscale' AND cf='$_POST[cf]'");
	else //altrimenti cancello solo una riga
	    $dbh->query("DELETE FROM anagrafica WHERE cf='$member->codice_fiscale'");	
	die();
}
?>
<div style="display: flex;">
	<img src="../img/check_ok.png" height="256" width="256">
</div>
<?php
successMessage();

/* Se ho cambiato il codice fiscale devo cancellare la riga con il vecchio codice fiscale in quanto REPLACE ne crea una nuova */
if($member->codice_fiscale != $_POST['cf']) {
    $dbh->query("DELETE FROM anagrafica WHERE cf='$member->codice_fiscale'");
    $dbh->query("DELETE FROM socio WHERE cf='$member->codice_fiscale'");
}    

/* Aggiorno il contatore di soci inseriti nella serata
if($_POST['aggiuntoSocio'] == 'aggiungi')
    array_push($_SESSION['members_evening'], $_POST['tessera']);
else if($_POST['aggiuntoSocio'] == 'cancella') {
    if (($key = array_search( $_POST['tessera'], $_SESSION['members_evening'])) !== false) { //se e' stato inserito in questa sessione devo conteggiare un socio in meno nella sessione stessa
        unset($_SESSION['members_evening'][$key]);
        $_SESSION['members_evening'] = array_values($_SESSION['members_evening']); //Reimposto l'array
    }
}
*/
//unset($_SESSION['socio']);
?>

<form action="http://<?php echo $_SERVER['HTTP_HOST'] ?>/index.php" method="get" > 
<span style="margin-left: 270px">
<input name="insert_member" value="Home page" type="submit" style="display: inline" >    
</span>
</form>
<?php 
function errorMessage(Exception $ex) {
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
        window.open('../php/root_functions.php?action=DB_functions','', "height=450,width=900");
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