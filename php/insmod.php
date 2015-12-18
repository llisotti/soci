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
/* RIchiedo il logger */
$mylog=$_SESSION['logger'];

/* Se nel form di profile_editor è stato cliccato annulla torno alla pagina precedente */
if(!empty($_POST['clean'])) {
    if(!empty($_GET['id']))
        header("Location: http://localhost/soci/php/profile_editor.php?id=$_GET[id]"); //Con id
    else
        header("Location: http://localhost/soci/php/profile_editor.php"); //Senza id
    die(); //Fondamentale, altrimenti lo script continua
}

/* Mi connetto al database */
try {
    if(!isset($dbh))
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
}
catch (PDOException $exception) {
    $mylog->logError("Errore di connessione al database: ".$exception->getMessage());
    die("Errore di connessione al database: ".$exception->getMessage());
}
?>
<div id="main">
<div id="header">
<a class="logo"><img src="../img/logo_copernico.jpg" width="300" height="54" alt="" /></a>
<a class="version" alt="<?php echo $_SESSION['local_commit_hash']; ?>" title="<?php echo $_SESSION['local_commit_hash']; ?>"><?php echo VERSION; ?></a>
<ul id="top-navigation">
    <li><span><span><a href= 'http://localhost/soci/index.php'>Home</a></span></span></li>
    <li class="active"><span><span><a href="http://localhost/soci/php/profile_editor.php">Profilo</a></span></span></li>
    <li><span><span><a href="http://localhost/soci/php/newsletter.php">Newsletter</a></span></span></li>
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
    if(!isset($_GET['allmembers']) || $_GET['allmembers']!="true")
            echo "<li><a href='http://localhost/soci/index.php?allmembers=true'>Visualizza elenco soci completo</a></li>";
    elseif($_GET['allmembers']=="true")
            echo "<li><a href='http://localhost/soci/index.php'>Visualizza elenco ultimi"." ".MEMBERS_RECENT_MAX. " "."soci</a></li>";
    ?>
    <li><a href="http://localhost/soci/index.php?show=allidentities">Visualizza elenco identità completo</a></li>
    <li><a id="esporta_soci" href="#">Esporta elenco soci completo</a></li>
    <li><a id="esporta_identita" href="#">Esporta elenco identità completo</a></li>
    <li><a id="DB_functions" href="#">Operazioni sul DB</a></li>
    <?php
    if($_SESSION['update']) {
        ?>
        <li class="last"><a href="./root_functions.php?action=update">Aggiornamento sw (*)</a></li>
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
    $members=$dbh->query("SELECT COUNT(*) FROM anagrafica WHERE tessera IS NOT NULL");					
    ?>
        <td style="width: 137px; text-align: center" colspan="2"><h1><span style="color: #F70"><?php echo $members->fetchColumn(); ?></span></h1></td>
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
/* Inizializzo un array di controllo tipo */
$control_input=array("tessera" => "int", "cognome" => "stringchar",
                        "nome" => "stringchar", "aaaa_nascita" => "stringnum",
                        "luogo_nascita" => "stringchar",
                        "citta" => "stringchar", "provincia" => "stringchar",
                        "stato" => "stringchar", "telefono" => "stringnum");

/* Controllo la validità del tipo e del formato dei dati provenienti da $_POST */
$validator=new InputValidate($control_input);
try {
    $validator->isValidType($_POST);
    $control_input=array("email" => "email");
    $validator->updateControl($control_input);
    $validator->isValidFormat($_POST);
    $validator->isValidLenght($_POST['cf'], 16, 1);
} catch (MyException $exc) {
    if(!isset($_GET['id']))
        $mylog->logError("Tentativo di inserimento nuovo socio fallito (COGNOME:".$_POST['cognome']." NOME:".$_POST['nome']."), ".$exc->show(TRUE));
    else
        $mylog->logError("Tentativo di aggiornamento socio fallito (ID:".$_GET['id']." COGNOME:".$_POST['cognome']." NOME:".$_POST['nome']."), ".$exc->show(TRUE));
    die($exc->show());
}

/* Inizializzo un array di formattazione */
$format_input=array("cognome" => "FUC", "nome" => "FUC", "cf" => "UC", "citta" => "FUC");
/* Formatto i dati provenienti da $_POST */
$formatter= new InputFormat($format_input);
$formatter->format($_POST);

/* Prelevo i numeri di tessera ed i member_id*/
$results=$dbh->query("SELECT member_id, tessera FROM anagrafica");
$rows=$results->fetchAll();

/* Sto aggiornando i dati di un socio oppure da identità diventa socio */
if (isset($_GET['id']))
{        
    /* Richiedo il socio salvato in profile_editor: mi serve per il member_id */
    $member=$_SESSION['member'];
    
    $member->cognome=$dbh->quote($_POST['cognome']); //Quoto il cognome
    $member->nome=$_POST['nome'];
}
/* Sto inserendo un nuovo socio */
else
{
    try
    {
        $member=new Socio_Copernico($_POST['cognome'], $_POST['nome']); 
    } catch (MyException $ex) {
        die($ex->show());
    }
    
    $member->cognome=$dbh->quote($_POST['cognome']); //Quoto il cognome
}

/* Sia che aggiorno sia che inserisco un nuovo socio popolo il socio con i campi passati in POST da profile_editor dopo aver eseguito gli opportuni controlli */
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


/* Creazione data di scadenza identità (anno corrente+5 - 12 - 31) */
$date=new DateTime(); //Data attuale
$this_year=$date->format('Y');
$date_drop_identity=new DateTime();
$date_drop_identity->setDate($date->format('Y'), '12', '31');
$date_drop_identity->modify(DROP_IDENTITY); //Data scadenza identità
  
$member->scadenza_id=$date_drop_identity->format('Y-m-d');

/* Gestione data tessera e data iscrizione */
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

/* Sentinella socio cambio tessera e da identità a socio */
$update_card=FALSE;
$id_to_member=FALSE;
if($member->tessera!=NULL && $member->tessera!=$_POST['tessera']) //Un socio cambia numero di tessera
    $update_card=TRUE;
elseif($member->tessera==NULL && $_POST['tessera']!=NULL) //Un' identità vuole diventare socio
    $id_to_member=TRUE;


/* Controllo che in caso di cambio tessera o inserimento nuovo socio il numero tessera già non esista */
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




/* Sto aggiornando i dati di un socio oppure da identità a socio */
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
/* Sto inserendo un nuovo socio */
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

/* Metto nuovamente i campi senza quote */
$member->cognome=$_POST['cognome'];
$member->indirizzo=$_POST['indirizzo'];
$member->citta=$_POST['citta'];

/* Se query precedente OK */
if ($membersobj != FALSE)
{
    /* Chiedo l'ultimo member_id inserito: se nuovo socio lo utilizzo altrimento no (non andrebbe neanche bene) */
    $last_member_id=$dbh->lastInsertId();
    
    /* Sto aggiornando un socio oppure identità diventa socio */
    if (isset($_GET['id']))
    {
        /* Gestione cambio data tessera */
        if ($aaaa_iscrizione!=$this_year) //Se l'anno della prima iscrizione è diverso dall'anno corrente (era un'identità) aggiorno solo la data della tessera
            $membersobj=$dbh->query("UPDATE presenze SET data='$member->data_tessera' WHERE member_id='$_GET[id]'");
        else //Se l'anno della prima iscrizione è uguale all'anno corrente aggiorno la data della tessera e la data di iscrizione
            $membersobj=$dbh->query("UPDATE presenze SET data='$member->data_tessera', iscrizione='$member->data_tessera' WHERE member_id='$_GET[id]'");
        
        /* La data di nascita non è stata definita nel form quindi la metto NULL */
        if ($_POST['gg_nascita']=="GG" || $_POST['mm_nascita']=="MM" || empty($_POST['aaaa_nascita']))
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita=NULL WHERE member_id='$_GET[id]'");
        /* la data di nascita è definita, la metto nel formato per MySql: AAAA-MM-GG e poi la rimetto nel formato GG/MM/AAAA */
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
    /* Sto inserendo un nuovo socio (in pratica aggiorno quello appena inserito) */
    else
    {
        $member->id=$last_member_id;
        $membersobj=$dbh->query("INSERT INTO presenze (data, iscrizione, member_id) VALUES ('$member->data_tessera', '$member->data_iscrizione', '$member->id')");
        /* La data di nascita non è stata definita nel form quindi la metto NULL */
        if ($_POST['gg_nascita']=="GG" || $_POST['mm_nascita']=="MM" || empty($_POST['aaaa_nascita']))
        {
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita=NULL WHERE member_id='$$member->id'");
        }
        /* la data di nascita è definita, la metto nel formato per MySql: AAAA-MM-GG e poi la rimetto nel formato GG/MM/AAAA */
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

    /* Aggiorno il contatore di soci inseriti nella serata */
    if(!isset($_GET['id']) || $id_to_member) {
        //$_SESSION['members_evening']++;
        array_push($_SESSION['members_evening'], $member->tessera);
        $mylog->logInfo("Soci inseriti per questa sessione: ".$maxkey);
    }
}
else {
    echo '<img src="../img/check_ko.png" height="256" width="256" alt="check_ko">';
    if(isset($_GET['id']))
        $mylog->logError("Tentativo inserimento/aggiornamento socio fallito (".$_POST['cognome']." ".$_POST['nome'].")");
    else
        $mylog->logError("Tentativo inserimento nuovo socio fallito (".$_POST['cognome']." ".$_POST['nome'].")");
}

//unset($_SESSION['socio']);
?>
<form action="http://localhost/soci/php/profile_editor.php" method="get" > 
<span style="margin-left: 270px">
<input name="insert_member" value="Inserisci altro socio" type="submit" style="display: inline" >    
</span>
</form>
<form action="http://localhost/soci/index.php" method="get" style="display: inline"> 
<input name="home" value="Home" type="submit" >
</form>

<p>
<!-- <div class="select-bar_bottom">
<table>
    <tr>
        <td> <?php echo $myerror; ?> </td>
    </tr>
    <tr>
        <td> <?php echo $error; ?> </td>
    </tr>
</table>-->
</p> 
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
});
</script>
</body>
</html>