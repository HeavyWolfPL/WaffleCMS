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

<div class="form-container">

    <h1>Rejestracja</h1>

    

    <form action="<?php $site_url . "backend/signup.php" ?>" method="post">
        <div class="input-container">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="login" placeholder="Login">
        </div>

        <div class="input-container">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Hasło">
        </div>

        <?php
            if(isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] === true){
                redirect($site_url . '/index.php');
                exit();
            }


            if (isset($_POST['signup-submit']) && $_POST['signup-submit'] === 'Submit') {
                $login = $_POST['login'];
                $password = $_POST['password'];

                if (empty($login) || empty($password)) {
                    echo '<div class="error">
                        Dane nie zostały wypełnione!
                    </div>';
                    echo sendAlert('warning', 'Dane nie zostały wypełnione!');
                } else if (createAccountInDB($database, $login, $password)) {
                    echo sendAlert('success', 'Konto zostało utworzone! Przekierowuję...');
                    $_SESSION['user'] = getUserData($database, $login);

                    sleep(2);
                    redirect($site_url);
                } else {
                    echo sendAlert('error', 'Nie udało się utworzyć konta!');
                }
            }
        ?>

        <button type="submit" name="signup-submit" value="Submit" class="btn accept_btn">
            <span class="btn_icon">
                <i class="fa-solid fa-user-plus"></i>
            </span>
            <span class="btn_text">Zarejestruj się</span>
        </button>

    </form>
</div>