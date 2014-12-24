<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
session_start();

/* Setto la sessione di 5 ore */
ini_set('session.gc_maxlifetime',18000);
//echo ini_get('session.gc_maxlifetime'); 
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
/* Mi connetto al database */
try {
    if(!isset($dbh))
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
}
catch (PDOException $exception) {
    echo "Errore di connessione al database: ".$exception->getMessage();
    die();
}
?>
<div id="main">
<div id="header">
<a class="logo"><img src="../img/logo_copernico.jpg" width="300" height="54" alt="" /></a>
<a class="version"><?php echo VERSION; ?></a>
<ul id="top-navigation">
    <li><span><span><a href= 'http://localhost/soci/index.php'>Home</a></span></span></li>
    <li class="active"><span><span><a href="http://localhost/soci/php/profile_editor.php">Profilo</a></span></span></li>
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
<h3>&nbsp &nbsp &nbsp &nbsp &nbsp Funzionalità</h3>
<ul class="nav">
    <?php
    if(!isset($_GET['allmembers']) || $_GET['allmembers']!="true")
            echo "<li><a href='http://localhost/soci/index.php?allmembers=true'>Visualizza elenco soci completo</a></li>";
    elseif($_GET['allmembers']=="true")
            echo "<li><a href='http://localhost/soci/index.php'>Visualizza elenco ultimi"." ".MEMBERS_RECENT_MAX. " "."soci</a></li>";
    ?>
    <li><a href="http://localhost/soci/index.php?show=allidentities">Visualizza elenco identità completo</a></li>
    <li><a id="esporta" href="#">Esporta elenco soci completo</a></li>
    <li><a href="#"></a></li>
    <li><a href="#"></a></li>
    <li class="last"><a href="#"></a></li>
</ul>
<table class="counter" cellpadding="0" cellspacing="0">
    <tr>
        <td><strong>N° SOCI SERATA</strong></td>
    </tr>
    <tr>
        <td width="137" align="center"><h1><font color="#F70"><?php if(isset($_SESSION['members_evening'])) echo $_SESSION['members_evening']; else echo 0; ?></font></h1></td>
    </tr>
    <tr>
        <td><br/><br/></td>
    </tr>
    <tr>
        <td width="137" align="center"><strong>N° SOCI <?php $time=getdate(); echo $time['year'] ?></strong></td>
    </tr>
    <tr>
        <?php
        /* Conto le righe di anagrafica che hanno la tessera per l'anno corrente */
        $membersobj=$dbh->query("SELECT COUNT(*) FROM anagrafica WHERE tessera IS NOT NULL");				
        ?>
        <td width="137" align="center"><h1><font color="#F70"><?php $members= $membersobj->fetchColumn(); echo $members; ?></font></h1></td>
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
                        "luogo_nascita" => "stringchar", "cap" => "stringnum",
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
    die($exc->show());
}

/* Inizializzo un array di formattazione */
$format_input=array("cognome" => "FUC", "nome" => "FUC", "cf" => "UC", "citta" => "FUC");
/* Formatto i dati provenienti da $_POST */
$formatter= new InputFormat($format_input);
$formatter->format($_POST);

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

/* Gestione data inserimento tessera se inserisco un nuovo socio oppure da identità a socio oppure... */
if(!isset($_GET['id']) || $member->tessera==NULL)
{
    $date_ins=DateTime::createFromFormat('Y-m-d', "$this_year-$_POST[mm_inserimento]-$_POST[gg_inserimento]");
    $member->data_tessera=$date_ins->format('Y-m-d');
}
/* ...se aggiorno un socio */
 else
{
    $date_ins=$date->format('Y-m-d');
    $member->data_tessera=$date->format('Y-m-d');
}

/* Sentinella socio cambio tessera e da identità a socio */
$update_card=FALSE;
$id_to_member=FALSE;
if($member->tessera!=NULL && $member->tessera!=$_POST['tessera']) //Un socio cambia numero di tessera
    $update_card=TRUE;
elseif($member->tessera==NULL && $_POST['tessera']!=NULL) //Un' identità vuole diventare socio
    $id_to_member=TRUE;

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
                                                    , email='$member->email'
                                                    , scadenza='$member->scadenza_id' WHERE member_id='$_GET[id]' 
                            ");
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
        if(isset($_POST['tessera']) && $update_card) //Il socio cambia numero tessera
        {
            $last_member_id=$date_drop_identity->format('Y-m-d'); //RICICLO $last_member_id
            $membersobj=$dbh->query("UPDATE anagrafica SET scadenza='$last_member_id', tessera='$member->tessera' WHERE member_id='$_GET[id]'");
        }
        elseif(isset($_POST['tessera']) && $id_to_member) //Da identità diventa socio
        {
            $last_member_id=$date_drop_identity->format('Y-m-d'); //RICICLO $last_member_id
            $membersobj=$dbh->query("UPDATE anagrafica SET scadenza='$last_member_id', tessera='$member->tessera' WHERE member_id='$_GET[id]'");
            $membersobj=$dbh->query("INSERT INTO presenze (data, member_id) VALUES ('$member->data_tessera', '$_GET[id]')");
        }
        
        if (!$membersobj)
            echo '<img src="../img/check_ko.png" height="256" width="256">';
         else
            echo '<img src="../img/check_ok.png" height="256" width="256">';
    }
    /* Sto inserendo un nuovo socio (in pratica aggiorno quello appena inserito) */
    else
    {
        $member->member_id=$last_member_id;
        $membersobj=$dbh->query("INSERT INTO presenze (data, member_id) VALUES ('$member->data_tessera', '$member->member_id')");
        /* La data di nascita non è stata definita nel form quindi la metto NULL */
        if ($_POST['gg_nascita']=="GG" || $_POST['mm_nascita']=="MM" || empty($_POST['aaaa_nascita']))
        {
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita=NULL WHERE member_id='$$member->member_id'");
        }
        /* la data di nascita è definita, la metto nel formato per MySql: AAAA-MM-GG e poi la rimetto nel formato GG/MM/AAAA */
        else
        {
            $date=DateTime::createFromFormat('Y-m-d', "$_POST[aaaa_nascita]-$_POST[mm_nascita]-$_POST[gg_nascita]"); //RICICLO $date
            $member->data_nascita=$date->format('Y-m-d');
            $membersobj=$dbh->query("UPDATE anagrafica SET data_nascita='$member->data_nascita' WHERE member_id='$member->member_id'");
            $member->data_nascita=$date->format('d/m/Y'); //rimetto il formato data di nascita GG/MM/AAA in oggetto $member !!

        }
        if (!$membersobj)
            echo '<img src="../img/check_ko.png" height="256" width="256">';
        else
            echo '<img src="../img/check_ok.png" height="256" width="256">';
    }

    /* Aggiorno il contatore di soci inseriti nella serata */
    if(!isset($_GET['id']) || $id_to_member)
        $_SESSION['members_evening']++;
}
else
    echo '<img src="../img/check_ko.png" height="256" width="256">';
        

//unset($_SESSION['socio']);
?>
<h6 align="center">
<form action="http://localhost/soci/php/profile_editor.php" method="get" > 
<input name="insert_member" value="Inserisci altro socio" type="submit" style="display: inline" >
</form>
<form action="http://localhost/soci/index.php" method="get" style="display: inline"> 
<input name="home" value="Home" type="submit" >
</form>
</h6>
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

    /* Funzione di gestione esportazione elenco */
    $("a#esporta").click(function() {
        window.open('../php/root_functions.php?action=members_export','', "height=190,width=580");
    })
});
</script>
</body>
</html>