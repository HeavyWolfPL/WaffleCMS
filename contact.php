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
    <title>Projekt - WaffleCMS</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/buttons-inputs.css">
    <link rel="stylesheet" href="css/header_footer.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/sidebar.css">
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
                echo "<button class='btn link'><a href='backend/logout.php'>
                    <span class='btn_icon'>
                        <i class='fa-solid fa-arrow-right-from-bracket'></i>
                    </span>
                    <span class='btn_text'>Wyloguj</span>
                </a></button>";
            }

        ?>
        
        <button class="btn link"><a href="admin/index.php">
            <span class="btn_icon">
                <i class="fa-solid fa-user-secret"></i>
            </span>
            <span class="btn_text">Admin</span>
        </a></button>
    </div>

</header>

<main>

    <section class="contactForm">
        <?php
            if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] == 'true') {
                $name = $_POST["name"];
                $email = $_POST["email"];
                $message = $_POST["message"];
            
                // $to = "admin@wafflecms";
                // $subject = "Formularz kontaktowy - WaffleCMS ({$name}) - {$email}";
                // $body = "Nazwa: $name\nEmail: $email\nWiadomość: \n$message";
                // $headers = "From: no-reply@wafflecms";
            
                // try {
                //     mail($to, $subject, $body, $headers);
                //     echo "Wysłano, dzięki za kontakt $name!";
                // } catch (Exception $e) {
                //     echo "Wystąpił problem podczas wysyłania wiadomości. Spróbuj ponownie później.";
                //     logError("Wystąpił problem podczas wysyłania wiadomości. Spróbuj ponownie później. {$e->getMessage()}", 'error', 'contact.php', 'Website');
                //     throw new Exception("Wystąpił problem podczas wysyłania wiadomości. Spróbuj ponownie później.");
                // }

                echo "Wysłano, dzięki za kontakt $name!";
                logError("\nNazwa: $name\nEmail: $email\nWiadomość: \n$message", 'info', 'contact.php', 'Website');
            }
        ?>

        <form id="contactForm" class="form-container" method="post">
            <h1>Skontaktuj się z nami!</h1>
            
            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="name" id="name" placeholder="Jan Nowak">

                <i class="fa-solid fa-at"></i>
                <input type="email" name="email" id="email" placeholder="adres@email.pl">
            </div>

            <div class="input-container">
                
            </div>

            <div class="input-container">
                <textarea name="message" id="message" rows="10" cols="20" placeholder="O czym chcesz nam powiedzieć?"></textarea>
            </div>

            <button name="submit" value="Submit" onclick="contactForm()" class="btn accept_btn">
                <span class="btn_icon">
                    <i class="fa-regular fa-paper-plane"></i>
                </span>
                <span class="btn_text">Wyślij</span>
            </button>
        </form>

        
    </section>

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