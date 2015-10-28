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
/* RIchiedo il logger */
$mylog=$_SESSION['logger'];

/* Mi connetto al database */
try
{
    if(!isset($dbh))
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
}
catch (PDOException $exception) {
    $mylog->logError("Errore di connessione al database: ".$exception->getMessage());
    die("Errore di connessione al database: ".$exception->getMessage());
}

$gg_nascita=NULL; $gg_inserimento=NULL;
$mm_nascita=NULL; $mm_inserimento=NULL;
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
    if ($member->data_nascita != NULL)
    {
        $data_nascita=$member->data_nascita;
        $gg_nascita=substr($data_nascita, '0','2');
        $mm_nascita=substr($data_nascita, '3','2');
        $aaaa_nascita=substr($data_nascita, '6','4');
    }
    
    /* Se è socio, estraggo giorno e mese in cui è stato inserito */
    if($member->tessera!=NULL) {
    $data_inserimento=$member->data_tessera;
    $gg_inserimento=substr($data_inserimento, '0','2');
    $mm_inserimento=substr($data_inserimento, '3','2');
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
<h3>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Funzionalità</h3>
<ul class="nav">
    <?php
    if(!isset($_GET['allmembers']) || $_GET['allmembers']!="true")
            echo "<li><a href='http://localhost/soci/index.php?allmembers=true'>Visualizza elenco soci completo</a></li>";
    elseif($_GET['allmembers']=="true")
            echo "<li><a href='http://localhost/soci/index.php'>Visualizza elenco ultimi"." ".MEMBERS_RECENT_MAX. " "."soci</a></li>";
    ?>
    <li><a href="http://localhost/soci/index.php?show=allidentities">Visualizza elenco identità completo</a></li>
    <li><a id="esporta_soci" href="#">Esporta soci</a></li>
    <li><a id="esporta_identita" href="#">Esporta identità</a></li>
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
    <?php
/* Se passo id allora sto MODIFICANDO il socio */
if(isset($_GET['id']))
{
    ?>
<form action=<?php echo "'http://localhost/soci/php/insmod.php?id="."$_GET[id]'"; ?> method="post"><input type="hidden" name="" value=""/>
    <?php
}
/* altrimenti sto INSERENDO un nuovo socio */
else
{
    ?>
    <form action=<?php echo "'http://localhost/soci/php/insmod.php'"; ?> method="post"><input type="hidden" name="" value=""/>
    <?php
}?>
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
<br/>
<div class="select-bar">
<?php
/* Chiedo a MySQL il numero di tessera più alto (come controprova, deve essere uguale al numero di righe che ho contato sopra) */
$membersobj=$dbh->query("SELECT MAX(tessera) FROM anagrafica"); //riciclo $membersobj
$maxnumcard= $membersobj->fetchColumn();

$time=getdate();
$date=new DatesForSelect(); //Oggetto per riemprire select
$date_not_null=new DatesForSelect(NULL, NULL);





if(isset($_GET['id']) && $member->tessera!=NULL)
{    
    /* Estraggo le presenze del socio, se ve ne è più di una visualizzo la prima (quella di tesseramento) //TODO */
    //$membersobj=$dbh->query("SELECT DATE_FORMAT(presenze.data,'%d/%m/%Y') data FROM presenze WHERE member_id=".$_GET['id']); //riciclo $membersobj
    //$presence_dates=$membersobj->fetchColumn();
    printf("Con numero tessera");
    ?>
    <input id="card" name="tessera" type="text" size="1" value="<?php echo $member->tessera; ?>" />
    <?php
    echo "in data <select class=date_ins style='width: 6%' name=gg_inserimento>";
    $date_not_null->showDays($gg_inserimento);
    echo "</select><select class=date_ins style='width: 6%' name=mm_inserimento>";
    $date_not_null->showMonths($mm_inserimento);
    echo "</select> è stato registrato il socio";
    //printf(" il giorno %s e msese %s è stato registrato il socio", $gg_inserimento, $mm_inserimento);
}
/* Inserisco un nuovo socio oppure da identità diventa socio */
else
{
    $gg_inserimento=$time['mday'];
    $mm_inserimento=$time['mon'];
    $aaaa_inserimento=$time['year'];
    echo "Oggi <select class=date_ins style='width: 6%' name=gg_inserimento>";
    $date_not_null->showDays($time['mday']);
    echo "</select><select class=date_ins style='width: 6%' name=mm_inserimento>";
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
<ol class="contact" style="padding-left: 15px">
    <li><label>GENERALITA'</label>&nbsp;&nbsp;<img style="margin-right: 400px; float: right;" id="searching" hidden="hidden" src="../img/ajax-loader.gif" ></li>
</ol>
<input id="scr" name="cognome" placeholder="Cognome" autofocus required tabindex="1" type="text" value="<?php if(isset($_GET['id'])) echo $member->cognome; ?>"/>  
<input name="nome" placeholder="Nome" required tabindex="2" type="text" value="<?php if(isset($_GET['id'])) echo $member->nome; ?>"/>

<br><br>
data di nascita
<br>
<select name="gg_nascita" tabindex="3" size="1" >
    <?php
    /* Popolo i giorni del mese */
    $date->showDays($gg_nascita);
    ?>
</select>
<select name="mm_nascita" tabindex="4">
    <?php
    /* Popolo i mesi dell'anno */
    $date->showMonths($mm_nascita);
    ?>
</select>
<input name="aaaa_nascita" placeholder="AAAA" tabindex="5" size="2" type="text" maxlength="4" value="<?php if(isset($_GET['id']) && $member->data_nascita != NULL) echo $aaaa_nascita; ?>" />
<input id="luogo_nascita" name="luogo_nascita" placeholder="Luogo di nascita" size="20" tabindex="6" type="text" value="<?php if(isset($_GET['id'])) echo $member->luogo_nascita; ?>" />
<!-- <img src="../img/_nascita_estero.png" height="60" width="100"> -->
<input name="cf" placeholder="Codice Fiscale" tabindex="7" type="text" value="<?php if(isset($_GET['id'])) echo $member->codice_fiscale ?>" maxlength="16" />&nbsp;
<?php
if (isset($_GET['id']))
{
?>
    M<input name="sesso" value="M" type="radio" <?php if($member->sesso == "M") echo "checked"; ?> />
    F<input name="sesso" value="F" type="radio" <?php if($member->sesso == "F") echo "checked"; ?> />
<?php
}
else
{
?>
    M<input name="sesso" value="M" type="radio" />
    F<input name="sesso" value="F" type="radio" />
<?php
}
?>
<br>
<span id="message" hidden="hidden" style="padding-left:140px; color: red; font-size: smaller ">Se straniero inserire lo stato (esempio: Romania)</span>
<br><br><br>
<ol class="contact" start="2" style="padding-left: 15px">
<li><label>RESIDENZA</label></li>
</ol>
<input name="indirizzo" placeholder="Indirizzo" tabindex="8" size="46" type="text" value="<?php if(isset($_GET['id'])) echo $member->indirizzo; ?>"/>
<input name="cap" placeholder="Cap" tabindex="9" maxlength="7" size="6" type="text" value="<?php if(isset($_GET['id'])) echo $member->cap; ?>" />
<input name="citta" placeholder="Città" tabindex="10" type="text" size="22" value="<?php if(isset($_GET['id'])) echo $member->citta; ?>" />
<input name="provincia" placeholder="Prov." tabindex="11" maxlength="2" size="2" type="text" value="<?php if(isset($_GET['id'])) echo $member->provincia; ?>" />
<input name="stato" placeholder="Stato" tabindex="12" maxlength="20" size="5" type="text" value="<?php if(isset($_GET['id'])) echo $member->stato; ?>" />
<br><br><br><br>
<ol class="contact" start="3" style="padding-left: 15px">
<li><label>CONTATTI</label></li>
</ol>
<input name="phone" placeholder="Telefono" tabindex="13" type="text" value="<?php if(isset($_GET['id'])) echo $member->telefono; ?>" />
<input name="email" placeholder="Email" tabindex="14" type="email" value="<?php if(isset($_GET['id'])) echo $member->email; ?>" size="30" />
<br><br><br>
<span style="padding-left: 300px; padding-bottom: 0px; padding-top: 20px"></span>
<input name="submit"  value="Salva" type="submit" /><input name="clean" value="Annulla" type="submit" formnovalidate />
<div class="select-bar_bottom">
</div>
</div>
</div>        
</form>
<div id="footer">
</div>
</div>
</div>
<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<script type="text/javascript">
$(document).ready(function(){
    
    /* Funzione che controlla se esiste la variabile $_GET[var_name] ed eventualmente ritorna il suo valore */
    function get_var(var_name){
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == var_name){return pair[1];}
       }
       return(false);
}
    /* Funzione di ricerca quando esco dal campo */
    $(function(){
        var exist_id = get_var("id");
        $("#scr").blur(function()
        {
            if($("#scr").val()=='' || exist_id) //Esco subito se campo vuoto oppure se sto aggiornando un socio o un'identità
                return false;
            $("#searching").show(); //Mostro la gif animata di ricerca
            var dataString = $(this).val();
        {
            $.ajax({
            type: "POST",
            url: "search.php",
            data: {searched: dataString},
            dataType: 'html',
            //cache: true,
            success: function(data)
            {
                if($("#scr").val()=='')//(code==8 && $("#scr").val()=='') //code=8: tasto premuto è backspace
                    $(".select-bar_bottom").html("");
                else
                    $(".select-bar_bottom").html(data);               
            },
            complete: function()
            {
                $("#searching").hide(); //Quando la richiesta ajax è completata nascondo la gif animata
            }
            });
        }return false;
        });
    });
    

    /* Funzione gestione messaggio "luogo nascita se inserimento straniero" */
    $("#luogo_nascita").focus(function(){
        $("#message").show();
        });
        
    $("#luogo_nascita").focusout(function(){
        $("#message").hide();
        });
        
        
    /* Funzione di creazione backup */
    $("a#DB_functions").click(function() {
        window.open('../php/root_functions.php?action=DB_functions','', "height=190,width=580");
    });
    
    
    /* Funzione di gestione esportazione elenco soci */
    $("a#esporta_soci").click(function() {
        window.open('../php/root_functions.php?action=members_export','', "height=190,width=580");
    })
    
        
    /* Funzione di gestione esportazione elenco identità*/
    $("a#esporta_identita").click(function() {
        window.open('../php/root_functions.php?action=identities_export','', "height=190,width=580");
    })


    /* Funzione visualizzazione tessere inserite nella sessione */
    $("a#view").click(function() {
        window.open('../php/root_functions.php?action=view_members_evening','', "height=190,width=580,scrollbars=1");
    });
});
</script>
</body>
</html>