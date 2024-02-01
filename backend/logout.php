<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '../logs/PHP.log');
error_reporting(E_ALL);

require_once __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/config.php";
?>

<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] == 'true') {
    foreach ($_GET as $key => $value) {
        unset($_GET[$key]);
    }
    
    foreach ($_POST as $key => $value) {
        unset($_POST[$key]);
    }

    foreach ($_SESSION as $key => $value) {
        unset($_SESSION[$key]);
    }

    session_destroy();
} else {
    redirect($site_url);
}

?>