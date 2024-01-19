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
    if (isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] == true) {
        redirect($site_url . '/index.php');
    }

    if (isset($_POST['ajax']) && isset($_POST['loginUser'])) {

        $login = $_POST['login'];
        $password = $_POST['password'];

        if (empty($login) || empty($password)) {
            echo sendAlert('warning', 'Dane nie zostały wypełnione!');
            http_response_code(401);
            return false;
        }

        if (verifyLogin($database, $login, $password)) {
            echo sendAlert('success', 'Zalogowano pomyślnie! Przekierowuję...');
            $_SESSION['user'] = getUserData($database, $login);
            $_SESSION['color_mode'] = $_SESSION['user']['color_mode'];

            sleep(2);
            redirect($site_url);
            exit();
        } else {
            unset($login, $password, $_POST['ajax'], $_POST['loginUser'], $_POST['login'], $_POST['password']);
            echo sendAlert('error', 'Nieprawidłowa nazwa użytkownika lub hasło!');
            http_response_code(401);
            return false;
        }
    }
?>

<div class="form-container">

    <h1>Logowanie</h1>
    <form id='loginForm' method="post">
        <div class="input-container">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="login" id="login_login" placeholder="Nazwa użytkownika">
        </div>

        <div class="input-container">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" id="login_password" placeholder="Hasło">
        </div>

        <button type="submit" name="login-submit" value="Submit" class="btn accept_btn" onclick="loginUser()">
            <span class="btn_icon">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </span>
            <span class="btn_text">Zaloguj się</span>
        </button>
    </form>
</div>