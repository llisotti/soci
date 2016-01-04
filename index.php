<?php
/* OBBLIGATORIO AVERE IL TEMPLATE DELLA CLASSE PRIMA DELL'INIZIO DELLA SESSIONE! */
if(PHP_OS=="Linux")
    require "php/member.php";
else
    require "php\member.php";

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

/**
 * @mainpage GESTIONE SOCI
 * @section Versione
 * 2.8
 * @section Descrizione
 * Gestione soci Osservatorio Copernico
 * @section Requisiti
 * @li Necessita della classe PHPMailer per la gestione della Newsletter
 * @section Autore
 * Luca Lisotti
 */

?>
<!DOCTYPE html>
<html>
<head>
<style></style>
<title>Gruppo Astrofili "N. Copernico"</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="css/index.css" media="all"/>
</head>
<body>
<?php
$member_obj=array(); //Array per oggetti socio

/* Inizializzo il logger, pulisco i vecchi file di sessione e controllo gli aggiornamenti*/
if(!isset($_SESSION['logger'])) {
    try {
        $mylog=new Logger(LOGFILE_PATH, LOGFILE_MAXSIZE);   
        $mylog->logInfo("Inizio nuova sessione@".VERSION);
    } catch (MyException $ex) {
        die($ex->show());
    }
    
    /* Faccio pulizia dei file delle vecchie sessioni */
    $iterator=new DirectoryIterator(LOGFILE_PATH);
    $session_files=array();
    foreach ($iterator as $fileinfo) {
        $namefile=$fileinfo->getFilename();
        if(substr($namefile, 0, 4 ) === "sess") { //Se è un file di sessione
            $accessed = $fileinfo->getAtime();
            $session_files[$accessed]=$namefile;

        }
    }
    krsort($session_files);
    $index=0;
    foreach ($session_files as $key => $value) {
        if($index!=0) //Il primo elemento e' il file di sessione corrente quindi non lo devo cancellare
            unlink(LOGFILE_PATH.$session_files[$key]);
        $index++;
    }
    
    /* Controllo se c'è connessione internet */
    $_SESSION['update']=FALSE; //La prima volta suppongo che non ci siano aggiornamenti disponibili
    $local_commit = NULL;
    $remote_commit=NULL;
    system("ping -w 3 www.google.com > null", $local_commit);
    system("ping -w 3 www.bitbucket.org > null", $remote_commit);
    /* Se sono connesso ad internet controllo se ci sono aggiornamenti */
    if($local_commit == 0 && $remote_commit == 0) {
        $mylog->logInfo("Connessione ad internet e repository remoto ok, controllo se ci sono aggiornamenti");
        exec(GIT_EXECUTABLE."fetch -v -t");
        $local_commit=exec(GIT_EXECUTABLE."rev-parse @");
        $_SESSION['local_commit_hash']=$local_commit;
        $remote_commit=exec(GIT_EXECUTABLE."rev-parse @{u}");
        //echo "HASH LOCALE: $local_commit HASH REMOTO: $remote_commit";
        if($local_commit != $remote_commit && $remote_commit!="") {
            $_SESSION['update']=TRUE; //Se ci sono aggiornamenti lo segnalo
            $mylog->logInfo("Sono presenti aggiornamenti software");
        }
        else
            $mylog->logInfo("Non sono presenti aggiornamenti software");
    }
    else {
        $local_commit=exec(GIT_EXECUTABLE."rev-parse @");
        $_SESSION['local_commit_hash']=$local_commit;
        $mylog->logInfo("Nessuna connessione ad internet disponibile oppure repository remoto non raggiungibile");
    }
}
else
    $mylog=$_SESSION['logger'];


/* Mi connetto al database */
try {
    if(!isset($dbh)) {
        $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");       
    }
}
catch (PDOException $exception) {
    $mylog->logError("Errore di connessione al database: ".$exception->getMessage());
    die("Errore di connessione al database: ".$exception->getMessage());
}
?>
<div id="main">
<div id="header">
<a class="logo"><img src="img/logo_copernico.jpg" width="300" height="54" alt="" /></a>
<a class="version" alt="<?php echo $_SESSION['local_commit_hash']; ?>" title="<?php echo $_SESSION['local_commit_hash']; ?>"><?php echo VERSION; ?></a>
<ul id="top-navigation">
    <li class="active"><span><span><a href="<?php echo $_SERVER['PHP_SELF']; ?>">Home</a></span></span></li>
    <li><span><span><a href="http://localhost/soci/php/profile_editor.php">Profilo</a></span></span></li>
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
    <?php
    if($_SESSION['update']) {
        ?>
        <li class="last"><a href="./php/root_functions.php?action=update">Aggiornamento sw (*)</a></li>
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
        /* Se sono stati inseriti soci per questa serata visualizzo quanti altrimenti visualizzo 0 */
        if(!isset($_SESSION['members_evening'])) {
            $_SESSION['members_evening']=array();
            $_SESSION['members_evening'][0]=0;
        }
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
        <img style="border:0;width:32px;height:32px" src="./img/HTML5_Logo.png" alt="HTML5 compliance code!" title="HTML5 compliance code!" />
    </td> 
    <td>   
        <img style="border:0;width:88px;height:31px" src="./img/vcss.gif" alt="CSS Valido!" title="CSS Valido!" />
    </td>        
    </tr>
</table>
</div>
<div id="center-column">
<div class="top-bar"> <a href="http://localhost/soci/php/profile_editor.php" class="button" title="Aggiungi nuovo socio"></a>
<?php
/* Se non passo nulla in GET visualizzo i soci più recenti */
if(empty($_GET) || !isset($_GET['show']))
{
    $members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica INNER JOIN presenze ON anagrafica.member_id = presenze.member_id AND anagrafica.tessera IS NOT NULL ORDER BY anagrafica.tessera DESC LIMIT ".MEMBERS_RECENT_MAX);
    echo "<h1>ELENCO SOCI RECENTI (".$members->rowCount().")</h1>";
}
else
{
    switch ($_GET['show'])
    {
        case "Cerca": //Visualizzo le identità (persone in anagrafica + soci= TUTTI) cercate
            $fullname_trimmed=trim($_GET['fullname']); // Tolgo tutti gli spazi dopo l'ultimo carattere
            $param=$dbh->quote('%'.$fullname_trimmed.'%');
            $members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica LEFT JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.cognome LIKE $param || anagrafica.nome LIKE $param ORDER BY anagrafica.cognome ASC");
            echo "<h1>ELENCO IDENTITA' TROVATE (".$members->rowCount().")</h1>";
            $mylog->logInfo("Ricerca (".$fullname_trimmed.") tra le identità");
            break;
        case "allmembers": //Visualizzo tutti i soci
            $members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica INNER JOIN presenze WHERE anagrafica.member_id = presenze.member_id AND anagrafica.tessera IS NOT NULL ORDER BY anagrafica.tessera DESC");
            echo "<h1>ELENCO SOCI COMPLETO (".$members->rowCount().")</h1>";
            break;
        case "allidentities": //Visualizzo tutte le identità (persone in anagrafica + soci= TUTTI)
            $members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica LEFT JOIN presenze ON anagrafica.member_id = presenze.member_id ORDER BY anagrafica.cognome ASC");
            echo "<h1>ELENCO IDENTITA' COMPLETO (".$members->rowCount().")</h1>";
            break;
        case "recentmembers": //Visualizzo i soci più recenti
        default:
            $members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica INNER JOIN presenze WHERE anagrafica.member_id = presenze.member_id AND anagrafica.tessera IS NOT NULL ORDER BY anagrafica.tessera DESC LIMIT ".MEMBERS_RECENT_MAX);
            echo "<h1>ELENCO SOCI RECENTI (".$members->rowCount().")</h1>";
            break;
    }
}
?>
</div>
<br />
<div class="select-bar">
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<input id="ricerca" autocomplete="off" type="text" name="fullname" placeholder="Cognome o Nome"/>
<input type="submit" name="show" value="Cerca" />
<!-- <input type="hidden" name="nonserve" value="true"/>  Barbatrucco per passare variabile in GET dopo $_SERVER['PHP_SELF'] -->
</form>
</div>
<div class="table">
<img src="img/bg-th-left.gif" width="8" height="7" alt="" class="left" />
<img src="img/bg-th-right.gif" width="7" height="7" alt="" class="right" />
<?php
$rows=$members->fetchAll();
$odd_tr=1;
?>
<table class="listing">
    <tr>
        <th class="first" style="width: 30px">ID</th>
        <th style="width: 167px">Cognome e Nome</th>
        <th>Data di nascita</th>
        <th>N° Tessera</th>
        <th>Data Tessera</th>
        <th colspan="3">Azioni</th>
        <th class="last">Prima Iscrizione</th>
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
        $member->data_nascita=($row['data_nascita']);
        $member->luogo_nascita=($row['luogo_nascita']);
        $member->sesso=($row['sesso']);
        $member->codice_fiscale=($row['cf']);
        $member->indirizzo=($row['indirizzo']);
        $member->cap=($row['cap']);
        $member->citta=($row['citta']);
        $member->provincia=($row['provincia']);
        $member->stato=($row['stato']);
        $member->telefono=($row['telefono']);
        $member->email=($row['email']);
        $member->tessera=$row['tessera'];
        $member->data_iscrizione=$row['iscrizione'];
        $member->scadenza_id=($row['scadenza']);
        $member->data_tessera=($row['data']); //Data del tesseramento

        /* Lo aggiungo all'array che contiene gli oggetti soci */
        array_push($member_obj, $member);
        ?>
            <td class="first style3"><?php echo $member->id ?></td>
            <?php if(isset($_GET['show']) && $_GET['show']=="Cerca") { ?>
            <td><?php echo str_ireplace($fullname_trimmed, '<span style="color: red; text-transform: uppercase";>'.$fullname_trimmed.'</span>', $member->cognome." ".$member->nome); ?></td>
            <?php } else { ?><td><?php echo $member->cognome." ".$member->nome ?></td> <?php } ?>
            <td><?php echo $member->data_nascita ?></td>
            <td><?php echo $member->tessera ?></td>
            <td><?php echo $member->data_tessera ?></td>
            <td class="see_profile"><a href="#" onclick="return false"><img alt="Visualizza profilo completo" title="Visualizza profilo completo" src="img/login-icon.gif" width="16" height="16" /></a></td>
            <td class="edit_profile"><a href="#" onclick="return false"><img alt="Modifica profilo" title="Modifica profilo" src="img/edit-icon.gif" width="16" height="16" /></a></td>
            <td class="link_profile"><a href="#"><img alt="Collega profilo" title="Collega profilo" src="img/not_linked.png" width="16" height="16" /></a></td>
            <td><?php echo $member->data_iscrizione=$row['iscrizione']; ?></td>
            <!--
            <td id="cancel_profile"><a href="#"><img alt="Elimina socio" title="Elimina socio" src="img/hr.gif" width="16" height="16" alt="" /></a></td>                                    
            <td><img src="img/save-icon.gif" width="16" height="16" alt="save" /> </td>
            -->
        </tr>
    <?php	
    }
    /* Creo una variabile di sessione e gli metto dentro l'array che contiene gli oggetti soci viaualizzati */
    $_SESSION['members']=$member_obj;
    $_SESSION['logger']=$mylog;
    ?>
</table>
<br/>
<div class="select-bar_bottom">
</div>
</div>
</div>
<div id="footer">
</div>
</div>
</div>
<script type="text/javascript" src="js/jquery-1.11.1.js"> </script>
<!-- <script type="text/javascript" src="js/jquery-ui-1.11.2/jquery-ui.js"> </script> -->
<script type="text/javascript">
$(document).ready(function(){
    
    /* Funzione di gestione esportazione elenco soci */
    $("a#esporta_soci").click(function() {
        window.open('./php/root_functions.php?action=members_export','', "height=190,width=580");
    });
    
        
    /* Funzione di gestione esportazione elenco identità */
    $("a#esporta_identita").click(function() {
        window.open('./php/root_functions.php?action=identities_export','', "height=190,width=580");
    });
        
    
    /* Funzione di creazione backup */
    $("a#DB_functions").click(function() {
        window.open('./php/root_functions.php?action=DB_functions','', "height=190,width=580");
    });
    
        
    /* Funzione di gestione 'Visualizza profilo' */
    $("td.see_profile").click(function() {
        //recupero il testo dentro il td precedente (che per come ho strutturato la tabella è il member_id)
        var member_id = $(this).siblings(":first").text();
        //lo invio in GET alla nuova finestra contenente la pagina "profile_viewer.php"
        window.open('./php/profile_viewer.php?id='+member_id,'', "height=190,width=580");
    });


    /* Funzione di gestione 'Modifica profilo' */
    $("td.edit_profile").click(function() {
        //recupero il testo dentro due td precedenti (che per come ho strutturato la tabella è il numero di tessera)
        var member_id = $(this).siblings(":first").text();
        //lo invio in GET alla nuova finestra contenente la pagina "profile_editor.php" inviando il numero tessera quindi MODIFICO il socio
        window.location.href='./php/profile_editor.php?id='+member_id;
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
    
    
    /* Funzione visualizzazione tessere inserite nella sessione */
    $("a#view").click(function() {
        window.open('./php/root_functions.php?action=view_members_evening','', "height=190,width=580,scrollbars=1");
    });
    
});
</script>
</body>
</html>
