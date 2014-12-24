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
<link rel="stylesheet" type="text/css" href="../css/profile_viewer.css" media="all"/>
</head> 
<body>
<div class="container">
<div class="form">
<table>		
<?php
if(isset($_SESSION['members']))
{
    foreach($_SESSION['members'] as $member)
    {
        if($member->id==(int)$_GET['id'])
        {
        ?>
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
            <td><label>COGNOME:</label></td>
            <td><input readonly value="<?php echo $member->cognome; ?>"></td>
            <td align="right"><label>NOME:</label></td>
            <td><input readonly value="<?php echo $member->nome; ?>"></td>
        </tr>
        <tr>
            <td><label>NATO/A IL:</label></td> 
            <td><input readonly value="<?php echo $member->data_nascita; ?>"></td>
            <td align="right"><label >A:</label></td>
            <td><input readonly value="<?php echo $member->luogo_nascita; ?>"></td>
            <td><label>SESSO:</label></td>
            <td><input readonly id=sesso value="<?php echo $member->sesso; ?>"></td>
        </tr>
        <tr>
            <td><label>CODICE FISCALE:</label></td>
            <td colspan = '2'><input readonly value="<?php echo $member->codice_fiscale; ?>"></td>
        </tr>
        <tr>
            <td><label>INDIRIZZO:</label></td> 
            <td colspan = '3'><input readonly id=residenza value="<?php echo $member->indirizzo." - ".$member->cap." ".$member->citta." ".$member->provincia." - ".$member->stato; ?>"></td>
        </tr>
        <tr>
            <td><label>TELEFONO:</label></td>
            <td><input readonly value="<?php echo $member->telefono; ?>"></td>
            <td align="right"><label>EMAIL:</label></td> 
            <td colspan = '3'><input readonly id=email value="<?php echo $member->email; ?>"></td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td><label>TESSERA:</label></td>
            <td><input readonly value="<?php echo "N°".$member->tessera." del ".$member->data_tessera; ?>"></td>
            <td align="right"><label>SCADENZA IDENTITA':</label></td>
            <td><input readonly id=scadenza value="<?php echo $member->scadenza_id; ?>"></td>
        </tr>
    </table>
        <?php
        break;	
        }
    }
}
else
    echo "Cookie disabilitati?";
?>
</div>	
</body> 
</html>