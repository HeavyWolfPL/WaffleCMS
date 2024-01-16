<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '/logs/PHP.log');
error_reporting(E_ALL);


require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/database.php";

?>

<?php
    if (isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] == true) {
        redirect($site_url . '/index.php');
    }
?>

<div class="form-container">

    <h1>Logowanie</h1>
    <form action="<?php $site_url . "backend/login.php" ?>" method="post">

        <div class="input-container">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="login" placeholder="Nazwa użytkownika">
        </div>

        <div class="input-container">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Hasło">
        </div>

        <?php
            if (isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] == true) {
                redirect($site_url . '/index.php');
            }

            if (isset($_POST['login-submit']) && $_POST['login-submit'] === 'Submit') {
                $login = $_POST['login'];
                $password = $_POST['password'];

                if (empty($login) || empty($password)) {
                    echo sendAlert('warning', 'Dane nie zostały wypełnione!');
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
                    unset($login, $password, $_POST['login-submit'], $_POST['login'], $_POST['password']);
                    echo sendAlert('error', 'Nieprawidłowa nazwa użytkownika lub hasło!');
                }
            }
        ?>

        <button type="submit" name="login-submit" value="Submit" class="btn accept_btn">
            <span class="btn_icon">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </span>
            <span class="btn_text">Zaloguj się</span>
        </button>

    </form>
</div>