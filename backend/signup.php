<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '/logs/PHP.log');
error_reporting(E_ALL);


require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/database.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<?php
    if (!isset($database)) $database = connectToDB($database_info['host'], $database_info['username'], $database_info['password'], $database_info['database']);

    $is_staff = false;
    if (isset($_SESSION['user']['access']) && $_SESSION['user']['access'] == 'Staff') {
        $is_staff = true;
    }
?>

<?php
    if (isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] === true){
        redirect($site_url . '/index.php');
    }

    if (isset($_POST['ajax']) && isset($_POST['signUpUser'])) {

        $login = $_POST['login'];
        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];

        if (empty($login) || empty($password)) {
            echo sendAlert('warning', 'Dane nie zostały wypełnione!');
            http_response_code(401);
            return false;
        }

        if ($password !== $repeat_password) {
            echo sendAlert('warning', 'Hasła nie są takie same!');
            http_response_code(401);
            return false;
        }

        if (createAccountInDB($database, $login, $password)) {
            echo sendAlert('success', 'Konto zostało utworzone! Przekierowuję...');
            $_SESSION['user'] = getUserData($database, $login);
            logError(var_export($_SESSION, true));

            sleep(2);
            redirect($site_url);
            return true;
        } else {
            unset($login, $password, $_POST['ajax'], $_POST['signUpUser'], $_POST['login'], $_POST['password']);
            echo sendAlert('error', 'Nie udało się utworzyć konta!');
            http_response_code(500);
            return false;
        }
    }
?>

<div class="form-container">

    <h1>Rejestracja</h1>

    <form id='signUpForm' method="post">
        <div class="input-container">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="login" id="signup_login" placeholder="Login">
        </div>

        <div class="input-container">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" id="signup_password" placeholder="Hasło">
        </div>

        <div class="input-container">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="repeat_password" id="signup_repeat_password" placeholder="Powtórz Hasło">
        </div>

        <button type="submit" name="signup-submit" value="Submit" class="btn accept_btn" onclick="signUpUser()">
            <span class="btn_icon">
                <i class="fa-solid fa-user-plus"></i>
            </span>
            <span class="btn_text">Zarejestruj się</span>
        </button>

    </form>
</div>