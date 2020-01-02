<?php
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


if(!isset($_SESSION['url'])) {
    $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
}

/* Se non mi sono loggato devo autenticarmi */
if (!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
    /* Procedo solo se e' sato inserito lo username... */
    if (isset($_POST['username'])) {
        try {
            $md5password=md5($_POST['password'], FALSE);
            $dbh = new PDO(SOCI_DBCONNECTION, "copernico", "");
            $prepared=$dbh->prepare("SELECT COUNT(*) FROM logins WHERE username=? AND psw=?");
            $prepared->execute([$_POST['username'], $md5password]);
            $counter=$prepared->fetch(PDO::FETCH_COLUMN);
            /* Se non ho trovato niente con quel username e password svuoto le variabili in POST ed esco */
            if(!$counter) {
                header('Location: '.$_SESSION['url']);
                unset($_POST['username']);
                unset($_POST['password']);
                exit;
            }
            /**
             * Se mi sono autenticato correttamente metto le credenziali in due variabili di sessione
             * Occhio, cio' significa che tutte le pagine devono supportare le sessioni altrimenti le variabili non si propagano
             */
            else {
                $_SESSION['username']=$_POST['username'];
                $_SESSION['password']=$md5password;
            }
        }
        catch (PDOException $exception) {
            $mylog->logError("Errore di connessione al database: ".$exception->getMessage());
            die("Errore di connessione al database: ".$exception->getMessage());
        }
    }
    /* ...altrimenti esco */
    else {
        header('Location: '.$_SESSION['url']);
        exit;
    }
}

?>