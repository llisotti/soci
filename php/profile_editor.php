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
<link rel="stylesheet" type="text/css" href="../css/profile_editor.css" media="all"/>
</head>
<body>
<?php
/* Richiedo il logger */
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

/* Cerco il socio corrispondente */
foreach($_SESSION['members'] as $member) {
    if($member->codice_fiscale==$_GET['cf'])
        break; //Ho trovato il socio (variabile $member) con cf passato in GET
}

/* Salvo il socio nella sessione: mi serve in insmod.php */
$_SESSION['member']=$member;

/* Estraggo giorno, mese e anno di nascita per riempire i campi data di nascita */
if ($member->data_nascita != NULL) {
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
	<li><a id="DB_functions" href="" onclick="return false;">Operazioni su DB</a></li>
    <!-- <li><a id="esporta_soci" href="#">Esporta soci</a></li> -->
    <!-- <li><a id="esporta_identita" href="#">Esporta identità</a></li> -->
    <li><a target="_blank" rel="noopener noreferrer" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/php/eXtplorer_2.1.13/index.php">Documenti</a></li>
    
    <?php
    if($_SESSION['update']) {
        ?>
        <li class="last"><a href="./root_functions.php?action=update">Aggiornamento sw *</a></li>
        <?php
    }
    else {
        ?>
        <li class="last"><a href="" onclick="return false;">Aggiornamento sw</a></li>
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
    
    if(isset($_SESSION['breakCards'])) {
        unset($_SESSION['breakCards']);
    }
    
    /* Calcolo eventuali buchi di tessere */
    $breakCards=array();
    $cards=$dbh->query("SELECT numero_tessera FROM socio WHERE numero_tessera IS NOT NULL ORDER BY socio.numero_tessera DESC");
    $numCards=$cards->fetchAll(PDO::FETCH_COLUMN, 0);
    
    for ($index=0; $index < $counter; $index++) {
        $internal_index=$index;
        $difference=1;
        if($index!=$counter-1) {
            while($numCards[$internal_index]-$difference!=($numCards[$internal_index+1])) {
            array_push($breakCards, $numCards[$internal_index]-$difference);
            $difference++;
            }
        }
        else { //Qui controllo se la tessera piu' bassa vale 1. Se non lo e' cerco la piu' bassa. Le mancanti da quest'ultima alla 1 le aggiungo all'array dei mancanti
            $difference=1;
            while($numCards[$index]!=$difference) {
                array_push($breakCards, $difference);
                $difference++;
            }
        }
    }
    rsort($breakCards);
    $_SESSION['breakCards']=$breakCards;
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
<form action=<?php echo "'http://{$_SERVER['HTTP_HOST']}/soci/php/insmod.php?cf="."$_GET[cf]'"; ?> method="post"><input type="hidden" name="" value=""/>
<div id="center-column">
<div class="top-bar"> <a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/soci/php/profile_editor.php" class="button" title="Aggiungi nuovo socio"></a>
<h1>MODIFICA PROFILO</h1>
<br/>
</div>
<br/>
<div class="select-bar">
<?php
/* Chiedo a MySQL il numero di tessera più alto (come controprova, deve essere uguale al numero di righe che ho contato sopra) */
//$membersobj=$dbh->query("SELECT MAX(numero_tessera) FROM socio"); //riciclo $membersobj
//$maxnumcard= $membersobj->fetchColumn();

$time=getdate();

/* Oggetto per riempire i select */
$date=new DataForSelect();
$date_not_null=new DataForSelect(NULL, NULL);

/* Intestazione se socio o identita' */
if($member->tessera!=NULL) { //Modifico un socio
    printf("Con numero tessera");
    ?>
    <input id="card" name="tessera" type="text" size="3" value="<?php echo $member->tessera; ?>" />
    <?php
    echo "in data <select class=date_ins style='width: 10%' name=gg_inserimento>";
    $date_not_null->showDays($gg_inserimento);
    echo "</select><select class=date_ins style='width: 10%' name=mm_inserimento>";
    $date_not_null->showMonths($mm_inserimento);
    echo "</select><span id=socio_ident> è stato registrato il socio</span>";
}
else { //Modifico un'identita' o da identita' diventa socio
    $gg_inserimento=$time['mday'];
    $mm_inserimento=$time['mon'];

    //$aaaa_inserimento=$time['year'];

    echo "Oggi <select class=date_ins style='width: 10%' name=gg_inserimento>";
    $date_not_null->showDays($time['mday']);
    echo "</select><select class=date_ins style='width: 10%' name=mm_inserimento>";
    $date_not_null->showMonths($time['mon']);
    echo "</select>";
    //$maxnumcard++;
    echo " $time[year]"." con numero tessera <input id=card name=tessera type=text size=3 autofocus/><span id=socio_ident> verrà aggiunto il socio</span>";
}
?>
<input id="aggiuntoSocio" type="hidden" name="aggiuntoSocio" value="nonfarenulla"> <!-- Serve solo per inviare in POST se aggiungere un socio o meno alla variabile si sessione $_SESSION['members_evening'] -->
</div>
<div class="table">
<ol class="contact" style="padding-left: 15px">
    <li><label>GENERALITA'</label>&nbsp;&nbsp;<img style="margin-right: 400px; float: right;" id="searching" hidden="hidden" src="../img/ajax-loader.gif" ></li>
</ol>
<table>
<tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
</tr>
<tr>
<td>Cognome: </td>
<td><input id="scr" name="cognome" type="text" value="<?php echo $member->cognome; ?>"/></td>
<td>Nome: </td>  
<td colspan="3"><input name="nome" type="text" value="<?php echo $member->nome; ?>"/></td>
</tr>
<tr>
<td>Nato/a il: </td>
<td><select name="gg_nascita" size="1">
    <?php
    /* Popolo i giorni del mese */
    $date->showDays($gg_nascita);
    ?>
</select>
<select name="mm_nascita">
    <?php
    /* Popolo i mesi dell'anno */
    $date->showMonths($mm_nascita);
    ?>
</select>&nbsp;&nbsp;
<input name="aaaa_nascita" size="2" type="text" value="<?php echo $aaaa_nascita; ?>" /></td>
<td style="text-align:right">a: </td>
<td><input id="comune_nascita" name="comune_nascita" size="20" type="text" value="<?php if($member->stato_nascita=="IT") echo $member->comune_nascita ?>" /></td> 
<td style="text-align:right">Provincia: </td>
<td><input id="provincia_nascita" name="provincia_nascita" size="1" type="text" value="<?php if($member->stato_nascita=="IT") echo $member->provincia_nascita ?>"/></td>
<td style="text-align:right">Stato: </td>
<td><input id="stato_nascita" name="stato_nascita" size="1" type="text" value="<?php echo $member->stato_nascita ?>"/></td>
<!-- <img src="../img/_nascita_estero.png" height="60" width="100"> -->
</tr>
<tr>
<td>Codice fiscale: </td>
<td><input name="cf" type="text" value="<?php echo $member->codice_fiscale ?>" /></td>
</table><?php /*
if (isset($_GET['cf']))
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
} */
?>
<span id="message" hidden="hidden" style="padding-left:140px; color: red; font-size: smaller ">Se straniero inserire lo stato (esempio: Romania)</span>
<br><br><br>
<ol class="contact" start="2" style="padding-left: 15px">
<li><label>RESIDENZA</label></li>
</ol>
<table>
<tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
</tr>
<tr>
<td>Indirizzo: </td>
<td><input name="indirizzo" size="46" type="text" value="<?php echo $member->indirizzo; ?>"/></td>
<td style="text-align:right">Cap: </td>
<td><input name="cap" size="9" type="text" value="<?php echo $member->cap; ?>" /></td>
</tr>
<tr>
<td style="text-align:right">Citta': </td>
<td><input name="citta" type="text" size="46" value="<?php echo $member->citta; ?>" /></td>
<td style="text-align:right">Provincia: </td>
<td><input name="provincia" size="1" type="text" value="<?php echo $member->provincia; ?>" /></td>
<td style="text-align:right">Stato: </td>
<td><input name="stato" size="1" type="text" value="<?php echo $member->stato; ?>" /><td>
</tr>
</table><br><br><br>
<ol class="contact" start="3" style="padding-left: 15px">
<li><label>CONTATTI</label></li>
</ol>
<table>
<tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
</tr>
<tr>
<td>Telefono: </td>
<td><input name="telefono" type="text" value="<?php echo $member->telefono; ?>" /></td>
<td>Email: </td>
<td><input name="email" tabindex="14" type="email" value="<?php echo $member->email; ?>" size="30" /></td>
</tr>
<tr>
<?php $adesioni=pack('C', $member->flags) ?>
<td colspan="6"><input id="privacy" type="checkbox" name="diffusione_nominativo" value="acconsento" <?php echo ($adesioni & 1 ? 'checked' : '') ?>><span style="font-size:13px; font-family: Arial Narrow">Acconsento alla diffusione del mio nome e cognome, della mia immagine o di video che mi riprendono nel sito istituzionale, nei social network (es. Facebook, Instagram, Youtube) e sul materiale informativo cartaceo dell'Associazione per soli fini di descrizione e promozione dell'attivita' istituzionale, nel rispetto delle disposizione del GDPR e del D. Lgs. n. 196/03 e delle autorizzazioni/indicazioni della commissione UE e del Garante per la Protezione dei Dati Personali.</span></td>
</tr>
<tr>
<td colspan="6"><input id="news" type="checkbox" name="newsletter" value="iscritto" <?php echo ($adesioni & 2 ? 'checked' : '')?>><span style="font-size:13px; font-family: Arial Narrow">Desidero iscrivermi alla newsletter per rimanere informato su novita' ed eventi.</span></td>
</tr>
<!-- <div style="background-color:#BBD9EE;"> -->
</table><br><br>
<div>
<ol class="contact" start="4" style="padding-left: 15px">
<li><label>
<?php
$current_year=date("Y");
if((($current_year-$aaaa_nascita) >=18) && ($adesioni & 4)) //Se e' maggiorenne ma risulta iscritto come minorenne chiedo che venga rifatta la firma o l'intera iscrizione
    echo "FIRMA DEL GENITORE: <br><span style='color:red'> ATTENZIONE: sembra che al momento dell' iscrizione la persona fosse minorenne mentre ora ha raggiunto la maggiore eta'. E' necessario aggiornare la firma o eseguire una nuova iscrizione</span>";
elseif ((($current_year-$aaaa_nascita) < 18) && ($adesioni & 4)) //Se e' minorenne e risulta iscritto come minorenne allora ok: firma del genitore
    echo "FIRMA DEL GENITORE:";
else //Se e' maggiorenne firma propria
    echo "FIRMA:";
?>
</label></li>
</ol>
<?php
$string=base64_encode(file_get_contents(str_replace(" ", "", SIGNATURE_IMAGE_PATH.$member->firma)))
?>
<img src="data:image/svg+xml;base64,<?php echo $string ?>" />
</div>
<br><br><br>
<span style="padding-left: 300px; padding-bottom: 0px; padding-top: 20px"></span>
<input name="submit"  value="Salva" type="submit" /><input id="pdf" value="Stampa PDF" type="button"/>
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

	/* Variabile che mi serve a definire appena si apre la pagina se la persona e' socio o identita' (identita' di default) */
	var erasocio = false;

	/* Se apro la pagina ed il valore della tessera contiene il numero tessera allora e' socio */
	if( $("#card").val() )
		erasocio=true;

	/* Se apro la pagina ed il valore della tessera e' vuoto disabilito la stampa del PDF */
	if( !$("#card").val() )
		$('#pdf').prop("disabled",true);
	
	/* Funzione che mi cambia l'intestazione se aggiungo socio (campo tessera con numero) o aggiorno un'identita' */
	$("#card").blur(function()
    {
		if((!$(this).val()) && erasocio) { //Se non ho il numero tessera ed era socio => da socio a identita'
	    	$("#socio_ident").text(" sara' cancellata la tessera al socio");
	    	$('#pdf').prop("disabled",true);
	    	$('#aggiuntoSocio').val("cancella");
		}
		else if (($(this).val()) && erasocio) { //Se ho il numero tessera ed era socio => modifico il profilo del socio
			$("#socio_ident").text(" verrà modificato il profilo del socio");
			$('#pdf').prop("disabled",false);
		}
		else if ((!$(this).val()) && !erasocio) { //Se non ho il numero tessera ed non era socio => modifico il profilo dell'identita' 
			$("#socio_ident").text(" verrà modificato il profilo di");
			$('#pdf').prop("disabled",true);
		}
		else { //Se ho il numero tessera e non era socio => da identita' a socio
			$("#socio_ident").text(" sara' aggiunto il socio");
			$('#pdf').prop("disabled",false);
			$('#aggiuntoSocio').val("aggiungi");
		}
    });
    
    /* Funzione che controlla se esiste la variabile $_GET[var_name] ed eventualmente ritorna il suo valore
    function get_var(var_name){
       var query = window.location.search.substring(1);
       var vars = query.split("&");
       for (var i=0;i<vars.length;i++) {
               var pair = vars[i].split("=");
               if(pair[0] == var_name){return pair[1];}
       }
       return(false);
	}
	*/
    /* Funzione di ricerca quando esco dal campo
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
    */    

    /* Funzione gestione messaggio "luogo nascita se inserimento straniero" */
    $("#luogo_nascita").focus(function(){
        $("#message").show();
        });
        
    $("#luogo_nascita").focusout(function(){
        $("#message").hide();
        });
        
        
    /* Funzione di creazione backup */
    $("a#DB_functions").click(function() {
        window.open('../php/root_functions.php?action=DB_functions','', "height=450,width=900");
    });
    
    
    /* Funzione di gestione esportazione elenco soci */
    $("a#esporta_soci").click(function() {
        window.open('../php/root_functions.php?action=members_export','', "height=300,width=700");
    })
    
        
    /* Funzione di gestione esportazione elenco identità*/
    $("a#esporta_identita").click(function() {
        window.open('../php/root_functions.php?action=identities_export','', "height=300,width=700");
    })


    /* Funzione visualizzazione tessere inserite nella sessione */
    $("a#view").click(function() {
        window.open('../php/root_functions.php?action=view_members_evening','', "height=190,width=580,scrollbars=1");
    });
        
    
    /* Funzione visualizzazione numeri di tessera mancanti */
    $("td#view_drop_cards").click(function() {
        window.open('../php/root_functions.php?action=view_drop_cards','', "height=190,width=580,scrollbars=1");
    });

    
    /* Funzione per la generazione del PDF */
    $("#pdf").click(function() {
        var tessera = $("#card").val();
        window.open('../php/pdfgen.php?tessera='+tessera,'', "height=600,width=900,scrollbars=1");
    })
    
});
</script>
</body>
</html>