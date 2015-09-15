<?php
require "member.php"; //OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE  !
require "class.phpmailer.php";
require "class.smtp.php";
session_start();
/* Setto la sessione di 5 ore */
ini_set('session.gc_maxlifetime',18000);
//echo ini_get('session.gc_maxlifetime'); 
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
<a class="version"><?php echo VERSION; ?></a>
<ul id="top-navigation">
    <li><span><span><a href= 'http://localhost/soci/index.php'>Home</a></span></span></li>
    <li><span><span><a href="http://localhost/soci/php/profile_editor.php">Profilo</a></span></span></li>
    <li class="active"><span><span><a href="http://localhost/soci/php/newsletter.php">Newsletter</a></span></span></li>
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
    /* Se sto visualizzando i soci più recenti, nel menù delle funzionalità propongo la visualizzazione di tutti i soci */
    if(!isset($_GET['show']) || empty($_GET) || $_GET['show']=="recentmembers")
        echo "<li><a href='http://localhost/soci/index.php?show=allmembers'>Visualizza elenco soci completo</a></li>";
    /* Altrimenti propongo la visualizzazione dei soci più recenti */
    else
        echo "<li><a href='http://localhost/soci/index.php'>Visualizza elenco ultimi"." ".MEMBERS_RECENT_MAX. " "."soci</a></li>";
    ?>
    <li><a href="http://localhost/soci/index.php?show=allidentities">Visualizza elenco identità completo</a></li>
    <li><a id="esporta_soci" href="#">Esporta soci</a></li>
    <li><a id="esporta_identita" href="#">Esporta identità</a></li>
    <li><a id="DB_functions" href="#">Operazioni sul DB</a></li>
    <li class="last"><a href="#"></a></li>
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
<input type="submit" value="Invia_newsletter" title="Invia Newsletter"/>
<?php
/* Se non ho inviato la newsletter richiedo le identità con email */

    /* Visualizzo le identità provviste di email */
    $members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data FROM anagrafica INNER JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.email!='' AND anagrafica.email IS NOT NULL ORDER BY anagrafica.tessera");
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
    <input type="file" name="userfile" disabled />
    <input id="psw_input" type="password" name="psw" placeholder="Password" size="18" required />
    <img id="lock" src="../img/locked.png" width="16" height="16" alt="Visualizza password" title="Visualizza password" />
    <textarea name="body_message" rows="2" cols="107" style="overflow:auto;resize:none" placeholder="Inserire qui un eventuale messaggio (ad esempio un'errata corrige)"></textarea>
    <!-- <input type="hidden" name="nonserve" value="true"/>  Barbatrucco per passare variabile in GET dopo $_SERVER['PHP_SELF'] -->
    <?php
} 
?>
</div>
<div class="table">
<?php
if(!isset($_POST['title'])) {
    ?>
<img src="../img/bg-th-left.gif" width="8" height="7" alt="" class="left" />
<img src="../img/bg-th-right.gif" width="7" height="7" alt="" class="right" />

<?php
/* Se non ho inviato la newsletter visualizzo la tebella dei soci con l'email */

$rows=$members->fetchAll();
$odd_tr=1;
?>
<table class="listing">
    <tr>
        <th class="first" style="width: 30px">ID</th>
        <th style="width: 167px">Cognome e Nome</th>
        <th style="width: 220px">Email</th>
        <th>N° Tessera</th>
        <th colspan="4">Azioni</th>
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

        /* Creo l'oggetto socio e lo popolo con tutti i dati */
        $member=new Socio_Copernico($row['cognome'], $row['nome']);
        $member->id=($row['primary_id']); //Ho usato un alias nella query
        //$member->data_nascita=($row['data_nascita']);
        //$member->luogo_nascita=($row['luogo_nascita']);
        //$member->sesso=($row['sesso']);
        //$member->codice_fiscale=($row['cf']);
        //$member->indirizzo=($row['indirizzo']);
        //$member->cap=($row['cap']);
        //$member->citta=($row['citta']);
        //$member->provincia=($row['provincia']);
        //$member->stato=($row['stato']);
        //$member->telefono=($row['telefono']);
        $member->email=($row['email']);
        $member->tessera=$row['tessera'];
        //$member->scadenza_id=($row['scadenza']);
        //$member->data_tessera=($row['data']); //Data del tesseramento

        /* Lo aggiungo all'array che contiene gli oggetti soci */
        array_push($member_obj, $member);
        ?>
            <td class="first style3"><?php echo $member->id ?></td>
            <td><?php echo $member->cognome." ".$member->nome ?></td>
            <td><?php echo $member->email ?></td>
            <td><?php echo $member->tessera ?></td>
            <td class="see_profile"><a href="#" onclick="return false"><img alt="Visualizza profilo completo" title="Visualizza profilo completo" src="../img/login-icon.gif" width="16" height="16" /></a></td>
            <td class="edit_profile"><a href="#" onclick="return false"><img alt="Modifica profilo" title="Modifica profilo" src="../img/edit-icon.gif" width="16" height="16" /></a></td>
            <td class="add_presence"><a href="#" onclick="return false"><img alt="Aggiungi presenza" title="Aggiungi presenza" src="../img/add-icon.gif" width="16" height="16" /></a></td>
            <td class="link_profile"><a href="#"><img alt="Collega profilo" title="Collega profilo" src="../img/not_linked.png" width="16" height="16" /></a></td>
            <td><input class="member_checkbox" name="checklist[]" type="checkbox" value="<?php echo $member->id; ?>" checked /></td>
            <!-- <td id="cancel_profile"><a href="#"><img alt="Elimina socio" title="Elimina socio" src="img/hr.gif" width="16" height="16" alt="" /></a></td>                                    
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
    /* RIchiedo il logger */
    $mylog=$_SESSION['logger'];
    
    /* Creo l'oggetto PHPMailer */
    $mail= new PHPMailer();
    //$mail->SMTPDebug= 3; //Per debug
    
    /* Configuro server SMTP */
    $mail->IsSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = TRUE;
    $mail->SMTPSecure = "ssl";
    $mail->Username = "luca.lisotti@gmail.com";
    $mail->Password = $_POST['psw'];
    $mail->Port = "465";
    
    /* Setto le codifiche e i campi della email */
    $mail->IsHTML(TRUE);
    $mail->CharSet= "ISO-8859-15";
    $mail->setFrom(FROM_ADDRESS, FROM_NAME);
    foreach ($_POST['checklist'] as $id_checked) {
        foreach ($_SESSION['members'] as $member) {
            if($member->id!=$id_checked)
                continue;
            $mail->AddAddress($member->email, $member->nome);
        }         
    }
    $mail->Subject  = $_POST['title'];
    
    /* Costruisco il corpo della mail */
    $message= $_POST['body_message'];
    $message .= file_get_contents('http://localhost/soci/html/newsletter_template.html');
    $mail->msgHTML($message);

    /* Invio email */
    if(!$mail->Send()) {
    echo '<img class="message_sent" src="../img/check_ko.png" height="256" width="256" alt="check_ko.png">';
    echo "\n\n".$mail->ErrorInfo;
    $mylog->logError("Tentativo di invio newsletter da account ".$mail->Username." fallito (".$mail->ErrorInfo.")");
    } else {
    echo '<img class="message_sent" src="../img/check_ok.png" height="256" width="256" alt="check_ko.png">';
    $mylog->logInfo("Tentativo di invio newsletter da account ".$mail->Username." riuscito");
    }
    $mail->clearAttachments();
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
        window.open('../php/root_functions.php?action=DB_functions','', "height=190,width=580");
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
        window.open('../php/root_functions.php?action=view_members_evening','', "height=190,width=580");
    });
});
</script>
</body>
</html>