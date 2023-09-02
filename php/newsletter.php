<?php
use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// Esempio invio newsletter con PHPMailer: https://github.com/PHPMailer/PHPMailer/blob/master/examples/mailing_list.phps
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
require 'login.php';
require "PHPMailer/src/PHPMailer.php";
require "PHPMailer/src/Exception.php";
require "PHPMailer/src/SMTP.php";

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

/* Togliere commento per debug */
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

?>
<!DOCTYPE html>
<html>
<head>
<style></style>
<title>Gruppo Astrofili "N. Copernico"</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/newsletter.css" media="all"/>
</head>
<body>
<?php
$member_obj=array(); //Array per le identità a cui inviare la mail

/* Mi connetto al database */
try {
    if(!isset($dbh))
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
}
catch (PDOException $exception) {
    die("Errore di connessione al database: ".$exception->getMessage());
}
?>
<div id="main">
<div id="header">
<a class="logo"><img src="../img/logo_copernico.jpg" width="300" height="54" alt="" /></a>
<a class="version" alt="<?php echo $_SESSION['local_commit_hash']; ?>" title="<?php echo $_SESSION['local_commit_hash']; ?>"><?php echo VERSION; ?></a>
<ul id="top-navigation">
    <li><span><span><a href= 'https://<?php echo $_SERVER['HTTP_HOST'] ?>/index.php'>Home</a></span></span></li>
    <li><span><span><a href="">Profilo</a></span></span></li>
    <li class="active"><span><span><a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/php/newsletter.php">Newsletter</a></span></span></li>
    <!--
    <li><span><span><a href="">Statistiche</a></span></span></li>
    <li><span><span><a href="">Opzioni</a></span></span></li>
    <li><span><span><a href="">Statistics</a></span></span></li>
    <li><span><span><a href="">Design</a></span></span></li>
    <li><span><span><a href="">Contents</a></span></span></li>
    -->
</ul>
</div>
<div id="middle">
<div id="left-column">
<h3>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Funzionalità</h3>
<ul class="nav">
    <?php
    /* Se sto visualizzando solo gli iscritti senza tessera (default) propongo la visualizzazione dei soli soci tesserati*/
    if(!isset($_GET['show']))
        echo "<li><a href='https://{$_SERVER['HTTP_HOST']}/index.php?show=allmembers'>Visualizza elenco soci tesserati</a></li>";
    /* Altrimenti propongo la visualizzazione di default (iscritti senza tessera) */
    else
        echo "<li><a href='https://{$_SERVER['HTTP_HOST']}/index.php'>Visualizza elenco iscritti ma non tesserati</a></li>";
    ?>
    <li><a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/index.php?show=allidentities">Visualizza elenco iscritti completo</a></li>
    <li><a id="DB_functions" href="" onclick="return false;" >Operazioni su DB</a></li>
    <!-- <li><a id="esporta_soci" href="#">Esporta soci</a></li> -->
    <!-- <li><a id="esporta_identita" href="#">Esporta identità</a></li> -->
    <li><a target="_blank" rel="noopener noreferrer" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/php/eXtplorer_2.1.13/index.php">Documenti</a></li>
    <li><a target="_blank" rel="noopener noreferrer" href="../doc/Manuale.pdf">Manuale utente</a></li>
    <?php
    /*
    if($_SESSION['update']) {
        ?>
        <li class="last"><a href="./root_functions.php?action=update">Aggiornamento sw *</a></li>
        <?php
    }
    else {
        ?>
        <li class="last"><a href="">Aggiornamento sw</a></li>
        <?php
    }
    */
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
        $members=$dbh->query("SELECT COUNT(*) FROM socio WHERE socio.data_tessera=CURDATE() OR socio.data_tessera=(CURDATE() - INTERVAL 1 DAY)");
        $counter=$members->fetchColumn();
        /* Visualizzo la chiave dell'array che corrisponde al numero di soci inseriti */
        //$maxkey=max(array_keys($_SESSION['members_evening']));
        echo "<a id='view' href='' style='color:#F70'/>$counter</a>";
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
        <td id="view_drop_cards" style="width: 137px; text-align: center" colspan="2"><h1><a href="" onclick="return false"><span style="color: #F70"><?php echo $counter; if (!empty($_SESSION['breakCards'])) { echo "<sup>+".count($_SESSION['breakCards'])."</sup>";}?></span></h1></a></td>
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
/* Se non ho inviato la newsletter visualizzo il form per l'invio */
if(!isset($_POST['title'])) {
    ?>
    <!-- Tipo di codifica dei dati, DEVE essere specificato come segue -->
    <form enctype="multipart/form-data" accept-charset="ISO-8859-15" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<?php
}   
?><div id="center-column">
<div class="top-bar">
<?php

if(!isset($_POST['title'])) {
    ?> 
<a href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/php/logout.php" class="button" title="<?php echo $_SESSION['username']?> - Clicca per uscire" /></a>
<?php
/* Se non ho inviato la newsletter richiedo le identità con email */

    /* Carico i dati da anagrafica e socio */
    if(!isset($_GET['custom_list'])) {
        $members=$dbh->query("SELECT *, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(socio.scadenza,'%d/%m/%Y') scadenza,DATE_FORMAT(socio.data_tessera,'%d/%m/%Y') data_tessera FROM anagrafica INNER JOIN socio WHERE anagrafica.id=socio.id AND socio.adesioni & 2 = 2 ORDER BY anagrafica.cognome ASC, anagrafica.nome ASC");
    }
    /* Carico i dati dalla sola tabella "customList" */
    else {
        $members=$dbh->query("SELECT * FROM customList ORDER BY customList.cognome ASC, customList.nome ASC");
    }
    echo "<h1>ELENCO IDENTITA' ISCRITTE ALLA NEWSLETTER (".$members->rowCount().")</h1>";
}
else
    echo "<h1>ESITO NEWSLETTER</h1>";
?>
</div>
<br />
<div class="select-bar">
<?php
/* Se non ho inviato la newsletter visualizzo il resto del form per l'invio */
if(!isset($_POST['title'])) {
    ?>
    <!-- MAX_FILE_SIZE (in byte) deve precedere campo di input del nome file -->
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    <input type="text" name="title" placeholder="Oggetto" size="50" required/>
    <!-- <input type="file" name="userfile" disabled /> -->
    <input id="psw_input" type="password" name="psw" placeholder="Password" size="13" required />
    <img id="lock" src="../img/locked.png" width="16" height="16" alt="Visualizza password" title="Visualizza password" />
    <input style="margin-left:25px" type="checkbox" name="include_image" checked/><span style="font-size:10px">Includi immagine</span>
    <input type="submit" value="Invia_newsletter" title="Invia Newsletter"/>
    <textarea name="preImage" rows="2" cols="69" style="overflow:auto;resize:none" placeholder="Inserire qui un eventuale messaggio (ad esempio un'errata corrige) prima di un'imamgine"></textarea></br>
   
    <table style="width:91%">
    <tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th colspan="3"></th>
  </tr>
    <tr>
    <td>
    <input type="checkbox" id="domain_control" <?php if(isset($_GET['domain_control'])) echo "checked" ?>/><span style="font-size:10px">Controllo domini</span>
    </td>
    <td>
    <input type="checkbox" disabled id="custom_list" <?php if(isset($_GET['custom_list'])) echo "checked" ?>/><span style="font-size:10px">Utilizza lista custom</span>
    </td>
    <td style="text-align: right">
    <input type="button" id="reload" value="Ricarica pagina" title="Ricarica pagina"/>
    </td>
    </tr>
    </table>
    <!-- <input type="hidden" name="nonserve" value="true"/>  Barbatrucco per passare variabile in GET dopo $_SERVER['PHP_SELF'] -->
    <?php
} 
?>
</div>
<div class="table">
<?php
/* Se non ho inviato la newsletter visualizzo la tebella dei soci con l'email */
if(!isset($_POST['title'])) {
    $rows=$members->fetchAll();
    
    /* Controllo coerenza indirizzi email e dominio */
    $invalid_mail=0;
    foreach($rows as $row) {
        //$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
        if(!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            if(!$invalid_mail) {
                echo "<h3 style='background-color:orange'>ATTENZIONE: i seguenti indirizzi email potrebbero essere non validi</h3>";
                $invalid_mail=1;
            }
            echo "NOME: ".$row['cognome']." ".$row['nome']."&nbsp;&nbsp;&nbsp;EMAIL:".$row['email']."</br>";
        }
        /* Il controllo del dominio richiede tempo: lo faccio solo se passo la variabile in GET */
        if(isset($_GET['domain_control'])) {
            $domain=substr($row['email'], strpos($row['email'], "@") + 1);
            $domain=$domain."."; //checkdnsrr vuole il . dopo il nome di dominio da controllare
            if (!checkdnsrr($domain, 'MX')) {
                if(!$invalid_mail) {
                    echo "<h3 style='background-color:orange'>ATTENZIONE: i seguenti indirizzi email potrebbero essere non validi</h3>";
                    $invalid_mail=1;
                }
                echo $domain." Dominio non valido</br>";
            }
        }
    }
    echo "</br></br>";
    $odd_tr=1;
?>
<table class="listing">
    <tr>
        <th class="first" style="width: 10%">ID</th>
        <th style="width: 150px">Cognome e Nome</th>
        <th style="width: 300px">Email</th>
        <th style="width: 100px">N° Tessera</th>
        <!--<th style="width: 200px">Codice Fiscale</th>-->
        <th class="last"><input id="allchecked" type="checkbox" title="Seleziona o deseleziona tutti" checked /></th>
    </tr>
    <?php
    foreach($rows as $row)
    {
        $odd_tr++;
        if($odd_tr%2==0)
            echo "<tr>";
        else
            echo "<tr class='bg'>";

            /* Se carico i dati da anagrafica e socio allora creo l'oggetto socio popolandolo con tutti i dati */
            if(!isset($_GET['custom_list'])) {
                $member=new Socio_Copernico($row['cognome'], $row['nome']);
                $member->id=($row['id']); //Ho usato un alias nella query
                $member->data_nascita=($row['data_nascita']);
                $member->comune_nascita=($row['comune_nascita']);
                $member->provincia_nascita=($row['provincia_nascita']);
                $member->stato_nascita=($row['stato_nascita']);
                $member->sesso=($row['sesso']);
                $member->indirizzo=($row['indirizzo']);
                $member->cap=($row['cap']);
                $member->citta=($row['citta']);
                $member->provincia=($row['provincia']);
                $member->stato=($row['stato']);
                $member->telefono=($row['telefono']);
                $member->email=($row['email']);
                $member->tessera=$row['numero_tessera'];
                $member->data_tessera=$row['data_tessera'];
                $member->scadenza=($row['scadenza']);
                $member->firma=$row['firma'];
                $member->flags=$row['adesioni'];
            }
            else { //Se carico i dati dalla sola tabella customList creo l'oggetto socio popolandolo con i soli dati che questa tabella contiene 
                $member=new Socio_Copernico($row['cognome'], $row['nome']);
                $member->codice_fiscale=($row['cf']);
                $member->email=($row['email']);
            }

        /* Lo aggiungo all'array che contiene gli oggetti soci */
        array_push($member_obj, $member);
        ?>
            <td class="first style3"><?php echo $member->id ?></td>
            <td><?php echo $member->cognome." ".$member->nome ?></td>
            <td><?php echo $member->email ?></td>
            <td><?php if(!isset($_GET['custom_list'])) echo $member->tessera ?></td> <!-- Nella tabella customList non c'e' il numero tessera -->
            <!--
            <td><?//php echo $member->codice_fiscale ?></td>             
            <td class="see_profile"><a href="" onclick="return false"><img alt="Visualizza profilo completo" title="Visualizza profilo completo" src="../img/login-icon.gif" width="16" height="16" /></a></td>
            <td class="edit_profile"><a href="" onclick="return false"><img alt="Modifica profilo" title="Modifica profilo" src="../img/edit-icon.gif" width="16" height="16" /></a></td>
            <td class="add_presence"><a href="" onclick="return false"><img alt="Aggiungi presenza" title="Aggiungi presenza" src="../img/add-icon.gif" width="16" height="16" /></a></td>
            <td class="link_profile"><a href=""><img alt="Collega profilo" title="Collega profilo" src="../img/not_linked.png" width="16" height="16" /></a></td>
            -->
            <td><input class="member_checkbox" name="checklist[]" type="checkbox" value="<?php echo $member->id; ?>" checked /></td>
            <!-- <td id="cancel_profile"><a href=""><img alt="Elimina socio" title="Elimina socio" src="img/hr.gif" width="16" height="16" alt="" /></a></td>                                    
            <td><img src="img/save-icon.gif" width="16" height="16" alt="save" /> </td> -->
        </tr>
        
    <?php	
    }
    /* Creo una variabile di sessione e gli metto dentro l'array che contiene gli oggetti soci viaualizzati */
    $_SESSION['members']=$member_obj;
    ?>
</table>
<br/>
<?php	
}
/* Invio newsletter */
else {
    
    /* Richiedo il logger */
    $mylog=$_SESSION['logger'];
    
    /* Creo l'oggetto PHPMailer */
    $mail= new PHPMailer(TRUE);
    
    //$mail->SMTPDebug= 3; //Per debug
    
    /* Configuro server SMTP */
    $mail->isSMTP();
    $mail->Sender= FROM_ADDRESS;
    //$mail->Sender = "luca.lisotti@outlook.com"; //per far prove con la mia casella di posta outlook.com
    $mail->Host = "mail.tophost.it";
    //$mail->Host = "smtp.live.com"; //per far prove con la mia casella di posta outlook.com
    $mail->SMTPAuth = TRUE;
    $mail->SMTPKeepAlive = true; // Serve per non chiudere la connessione SMTP dopo ogni email inviata: riduce il sovraccarico del server SMTP
    /* 
     * Per inviare le email tramite aruba e non avere errori SSL:
     * 1) Occorre commentare la linea CipherString = DEFAULT@SECLEVEL=2 nel file /etc/ssl/openssl.conf
     * 2) Riavviare il server Apache per far rileggere la configurazione: #/etc/init.d/apache2 restart
     */
    $mail->SMTPSecure = "tls";
    $mail->addReplyTo("luca.lisotti@yahoo.com"); 
    //$mail->addReplyTo("luca.lisotti@outlook.com"); //per far prove con la mia casella di posta outlook.com
    $mail->Username = "osservatoriocopernico.org";
    //$mail->Username = "luca.lisotti@outlook.com"; //per far prove con la mia casella di posta outlook.com
    $mail->Password = $_POST['psw'];
    $mail->Port = "587";    //587 (per TLS) oppure 465 (per SSL) oppure 25 per nessuna crittografia
    
    /* Setto le codifiche e i campi della email */
    $mail->IsHTML(TRUE);
    $mail->CharSet= "ISO-8859-15";
    $mail->setFrom(FROM_ADDRESS, FROM_NAME);
    //$mail->setFrom("luca.lisotti@outlook.com", FROM_NAME); //per far prove con la mia casella di posta outlook.com
    
    $mail->Subject  = $_POST['title'];
    
    if(isset($_POST['include_image']))
        $mail->AddEmbeddedImage('../doc/evento.jpg', 'evento');
    $mail->AddEmbeddedImage('../img/logo_copernico.jpg', 'logo');
    $mail->AddEmbeddedImage('../img/web.png', 'web');
    $mail->AddEmbeddedImage('../img/facebook.jpg', 'facebook');
    $mail->AddEmbeddedImage('../img/telegram.png', 'telegram');
    $mail->AddEmbeddedImage('../img/g_map.png', 'map');
    
    /* Costruisco il corpo della mail */
    $message = $_POST['preImage'];
    if(isset($_POST['include_image']))
        $message .= file_get_contents('https://'.$_SERVER['HTTP_HOST'].'/html/newsletter_template.html');
    else {
        $message .= file_get_contents('https://'.$_SERVER['HTTP_HOST'].'/html/newsletter_template_noimage.html');;
    }
    $mail->msgHTML($message);
    
    //$added=0;       //Indirizzi aggiunti alla lista
    $not_added=0;   //Numero di indirizzi non aggiunti alla lista e quindi email non inviata
    $not_sent=0;    //Numero di Email non inviate
    $sent=0;        //Numero di Email inviate
    foreach ($_POST['checklist'] as $id_checked) {
        foreach ($_SESSION['members'] as $member) {
            if($member->id != $id_checked)
                continue;
            else {
                try {
                    $mail->addAddress($member->email, $member->nome);
                    //file_put_contents(BACKUP_PATH."newsletter.log", $member->email." aggiunto correttamente\n", FILE_APPEND);
                    //$added++;
                }catch (Exception $e) {
                    file_put_contents(BACKUP_PATH."newsletter.log", "ERRORE: ".$member->email." non aggiunto correttamente\n", FILE_APPEND);
                    $not_added++;
                    continue;
                }
                try {
                    $mail->send();
                    file_put_contents(BACKUP_PATH."newsletter.log", $member->email.": messaggio inviato\n", FILE_APPEND);
                    $sent++;
                } catch (Exception $e) {
                    file_put_contents(BACKUP_PATH."newsletter.log", "ERRORE: ".$member->email." :messaggio non inviato\n", FILE_APPEND);
                    echo 'Mailer Error (' . htmlspecialchars($member->email) . ') ' . $mail->ErrorInfo . '<br>';
                    //Reset the connection to abort sending this message
                    //The loop will continue trying to send to the rest of the list
                    //$mail->smtp->reset(); <- provoca eccezione in quanto smtp e' protected e non posso accedere
                    $not_sent++;
                    $mysmtp=$mail->getSMTPInstance();
                    $mysmtp->reset();
                }
            }
        }
        //Clear all addresses and attachments for the next iteration
        $mail->clearAddresses();
        //$mail->clearAttachments();
    }
    
    file_put_contents(BACKUP_PATH."newsletter.log", "\n\nINVIATI ".$sent."\nNON INVIATI ".$not_sent."\nNON AGGIUNTI ".$not_added, FILE_APPEND);
    
    if($not_added || $not_sent)
        echo '<img class="message_sent" src="../img/check_ko.png" height="256" width="256" alt="check_ko.png">';
    else
        echo '<img class="message_sent" src="../img/check_ok.png" height="256" width="256" alt="check_ok.png">';
    
    echo "</br></br>Controllare il file newsletter.log nella cartella doc";
    

}
?>

<div class="select-bar_bottom">
</div>
</div>
</div>
<?php
if(!isset($_POST['title'])) {
    ?>
    </form>
    <?php
}
?>
<div id="footer">
</div>
</div>
</div>
<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<!-- <script type="text/javascript" src="js/jquery-ui-1.11.2/jquery-ui.js"> </script> -->
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
    
        
    /* Funzione di gestione 'Visualizza profilo' */
    $("td.see_profile").click(function() {
        //recupero il testo dentro il td precedente (che per come ho strutturato la tabella è il member_id)
        var member_id = $(this).siblings(":first").text();
        //lo invio in GET alla nuova finestra contenente la pagina "profile_viewer.php"
        window.open('profile_viewer.php?id='+member_id,'', "height=190,width=580");
    });


    /* Funzione di creazione backup */
    $("a#DB_functions").click(function() {
        window.open('../php/root_functions.php?action=DB_functions','', "height=450,width=900");
    });
    

    /* Funzione di gestione 'Modifica profilo' */
    $("td.edit_profile").click(function() {
        //recupero il testo dentro due td precedenti (che per come ho strutturato la tabella è il numero di tessera)
        var member_id = $(this).siblings(":first").text();
        //lo invio in GET alla nuova finestra contenente la pagina "profile_editor.php" inviando il numero tessera quindi MODIFICO il socio
        window.location.href='profile_editor.php?id='+member_id;
    });


    /* Funzione di gestione testo in grassetto al passaggio con il mouse sulla tabella elenco soci */
    $('td').mouseover(function(){
        if($(this).closest('table').hasClass('listing')) {
        $(this).css({'font-weight':'bold'}); //effetto anche su elemento dove si trova il mouse
        $(this).siblings().css({'font-weight':'bold'})
    }
    }).mouseout(function(){
        $(this).css({'font-weight':''}); //effetto anche su elemento dove si trova il mouse
        $(this).siblings().css({'font-weight':''});
    });
    
    
    /* Funzione per gestire il check o meno di tutte le checkbox nella tabella */
    $("#allchecked").click(function () {
        if ($("#allchecked").is(':checked')) {
            $(".member_checkbox").prop("checked", true);
        } else {
            $(".member_checkbox").prop("checked", false);
        }
    });
    
    
    /* Funzione per gestire la visualizzazione o meno della password */
    $("#lock").on('click', function () {
        if($("#psw_input").attr('type')=='password') {
            $("#psw_input").attr('type', 'text');
            $(this).attr('src', '../img/unlocked.png');
            $(this).attr('title', 'Nascondi password');
        }
        else {
            $("#psw_input").attr('type', 'password');
            $(this).attr('src', '../img/locked.png');
            $(this).attr('title', 'Visualizza password');
        }
    });
    
    
    /* Funzione visualizzazione tessere inserite nella sessione */
    $("a#view").click(function() {
        window.open('../php/root_functions.php?action=view_members_evening','', "height=190,width=580,scrollbars=1");
    });
            
    
    /* Funzione visualizzazione numeri di tessera mancanti */
    $("td#view_drop_cards").click(function() {
        window.open('../php/root_functions.php?action=view_drop_cards','', "height=190,width=580,scrollbars=1");
    });
    

    /* Funzione per il ricaricamento della pagina con le variabili in $_GET */
    $('#reload').click(function() {
    	var uri = window.location.toString();
        if (uri.indexOf("?") > 0) {
            var clean_uri = uri.substring(0, uri.indexOf("?"));
            window.history.replaceState({}, document.title, clean_uri);
        }
		if($('#domain_control').is(':checked') && $('#custom_list').is(':checked'))
    		window.location.href=window.location.href+'?domain_control&custom_list';  
		else if($('#custom_list').is(':checked'))
    		window.location.href=window.location.href+'?custom_list';
    	else if($('#domain_control').is(':checked'))
    		window.location.href=window.location.href+'?domain_control';
    	else
    		window.location.href=window.location.href;
		
    });
   
});
</script>
</body>
</html>
