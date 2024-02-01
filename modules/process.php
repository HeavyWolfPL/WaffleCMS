<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '../logs/PHP.log');
error_reporting(E_ALL);

require_once dirname(__DIR__) . "/backend/functions.php";
require_once dirname(__DIR__) . "/backend/config.php";
require_once dirname(__DIR__) . "/backend/database.php";

?>

<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = connectToDB($database_info['host'], $database_info['username'], $database_info['password'], $database_info['database']);
    if (isset($_POST['updateTheme']) && isset($_POST['currentTheme']) && !empty($_POST['currentTheme'])) {
        
        try {
            if (isset($_SESSION['user']['id'])) {
                updateUserData($database, $_SESSION['user']['id'], array(
                    'color_mode' => $_POST['currentTheme']
                    )
                );
            }
            echo sendAlert('info', 'Zmieniono motyw');
            setcookie('color_mode', $_POST['currentTheme'], time() + (86400 * 30), "/"); // Cookie will expire in 30 days
        } catch (Exception $e) {
            http_response_code(400); // Bad request status code
            echo 'Unable to update theme: ' . $e->getMessage();
            logError($e->getMessage(), "erorr", "process.php::updateTheme", "website");
        }
    }
} else {
    http_response_code(400); // Bad request status code
    echo 'Invalid request method';

    sleep(2);
    redirect($site_url);
}
?>
