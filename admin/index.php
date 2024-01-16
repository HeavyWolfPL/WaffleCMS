<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '../logs/PHP.log');
error_reporting(E_ALL);


require_once dirname(__DIR__) .  "/backend/functions.php";
require_once dirname(__DIR__) .  "/backend/config.php";
require_once dirname(__DIR__) .  "/backend/database.php";

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projekt - WaffleCMS</title>

    <!-- CSS -->
    <link rel="stylesheet" href="/cms/css/buttons-inputs.css">
    <link rel="stylesheet" href="/cms/css/header_footer.css">
    <link rel="stylesheet" href="/cms/css/main.css">
    <link rel="stylesheet" href="/cms/css/sidebar.css">

    <!-- FontAwesome 6 Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- JS -->
    <script src="../frontend/functions.js"></script>

</head>
<body>

<div id="alertContainer"></div> <!-- Alert Container, used by functions.js::sendAlert() / backend/functions.php::sendAlert() -->

<header>

    <a href="<?php echo $site_url ?>"><div id="header-logo" class="logo-container">
        <img src="https://i.imgur.com/g3a3tLo.png" alt="Logo WaffleCMS">WaffleCMS
    </div></a>

    <div id="header-buttons" class="flex-space-around">
        <button class="btn link"><a href="backend/logout.php">
            <span class="btn_icon">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </span>
            <span class="btn_text">Wyloguj</span>
        </a></button>
    </div>

</header>

<main>

    <?php

    session_start();

    $database = connectToDB($database_info['host'], $database_info['username'], $database_info['password'], $database_info['database']);

    if (isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] == true) {        
        include dirname(__DIR__) . '/main.php';
    } else {
        echo "
            <div class='flip-card'>
                <div class='flip-card-inner'>
                    <div class='flip-card-front'>";
                        include dirname(__DIR__) . "/backend/login.php";
        echo "      </div>
                    <div class='flip-card-back'>";
                        include dirname(__DIR__) . "/backend/signup.php";
        echo "      </div>
                </div>
                <button class='btn' onclick='flipCard()'>
                    <span class='btn_icon'>
                        <i class='fa-solid fa-rotate'></i>
                    </span>
                    <span class='btn_text'>Zarejestruj się</span>
                </button>
            </div>";
    }
    ?>


</main>

<footer>

    <!-- Co ty tutaj robisz? -->
    <span id="copyright" class="copyright-container">
        <i class="fa-regular fa-copyright"></i>
        2023 Wafelowski.pl
    </span>

</footer>
    
</body>
</html>