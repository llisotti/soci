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
<?php
if(isset($_SESSION['members']))
{
    foreach($_SESSION['members'] as $member)
    {
        if($member->codice_fiscale==$_GET['codice_fiscale'])
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
            <td style="text-align:right"><label>COGNOME:</label></td>
            <td><input readonly value="<?php echo $member->cognome; ?>"></td>
            <td style="text-align:right"><label>NOME:</label></td>
            <td><input readonly value="<?php echo $member->nome; ?>"></td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td style="text-align:right"><label>NATO/A IL:</label></td> 
            <td><input readonly value="<?php echo $member->data_nascita; ?>"></td>
            <td style="text-align:right"><label>A:</label></td>
            <td colspan="3"><input readonly id="nascita" value="<?php if($member->stato_nascita=="IT") echo $member->comune_nascita." (".$member->provincia_nascita.") - (".$member->stato_nascita.")"; else echo "(".$member->stato_nascita.")" ;?>"></td>
            <!--<td><label>SESSO:</label></td>
            <td><input readonly id=sesso value="<?php echo $member->sesso; ?>"></td>-->
        </tr>
        <tr>
            <td style="text-align:right"><label>CODICE FISCALE:</label></td>
            <td><input readonly value="<?php echo $member->codice_fiscale; ?>"></td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td style="text-align:right"><label>INDIRIZZO:</label></td> 
            <td colspan="4"><input readonly id=residenza value="<?php echo $member->indirizzo." - ".$member->cap." ".$member->citta." (".$member->provincia.") - (".$member->stato.")"; ?>"></td>
			<td></td>
        </tr>
        <tr>
            <td style="text-align:right"><label>TELEFONO:</label></td>
            <td><input readonly value="<?php echo $member->telefono; ?>"></td>
            <td style="text-align:right"><label>EMAIL:</label></td> 
            <td colspan="3"><input readonly id=email value="<?php echo $member->email; ?>"></td>
            
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td style="text-align:right"><label>TESSERA:</label></td>
            <td><input readonly value="<?php if($member->tessera) echo "N°".$member->tessera." del ".$member->data_tessera; else echo "" ?>"></td>
            <td style="text-align:right"><label>SCADENZA ISCRIZIONE:</label></td>
            <td><input readonly value="<?php echo $member->scadenza; ?>"></td>
            <td colspan="2"></td>
        </tr>
    </table>
        <?php
        break;	
        }
    }
}
else
    echo "Sessione scaduta?";
?>
</div>
</div>	
</body> 
</html>