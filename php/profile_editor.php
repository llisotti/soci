<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
session_cache_limiter('private,must-revalidate');
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
<link rel="stylesheet" type="text/css" href="../css/profile_editor.css" media="all"/>
</head>
<body>
<?php
/* Mi connetto al database */
try
{
    if(!isset($dbh))
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
}
catch (PDOException $exception)
{
    echo "Errore di connessione al database: ".$exception->getMessage();
    die();
}

/* Se passo id allora cerco il socio corrispondente */
if(isset($_GET['id']))
{
    foreach($_SESSION['members'] as $member)
    {
        if($member->id==(int)$_GET['id'])
            break; //Ho trovato il socio (variabile $member) con id passato in GET
    }

    /* Salvo il socio nella sessione: mi serve in insmod.php */
    $_SESSION['member']=$member;
    
    /* Estraggo giorno, mese e anno di nascita per riempire i campi data di nascita */
    $gg=NULL;
    $mm=NULL;
    if ($member->data_nascita != NULL)
    {
        $data_nascita=$member->data_nascita;
        $gg=substr($data_nascita, '0','2');
        $mm=substr($data_nascita, '3','2');
        $aaaa=substr($data_nascita, '6','4');
    }
}
?>
<div id="main">
<div id="header">
<a class="logo"><img src="../img/logo_copernico.jpg" width="300" height="54" alt="" /></a>
<a class="version"><?php echo VERSION; ?></a>
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
<div class="top-bar">
<h1>
<?php
/* Verifico se voglio modificare un socio, un'identità o inserire un nuovo socio */
if(isset($_GET['id']) && $member->tessera!=NULL) //E' un socio
    echo "MODIFICA SOCIO";
elseif(isset($_GET['id']) && $member->tessera==NULL) //E' un'identità
    echo "MODIFICA IDENTITA'";
else //Non ancora esistente a database, inserisco un nuovo socio
    echo "INSERIMENTO NUOVO SOCIO";
?>
</h1>
<br/>
</div>
<br />
<div class="select-bar">
<input type="hidden" name="" value=""/>
<?php
/* Chiedo a MySQL il numero di tessera più alto (come controprova, deve essere uguale al numero di righe che ho contato sopra) */
$membersobj=$dbh->query("SELECT MAX(tessera) FROM anagrafica"); //riciclo $membersobj
$maxnumcard= $membersobj->fetchColumn();

$time=getdate();
$date=new DatesForSelect(); //Oggetto per riemprire select
$date_not_null=new DatesForSelect(NULL, NULL);


/* Se passo id allora sto MODIFICANDO il socio */
if(isset($_GET['id']))
{
    ?>
<form action=<?php echo "http://localhost/soci/php/insmod.php?id=".$_GET['id']; ?> method="post">
    <?php
}
/* altrimenti sto INSERENDO un nuovo socio */
else
{
    ?>
    <form action=<?php echo "http://localhost/soci/php/insmod.php"; ?> method="post">
    <?php
}


if(isset($_GET['id']) && $member->tessera!=NULL)
{    
    /* Estraggo le presenze del socio, se ve ne è più di una visualizzo la prima (quella di tesseramento) //TODO */
    $membersobj=$dbh->query("SELECT DATE_FORMAT(presenze.data,'%d/%m/%Y') data FROM presenze WHERE member_id=".$_GET['id']); //riciclo $membersobj
    $presence_dates=$membersobj->fetchColumn();
    printf("Con numero tessera");
    ?>
        <input id="card" name="tessera" type="text" size="1" value="<?php echo $member->tessera; ?>" >
    <?php
    printf(" il giorno %s è stato registrato il socio", $presence_dates);
}
/* Inserisco un nuovo socio oppure da identità diventa socio */
else
{
    $gg_inserimento=$time['mday'];
    $mm_inserimento=$time['mon'];
    $aaaa_inserimento=$time['year'];
    echo "Oggi <select id=date_ins name=gg_inserimento>";
    $date_not_null->showDays($time['mday']);
    echo "</select><select id=date_ins name=mm_inserimento>";
    $date_not_null->showMonths($time['mon']);
    echo "</select>";
    //echo "</select> <input id=card name=aaaa_inserimento placeholder=AAAA size=2 type=text maxlength=4 value=$time[year]> ";
    $maxnumcard++;
    echo " $time[year]"." con numero tessera <input id=card required name=tessera type=text size=1 value= $maxnumcard /> verrà aggiunto il socio";
    //echo"</div>";
    
}
?>
    

</div>
<div class="table">
<ol>
<p class="contact">
<li><label for="name">GENERALITA'</label></li>
</p>
<p class="contact">
<input name="cognome" placeholder="Cognome" autofocus required tabindex="1" type="text" value="<?php if(isset($_GET['id'])) echo $member->cognome; ?>">  
<input name="nome" placeholder="Nome" required tabindex="2" type="text" value="<?php if(isset($_GET['id'])) echo $member->nome; ?>"> 
</p>
<p class="contact">
data di nascita
<br/>
<select name="gg_nascita" tabindex="3" size="1" >
    <?php
    /* Popolo i giorni del mese */
    $date->showDays($gg);
    ?>
</select>
<select name="mm_nascita" tabindex="4">
    <?php
    /* Popolo i mesi dell'anno */
    $date->showMonths($mm);
    ?>
</select>
<input name="aaaa_nascita" placeholder="AAAA" tabindex="5" size="2" type="text" maxlength="4" value="<?php if(isset($_GET['id']) && $member->data_nascita != NULL) echo $aaaa; ?>" >
<input id="luogo_nascita" name="luogo_nascita" placeholder="Luogo di nascita" size="20" tabindex="6" type="text" value="<?php if(isset($_GET['id'])) echo $member->luogo_nascita; ?>">
<!-- <img src="../img/_nascita_estero.png" height="60" width="100"> -->
<input name="cf" placeholder="Codice Fiscale" tabindex="7" type="text" value="<?php if(isset($_GET['id'])) echo $member->codice_fiscale ?>" maxlength="16">&nbsp
<?php
if (isset($_GET['id']))
{
?>
    M<input name="sesso" value="M" type="radio" <?php if($member->sesso == "M") echo "checked"; ?> >
    F<input name="sesso" value="F" type="radio" <?php if($member->sesso == "F") echo "checked"; ?> >
<?php
}
else
{
?>
    M<input name="sesso" value="M" type="radio">
    F<input name="sesso" value="F" type="radio">
<?php
}
?>
</p>
<span id="message" hidden="hidden" style="padding-left:145px; color: red; font-size: smaller ">Se straniero inserire lo stato (esempio: Romania)</span>
<br/>
<p class="contact">
<li><label for="name">RESIDENZA</label></li>
</p>
<p class="contact">
<input name="indirizzo" placeholder="Indirizzo" tabindex="8" size="46" type="text" value="<?php if(isset($_GET['id'])) echo $member->indirizzo; ?>">
<input name="cap" placeholder="Cap" tabindex="9" maxlength="5" size="4" type="text" value="<?php if(isset($_GET['id'])) echo $member->cap; ?>">
<input name="citta" placeholder="Città" tabindex="10" type="text" size="22" value="<?php if(isset($_GET['id'])) echo $member->citta; ?>">
<input name="provincia" placeholder="Prov." tabindex="11" maxlength="2" size="2" type="text" value="<?php if(isset($_GET['id'])) echo $member->provincia; ?>">
<input name="stato" placeholder="Stato" tabindex="12" maxlength="20" size="5" type="text" value="<?php if(isset($_GET['id'])) echo $member->stato; ?>">
</p>
<br/>
<p class="contact">
<li><label for="phone">CONTATTI</label></li>
</p>
<p class="contact">
<input name="phone" placeholder="Telefono" tabindex="13" type="text" value="<?php if(isset($_GET['id'])) echo $member->telefono; ?>">
<input name="email" placeholder="Email" tabindex="14" type="email" value="<?php if(isset($_GET['id'])) echo $member->email; ?>" size="30">
</p>
</ol>
<h6 align="center">
<input name="submit"  value="Salva" type="submit">&nbsp 
</form>
<form action="" method="post" style="display: inline"> 
<input name="clean" value="Annulla" type="submit" >
</form>
</h6>
<div class="select-bar_bottom">
</div>
</div>
</div>
<div id="footer">
</div>
</div>
</div>
<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<script type="text/javascript">
$(document).ready(function(){

    /* Funzione gestione messaggio "luogo nascita se inserimento straniero" */
    $("#luogo_nascita").focus(function(){
        $("#message").show();
        });
    
    
    $("#luogo_nascita").focusout(function(){
        $("#message").hide();
        });
        
        
    /* Funzione di gestione esportazione elenco */
    $("a#esporta").click(function() {
        window.open('../php/root_functions.php?action=members_export','', "height=190,width=580");
    })

});
</script>
</body>
</html>