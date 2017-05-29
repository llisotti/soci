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
<title>Gruppo Astrofili "N. Copernico"</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="../css/index.css" media="all"/>
</head>
<body>
<?php
$dbh = new PDO(SOCI_DBCONNECTION, "copernico", ""); 
$fullname_trimmed=trim($_POST['searched']); // Tolgo tutti gli spazi dopo l'ultimo carattere
//$db = new PDO(SOCI_DBCONNECTION, "copernico", "");
$param=$dbh->quote($fullname_trimmed.'%');
$members=$dbh->query("SELECT *, anagrafica.member_id AS primary_id, DATE_FORMAT(anagrafica.data_nascita,'%d/%m/%Y') data_nascita, DATE_FORMAT(anagrafica.scadenza,'%d/%m/%Y') scadenza, DATE_FORMAT(presenze.data,'%d/%m/%Y') data, DATE_FORMAT(presenze.iscrizione,'%d/%m/%Y') iscrizione FROM anagrafica LEFT JOIN presenze ON anagrafica.member_id = presenze.member_id WHERE anagrafica.cognome LIKE $param AND anagrafica.tessera IS NULL ORDER BY anagrafica.cognome, anagrafica.nome ASC");
$rows=$members->fetchAll();
if(empty($rows))
{
    echo "Nessun risultato";
    die();
}

$odd_tr=1;
?>
<div class="table">
<img src="img/bg-th-left.gif" width="8" height="7" alt="" class="left" />
<img src="img/bg-th-right.gif" width="7" height="7" alt="" class="right" />
<table class="listing">
<tr>
    <th class="first" style="width: 30px">ID</th>
    <th style="width: 167px">Cognome e Nome</th>
    <th>Data di nascita</th>
    <th style="width: 180px">Codice Fiscale</th>
    <th>Prima iscrizione</th>
    <th class="last" colspan="2">Azioni</th>
</tr>
<?php
unset($_SESSION['members']); //Evita il memory leak
$members_arr=array();

foreach($rows as $row)
{
    $odd_tr++;
    if($odd_tr%2==0)
        echo "<tr>";
    else
        echo "<tr class='bg'>";
?>
    <td class="first style3"><?php echo $row['member_id'] ?></td>
    <td><?php echo $row['cognome']." ".$row['nome'] ?></td>
    <td><?php echo $row['data_nascita'] ?></td>
    <td><?php echo $row['cf'] ?></td>
    <td><?php echo $row['iscrizione'] ?></td>
    <td class="edit_profile"><a href="#" onclick="return false"><img alt="Modifica profilo" title="Modifica profilo" src="../img/edit-icon.gif" width="16" height="16" /></a></td>
    <!--
    <td class="link_profile"><a href="#"><img alt="Collega profilo" title="Collega profilo" src="img/not_linked.png" width="16" height="16" /></a></td>    
    <td id="cancel_profile"><a href="#"><img alt="Elimina socio" title="Elimina socio" src="img/hr.gif" width="16" height="16" alt="" /></a></td>                                    
    <td><img src="img/save-icon.gif" width="16" height="16" alt="save" /> </td>
    -->
</tr>
<?php
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
    array_push($members_arr, $member);
}
$_SESSION['members']=$members_arr;
?>
</table>
</div>
<script type="text/javascript" src="../js/jquery-1.11.1.js"> </script>
<script type="text/javascript">
$(document).ready(function(){
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
    
            /* Funzione di gestione 'Modifica profilo' */
    $("td.edit_profile").click(function() {
        //recupero il testo dentro due td precedenti (che per come ho strutturato la tabella è il numero di tessera)
        var member_id = $(this).siblings(":first").text();
        //lo invio in GET alla nuova finestra contenente la pagina "profile_editor.php" inviando il numero tessera quindi MODIFICO il socio
        window.location.href='./profile_editor.php?id='+member_id;
    });
    

});
</script>
</body>
</html>
