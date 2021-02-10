<?php
require "php/member.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>

        <!-- Jquery JS-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="js/libs/jSignature.min.noconflict.js"> </script>
    <script src="js/libs/modernizr.js"></script>
    <!-- Vendor JS-->
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/datepicker/moment.min.js"></script>
    <script src="vendor/datepicker/daterangepicker.js"></script>

    
    <!--<script type="text/javascript" src="js/jquery-1.11.1.js"> </script>-->
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Colorlib Templates">
    <meta name="author" content="Colorlib">
    <meta name="keywords" content="Colorlib Templates">

    <!-- Title Page-->
    <title>Osservatorio Copernico - Modulo di registrazione</title>

    <!-- Icons font CSS-->
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i" rel="stylesheet">

    <!-- Vendor CSS-->
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="css/index_guest.css" rel="stylesheet" media="all">
    

</head>
<body>
    <div class="page-wrapper bg-blue p-t-100 p-b-100 font-robo">
        <div class="wrapper wrapper--w680">
            <div class="card card-1">
                <div class="card-heading">
                <?php
                /* Se l'utente accede con Internet Explorer o col browser stock di Android non lo faccio proseguire */
                $ua = htmlentities($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
                $matches = [];
                preg_match ( '/Android.*AppleWebKit\/([\d.]+)/', $ua, $matches);
                if (preg_match('~MSIE|Internet Explorer~i', $ua) || (strpos($ua, 'Trident/7.0') !== false && strpos($ua, 'rv:11.0') !== false)) {
                    echo "<br><br>Il browser Microsoft explorer potrebbe non funzionare correttamente. Si prega di utilizzare un altro browser (ad esempio Google Chrome, Firefox, ecc..)><br><br>";
                    die();
                }
                elseif( isset($matches[0]) && ( isset($matches[1]) && intval($matches[1] < 537))) {
                    echo "<br><br>Il browser stock di Android potrebbe non funzionare correttamente. Si prega di utilizzare un altro browser (ad esempio Google Chrome, Firefox, ecc..)<br><br>";
                    die();
                }
                ?>
                </div>
    <div class="card-body" style="font-size: 17px; font-family: Times new Roman">
        <h2 class="title">Benvenuto nella pagina di gestione soci dell' Osservatorio Copernico</h2>
        <div style="padding: 5px; background-color:  #9d9ea5 ">
        	<br>
        	<form id='form' action=<?php echo "https://{$_SERVER['HTTP_HOST']}/index.php"; ?> method="POST">
            	<table>
            	<tr>
            		<td><h6>Username:&nbsp</h6></td>
            		<td><input class="input--style-1" name="username" type="text"></td>
            		<td align="right"><h6>&nbsp;&nbsp;Password:</h6></td>
            		<td align="right"><input id=psw_input class="input--style-1" name="password" type="password"></td>
            		<td>&nbsp<img id="lock" src="img/locked.png" width="16" height="16" alt="Visualizza password" title="Visualizza password" /></td>
            	</tr>
            	<tr>
            	<td colspan="3"></td>
            		<td><h6>&nbsp</h6></td> <!-- Solo per creare spazio verticale tra caselle e testo Area riservata -->
				</tr>
            	<tr>
            	<td colspan="3"></td>
            		<td><button type="submit" style="float: right;">Area riservata</button></td>
				</tr>
            	</table>
        	</form>
        </div><br>
        <ul style="list-style-type:none; padding-top: 10px; font-family: Arial; font-size:12px">
        	<li>Puoi controllare lo stato della Tua iscrizione e la validita' della tessera per l'anno in corso cliccando <a style="color:blue" href="" id="cerca">qui</a>.</li>
    	</ul>
        <ul style="list-style-type:none; padding-top: 10px; font-family: Arial; font-size:12px">
        	<li>Se non risulti iscritto puoi farlo velocemente compilando il modulo di registrazione sottostante.</li>
        	<li>Dopo la conferma della corretta iscrizione puoi recarti direttamente in sede. Versando la quota associativa di 6€ sara' rilasciata subito la tessera.</li>
        	<li>L'iscrizione e' obbligatoria per individui di eta' uguale o maggiore ad 8 anni.</li>
    	</ul>
		<ul style="list-style-type:none; padding-top: 10px; font-family: Arial; font-size:12px">
			<li>In caso di problemi con l'iscizione o ulteriori chiarimenti invia una segnalazione <a style="color:blue" href="" id="segnalazione">qui</a>.</li>
        </ul>
    	<br><br>
        <h3 class="title">Modulo di registrazione</h3><br>
		<h6 style="text-align:center">- DATI ANAGRAFICI -</h6>
		<br>
        <form id='form' action=<?php echo "https://{$_SERVER['HTTP_HOST']}/php/insmod_guest.php"; ?> method="POST">
		    <div class="row row-space">
		    	<div class="col-2">
            		<div class="input-group">
                        <input id="cognome" style="text-transform: capitalize" class="input--style-1" type="text" placeholder="Cognome" name="cognome" required pattern="^([^0-9]*)$">
					</div>
				</div>
				<div class="col-2">
					<div class="input-group">
                        <input id="nome" style="text-transform: capitalize" class="input--style-1" type="text" placeholder="Nome" name="nome" required pattern="^([^0-9]*)$">
                    </div>
				</div>
			</div>
            <div class="row row-space">
                <div class="col-2">
                    <div class="input-group">
                        <input id="birthday" class="input--style-1 js-datepicker" type="text" placeholder="Data di Nascita" name="data_nascita" required>
                        <i class="zmdi zmdi-calendar-note input-icon js-btn-calendar"></i>
                    </div>
                </div>
                <div class="col-2">
    				<div class="input-group">
    					<input id="cf" class="input--style-1" style="width:80%" type="text" placeholder="Codice Fiscale" name="cf"  maxlength="16" required style="background-image: url(img/credit-card.png); no-repeat; text-indent: 20px">&nbsp;&nbsp;&nbsp;&nbsp;
    					<a target="_blank" rel="noopener noreferrer" href="https://www.codicefiscale.com/"><img src="img/credit-card.png" align="right" title="Calcola codice fiscale online" alt="Calcola codice fiscale online" style="margin-right:5px"></img></a>
    				</div>
				</div>
            </div>
            <span id="nascosto_1" style="visibility:hidden; font-size:10px; font-family: Arial Narrow; color:red">Se nato all'estero selezionare solo lo Stato</span>
			<div class="row row-space">
				<div class="col-3">
                	<div class="input-group">
                        <input id="comune" type="text" pattern="^[a-zA-Z\s]*$" id="cm" class="input--style-1" type="text" placeholder="Comune di nascita" name="comune_nascita" required>
                    </div>
                </div>
                <div class="col-3">
					<div class="rs-select2 js-select-simple select--no-search">
						<select id="provincia_nascita" name="provincia_nascita">
							<!--<option selected="selected">Provincia</option>-->
							    <?php
							    $dati_select=new DataForSelect(NULL, NULL);
							    echo $dati_select->showProvince();
							    ?>
						 </select>
					<div class="select-dropdown"></div>
					</div>
				</div>                         
			    <div class="col-3">
					<div class="rs-select2 js-select-simple select--no-search">
                        <select id="stato_nascita" name="stato_nascita">
    						<!--<option disabled="disabled" selected="selected" required>Stato di nascita *</option>-->
    						<?php
    				        echo $dati_select->showStates();
    						?>
                        </select>
                        <div class="select-dropdown"></div><br>
                    </div>
				</div>
        	</div>
			<h6 style="text-align:center">- DATI DI RESIDENZA -</h6>
			<!-- <h6 style="text-align:center; font-style:italic">(se residente all'estero selezionare solo lo Stato)</h6>  -->
			<br>
			<!-- <span id="nascosto_2" style="visibility:hidden; font-size:10px; font-family: Arial Narrow; color:red">Se residente all'estero selezionare solo lo Stato</span> -->
			<div class="row row-space">
                <div class="col-2">
                    <div class="input-group">
                        <input id="indirizzo" style="text-transform: capitalize" class="input--style-1" type="text" placeholder="Indirizzo" name="indirizzo" required>
                    </div>
			    </div>
				<div class="col-2">
                	<div class="input-group">
                        <input id="citta" style="text-transform: capitalize" class="input--style-1" type="text" placeholder="Citta'" name="citta" required>
                    </div>
                </div>
            </div>
			<span id="nascosto_2" style="visibility:hidden; font-size:10px; font-family: Arial Narrow; color:red">Se residente all'estero selezionare solo lo Stato</span>
			<div class="row row-space">			
				<div class="col-3">					
					<div class="input-group">						
						<input id="cap" pattern="[0-9]*" class="input--style-1" type="text" placeholder="CAP" name="cap" required>
					</div>
				</div>
				<div class="col-3">
					<div class="rs-select2 js-select-simple select--no-search">
						<select id="provincia_residenza" name="provincia">
							<!--<option selected="selected">Provincia</option>-->
							    <?php					    
							    echo $dati_select->showProvince();
							    ?>
						 </select>
					<div class="select-dropdown"></div>
					</div>
				</div>
			<div class="col-3">
				<div class="rs-select2 js-select-simple select--no-search">
					<select id="stato_residenza" name="stato_residenza">
						<!--<option selected="selected" required>Stato</option>-->
						    <?php					    
						    echo $dati_select->showStates();
						    ?>
					</select>
					<div class="select-dropdown"></div>
			    	</div>                  
			</div>
		</div>
		<input id="etaconsenso" name="etaconsenso" type="hidden">
			<h6 style="text-align:center">- CONTATTI -</h6>
			<br>
			<div class="row row-space">
		    <div class="col-2">
                        <div class="input-group">
                            <input type="text" id="telefono" class="input--style-1" placeholder="Telefono" name="telefono">
			</div>
			</div>
			<div class="col-2">
			<div class="input-group">
                            <input type="text" style="text-transform: lowercase" id="email"  class="input--style-1" type="email" placeholder="email" name="email">
                        </div>
			</div>
			</div>
			<input id="adesione_obbligatoria" class="check" type="checkbox" name="adesione_statuto" value="aderisco" checked required><span style="font-size:13px; font-family: Arial Narrow"> Dichiaro di aver preso visione dello <a target="_blank" rel="noopener noreferrer" style="color:blue" href="/doc/art1e2_Statuto.pdf">statuto</a> dell' Associazione senza scopo di lucro denominata "ASSOCIAZIONE CULTURALE GRUPPO ASTROFILI N. COPERNICO" e che, in particolare, condivido gli scopi di natura ideale dell' Associazione (art. 2 dello statuto).</span><br><br>
			<input id="privacy_obbligatoria" class="check" type="checkbox" name="trattamento_dati" value="acconsento" checked required><span style="font-size:13px; font-family: Arial Narrow">Acconsento al trattamento dei miei dati personali da svolgersi in conformita' di quanto indicato <a id="info" target="_blank" rel="noopener noreferrer" style="color:blue" href="/doc/1-INFORMATIVA E CONSENSO PER SOCI_2019.pdf">nell'informativa </a>e nel rispetto delle disposizioni del GDPR e del D. Lgs. n. 196/03.</span><br><br>
			<input id="privacy"class="check" type="checkbox" name="diffusione_nominativo" value="acconsento"><span style="font-size:13px; font-family: Arial Narrow">Acconsento alla diffusione del mio nome e cognome, della mia immagine o di video che mi riprendono nel sito istituzionale, nei social network (es. Facebook, Instagram, Youtube) e sul materiale informativo cartaceo dell'Associazione per soli fini di descrizione e promozione dell'attivita' istituzionale, nel rispetto delle disposizione del GDPR e del D. Lgs. n. 196/03 e delle autorizzazioni/indicazioni della commissione UE e del Garante per la Protezione dei Dati Personali.</span><br><br>
			<input id="news" class="check" type="checkbox" name="newsletter" value="iscritto"><span style="font-size:13px; font-family: Arial Narrow">Desidero iscrivermi alla newsletter per rimanere informato su novita' ed eventi.</span><br><br><br>
			<h5 id="firma" class="input--style-1" style="text-align:left">FIRMA DEL RICHIEDENTE (leggibile)</h5>
			<div class="signature-panel" style="background-color: gainsboro"></div><br>
			<div class="controls-panel">
			      <a href="" class="btn submit-button btn--disabled">Valida firma</a>
			      <a href="" class="btn clear-button" style="padding-left: 20px">Cancella firma</a>
			      <!--<a href="" class="link cancel-link">Cancel</a>-->
			      <!--<a href="" class="link skip-link">Skip for now</a>-->
		  </div>
        		<div class="p-t-20">
                            <button class="btn btn--radius btn--green" type="submit" style="float: right; padding: 5px 20px 5px 20px">Iscriviti</button><br>
                        </div>
                    </form>
	    
                </div>
            </div>
        </div>
    </div>



    <!-- Main JS-->
<script src="js/global.js"></script>
<script type="text/javascript">
$(document).ready(function(){

	/* Inizializzo JSignature */
	$('.signature-panel').jSignature();

	/* Cancello la firma */
    $('.clear-button').on('click', function(e) {
    	e.preventDefault();
    	$('.signature-panel').jSignature("reset");
	});

    /* Gestione salvataggio firma */
    $(".signature-panel").bind("change", function(event){
        if(isValidSignature()) {
          $('.submit-button').removeClass('btn--disabled');
        } else {
          $('.submit-button').addClass('btn--disabled');
        }
      });

    /* Gestione validazione firma */
    $('.submit-button').on('click', function(e) {
        e.preventDefault();
        if(isValidSignature()) {
        	var datapair = $(".signature-panel").jSignature("getData", "image");
    		var i = new Image();
        	i.src = "data:" + datapair[0] + "," + datapair[1];
        	var cognome = $("#cognome").val();
        	var nome = $("#nome").val();
        	var birthday = $("#birthday").val();
        	if(cognome.trim() && nome.trim() && birthday.trim()) {
            	$.ajax({
                    type: "POST",
                    url: "./php/signature.php",
                    data: {firma: datapair[1], cognome: cognome, nome: nome, birthday: birthday},
                    dataType: 'html',
                    /* ritorno un messaggio e visualizzo il popup */
                    success: function (response) {	
                    	if(response=="ok") {
                    		$('.clear-button').hide(); //Una volta inserita correttamente la firma evito che sia cancellata
                    		$('.submit-button').hide(); //Una volta inserita correttamente la firma evito che sia inserita nuovamente
                    		alert("Firma validata correttamente. Ora e' possibile iscriversi");
                    	}
                    	else if (response=="0") {
                    		alert("Errore validazione: la firma e' gia' presente nel nostro database");
                    	}
                    	else {
                    		alert("Errore validazione: ripetere nuovamente la firma assicurandosi che occupi quanto piu' spazio possibile");
                        }
            		}
            	});
        	}
        	else
        		alert("Prima di validare la firma e' necessario riempire almeno i campi obbligatori");
        }
        else
        	alert("Errore validazione: cancellare e ripetere nuovamente la firma. E' necessario che la firma occupi quanto piu' spazio possibile");
    });
     
    /* Funzione per validare o meno una firma */
    function isValidSignature() {
      var canvas = $('.signature-panel canvas')[0];
        var ctx = canvas.getContext('2d');
        var imageData = ctx.getImageData(0,0,canvas.width,canvas.height);
        var filledCount = 0;
        var totalCount = 0;
        for(var i = 0; i < imageData.data.length; i++) {
          if(imageData.data[i] > 0) {
            filledCount++;
          } 
          totalCount++;
        }
        var percentRequired = 0;
        if(window.innerWidth < 330) {
          percentRequired = 3;
        } else if (window.innerWidth > 330 && window.innerWidth < 400) {
          percentRequired = 2;
        } else {
          percentRequired = 0.95;
        }
        console.log(`total filled: ${filledCount / totalCount * 100} / ${percentRequired}`);
        return ((filledCount / totalCount) * 100) > percentRequired;
    }

    /* Validazione cognome
	$("#cognome").blur(function() {
		var hasNumber = /\d/;
		var value = $(this).val();
		if(hasNumber.test(value) || !($.trim(value)))
        	alert("Attenzione: controllare che il cognome immesso sia corretto");
	});
	*/

	/* Validazione nome
	$("#nome").blur(function() {
		var hasNumber = /\d/;
		var value = $(this).val();
		if(hasNumber.test(value) || !($.trim(value)))
        	alert("Attenzione: controllare che il nome immesso sia corretto");
	});
	*/

    /* Validazione codice fiscale (lo controllo quando entro nel campo comune) */
	$("#comune").focus(function() {
    	// http://blog.marketto.it/2016/01/regex-validazione-codice-fiscale-con-omocodia/
    	var pattern = /^(?:[A-Z][AEIOU][AEIOUX]|[B-DF-HJ-NP-TV-Z]{2}[A-Z]){2}(?:[\dLMNP-V]{2}(?:[A-EHLMPR-T](?:[04LQ][1-9MNP-V]|[15MR][\dLMNP-V]|[26NS][0-8LMNP-U])|[DHPS][37PT][0L]|[ACELMRT][37PT][01LM]|[AC-EHLMPR-T][26NS][9V])|(?:[02468LNQSU][048LQU]|[13579MPRTV][26NS])B[26NS][9V])(?:[A-MZ][1-9MNP-V][\dLMNP-V]{2}|[A-M][0L](?:[1-9MNP-V][\dLMNP-V]|[0L][1-9MNP-V]))[A-Z]$/i;
    	if(!$("#cf").val().match(pattern))
        	alert("Attenzione: controllare che il codice fiscale immesso sia corretto");
    });

    /* Validazione data nascita
    $("#birthday").blur(function() {
    	var value = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
    	if(!value.test($('#birthday').val())) {
        	alert("Attenzione: La data deve essere nel formato GG/MM/AAAA (esempio 14/04/1976)");
    	}
    }); */

    /* Validazione data nascita (la controllo quando entro nel campo codice fiscale) */
    $("#cf").focus(function() {
    	var value = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
    	if(!value.test($('#birthday').val())) {
        	alert("Attenzione: La data deve essere nel formato GG/MM/AAAA (esempio 14/04/1976)");
        	//$(this).val('');
    	}
    }); 

	/* Validazione cap */
	$("#cap").blur(function() {
		var hasNumber = /\d/;
		var value = $(this).val();
		if(!$.isNumeric(value) && $.trim(value))
        	alert("Attenzione: controllare che il cap immesso sia corretto");
	});

	/* Validazione numero di telefono  */
	$("#telefono").blur(function() {
		var hasNumber = /\d/;
		var value = $(this).val();
		if(!$.isNumeric(value) && $.trim(value))
        	alert("Attenzione: controllare che il numero di telefono immesso sia corretto.");
	});
	
    /* Se clicco per iscrivermi e non ho messo la firma blocco il submit */
    $(".btn--radius").on('click', function(e) {
    	if($('.clear-button').is(":visible")) { //Controllo se il pulsante cancella firma e' visibile
    		alert("Prima di potersi iscrivere e' necessario validare la firma");
    		e.preventDefault(); //Evito l'azione di default del pulsante: il submit del form
    	}
    });
    
	/* Se l'utente visualizza il sito in portrait gli mando un alert che e' meglio ruotare il telefono */
    if(window.innerHeight > window.innerWidth){
        alert("A fine procedura, nel momento di apporre la firma digitale, e' consigliabile ruotare la vista del dispositivo in senso orizzontale");
    }
    
    /* Controllo se minorenne ed eventualmente cambio messaggio nella firma */
    $("#cf").focus(function() { // La funzione parte quando entro nel campo inserimento codice fiscale
    	var birthday_year = $("#birthday").val().substr(6,4);
    	if((birthday_year) == "")
    		return;
    	var data = new Date();
    	var today_year = data.getFullYear().toString();
    	var age = today_year-birthday_year;
    	if((age) < "18") {
    		$("#firma").text("FIRMA DEL GENITORE/TUTORE (leggibile)"); //Cambio l'intestazione della firma
    		$("#info").attr("href", "/doc/2-INFORMATIVA E CONSENSO PER SOCI MINORENNI.pdf"); //Cambio l'informativa con quella per minorenni...
    		$("#info").text("nell'informativa per minorenni "); //...ed il testo
    		$("#etaconsenso").val("minorenne");
    	}
    	else {
    		$("#firma").text("FIRMA DEL RICHIEDENTE (leggibile)");
    		$("#info").attr("href", "/doc/1-INFORMATIVA E CONSENSO PER SOCI _2019.pdf"); //Cambio l'informativa con quella per maggiorenni...
    		$("#info").text("nell'informativa "); //...ed il testo
    		$("#etaconsenso").val("maggiorenne");
    	}
	});

    /* Controllo che nel caso si voglia iscriversi alla newsletter sia riempito il campo email */
    $("#news").change( function(){
       if( $(this).is(':checked') && $("#email").val() == "") {
    	alert("Per isciversi alla newsletter e' necessario un indirizzo email valido");
    	$(this).prop( "checked", false );
    	}
    });
    
    /* Controllo che si aderisca allo statuto */
    $("#adesione_obbligatoria").change( function(){
       if( $(this).not(':checked')) {
    	alert("Per iscriversi e' necessario aderire allo statuto dell' Associazione");
    	$(this).prop( "checked", true );
    	}
    });
    
    /* Controllo che si aderisca alla parte di privacy obbligatoria */
    $("#privacy_obbligatoria").change( function(){
       if( $(this).not(':checked')) {
    	alert("Per iscriversi e' necessario autorizzare il trattattamento dei dati personali");
    	$(this).prop( "checked", true );
    	}
    });
    
    /* Gestione se mostrare o meno i campi comune di nascita e provincia di nascita a seconda dello stato di nascita */
    $("#stato_nascita").change( function(){
    	var state = $(this).val();
    	if(state != "IT") {
    		$("#comune").val('');
    		$("#comune").prop("disabled", true);
    		$("#comune").attr("placeholder", "");
    		$("#provincia_nascita").prop("disabled", true);
    		$("#provincia_nascita").empty();
    	}
    	else {
    		$("#comune").prop("disabled", false);
    		$("#comune").attr("placeholder", "comune di nascita");
    	}
    });

    /* Gestione se mostrare o meno tutti i campi di residenza a seconda dello stato di residenza */
    $("#stato_residenza").change( function(){
    	var state = $(this).val();
    	if(state != "IT") {
    		//$("#indirizzo").val('');
    		//$("#indirizzo").prop("disabled", true);
    		//$("#indirizzo").attr("placeholder", "");
    		//$("#citta").val('');
    		//$("#citta").prop("disabled", true);
    		//$("#citta").attr("placeholder", "");
    		$("#cap").val('');
    		$("#cap").prop("disabled", true);
    		$("#cap").attr("placeholder", "");
    		$("#provincia_residenza").prop("disabled", true);
    		$("#provincia_residenza").empty();
    	}
    	else {
    		//$("#indirizzo").prop("disabled", false);
    		//$("#indirizzo").attr("placeholder", "indirizzo");
    		//$("#citta").prop("disabled", false);
    		//$("#citta").attr("placeholder", "citta'");
    		$("#cap").prop("disabled", false);
    		$("#cap").attr("placeholder", "cap");
    		//$("#provincia_residenza").prop("disabled", false);
    	}
	});
		
    /* Faccio apparire la scritta di compilazione alcuni campi solo se nato in Italia */
    $("#comune").focus(function() {
    	$("#nascosto_1").css("visibility","visible");
    });
    
    /* Faccio scomparire la scritta di compilazione alcuni campi solo se nato in Italia */
    $("#comune").blur(function() {
    	$("#nascosto_1").css("visibility","hidden");
    });
    
    /* Faccio apparire la scritta di compilazione alcuni campi solo se resisdente in Italia */
    $("#cap").focus(function() {
    	$("#nascosto_2").css("visibility","visible");
    });
    
    /* Faccio scomparire la scritta di compilazione alcuni campi solo se resisdente in Italia */
    $("#cap").blur(function() {
    	$("#nascosto_2").css("visibility","hidden");
    });
        
	/* Ricerca se esiste gia' l'iscrizione */
    $("#cerca").on('click', function(e) {
    	e.preventDefault();
    	var codice_fiscale = prompt("Inserisci il codice fiscale");
    	if(!codice_fiscale)
        	return;
    	$.ajax({
            type: "POST",
            url: "./php/cf.php",
            data: {cf: codice_fiscale},
            dataType: 'html',
            /* ritorno un messaggio e visualizzo il popup */
            success: function (response) {
            	if(response=="ko")
            		alert("I Tuoi dati non risultano nel nostro archivio. E' necessario eseguire una nuova registrazione");
            	else if(response == 0)
            		alert("I Tuoi dati risultano correttamente inseriti nel nostro archivio ma non sei ancora tesserato. E' sufficiente recarsi in Osservatorio e Ti sara' rilasciata subito una nuova tessera");
            	else
            		alert("I Tuoi dati risultano correttamente inseriti nel nostro archivio ed il tuo numero tessera e':  " + response);
            }
    	});
    });

    $("#segnalazione").click(function(e) {
        e.preventDefault();
        window.open('./php/helpdesk.php','', "height=700,width=580,scrollbars=1");
    });


    /* Funzione per gestire la visualizzazione o meno della password */
    $("#lock").on('click', function () {
        if($("#psw_input").attr('type')=='password') {
            $("#psw_input").attr('type', 'text');
            $(this).attr('src', 'img/unlocked.png');
            $(this).attr('title', 'Nascondi password');
        }
        else {
            $("#psw_input").attr('type', 'password');
            $(this).attr('src', 'img/locked.png');
            $(this).attr('title', 'Visualizza password');
        }
    });

});
</script>
</body>
</html>