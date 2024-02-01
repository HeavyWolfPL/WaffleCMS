<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '/logs/PHP.log');
error_reporting(E_ALL);


require_once __DIR__ . "/backend/functions.php";
require_once __DIR__ . "/backend/config.php";
require_once __DIR__ . "/backend/database.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WaffleCMS</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/buttons-inputs.css">
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/login.css">

    <?php
        if (isset($_COOKIE['color_mode']) && $_COOKIE['color_mode'] == 'light') {
            echo "<link rel='stylesheet' href='css/colors_light_mode.css' id='theme-mode'>";
        } else {
            echo "<link rel='stylesheet' href='css/colors_dark_mode.css' id='theme-mode'>";
        }
    ?>

    <!-- FontAwesome 6 Free -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- FavIcon -->
    <link rel="shortcut icon" href="https://i.imgur.com/g3a3tLo.png" type="image/x-icon">

    <!-- JS -->
    <script src="frontend/functions.js"></script>
    <script src="frontend/ajax.js"></script>

</head>
<body>

<div id="alertContainer"></div> <!-- Alert Container, used by functions.js::sendAlert() / backend/functions.php::sendAlert() -->

<header>

    
    <a href="<?php echo $site_url ?>"><div id="header-logo" class="logo-container">
        <img src="https://i.imgur.com/g3a3tLo.png" alt="Logo WaffleCMS">WaffleCMS
    </div></a>

    <div id="header-buttons" class="flex-space-around">
        <?php
        
            if (isset($_COOKIE['color_mode']) && $_COOKIE['color_mode'] == 'light') {
                echo '<button class="btn link" id="updateTheme" onclick="updateTheme(this)" data-current_theme="dark">
                        <span class="btn_icon">
                            <i class="fa-solid fa-sun"></i>
                        </span>
                    </button>';
            } else {
                echo '<button class="btn link" id="updateTheme" onclick="updateTheme(this)" data-current_theme="light">
                        <span class="btn_icon">
                            <i class="fa-solid fa-moon"></i>
                        </span>
                    </button>';
            }
        ?>
        
        <?php
            if (isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] == true) {        
                echo "<button class='btn link' onclick='logoutUser()'>
                    <span class='btn_icon'>
                        <i class='fa-solid fa-arrow-right-from-bracket'></i>
                    </span>
                    <span class='btn_text'>Wyloguj</span>
                </button>";

                if (isset($_SESSION['user']['access']) && $_SESSION['user']['access'] == 'Staff') {
                    echo "<button class='btn link'><a href='admin/index.php'>
                        <span class='btn_icon'>
                            <i class='fa-solid fa-user-secret'></i>
                        </span>
                        <span class='btn_text'>Admin</span>
                    </a></button>";
                }
            }

        ?>
    </div>

</header>

<main>

    <?php

    $database = connectToDB($database_info['host'], $database_info['username'], $database_info['password'], $database_info['database']);

    if (isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'] == true) {        
        if (isset($_GET['mode']) && $_GET['mode'] == 'post') {
            include __DIR__ . '/modules/post.php';
        } else if (isset($_GET['mode']) && $_GET['mode'] == 'createPost') {
            include __DIR__ . '/modules/createPost.php';
        } else {
            include __DIR__ . '/main.php';
        }

        include __DIR__ . '/modules/settings.php';
    } else {
        echo "
            <div id='flipCard' class='flip-card'>
                <div class='flip-card-inner'>
                    <div class='flip-card-front'>";
                        include __DIR__ . "/backend/login.php";
        echo "      </div>
                    <div class='flip-card-back'>";
                        include __DIR__ . "/backend/signup.php";
        echo "      </div>
                </div>
                <button id='flipCardBtn' class='btn' onclick='flipCard()'>
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