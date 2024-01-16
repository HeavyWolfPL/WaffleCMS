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
    if (!isset($_SESSION['user']) || empty($_SESSION['user']) || !isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
        redirect('index.php');
    }
?>

<ul id="sidebar" class="sidebar">
    <li><a href="index.php">
        <i class="fa-solid fa-home"></i>
        <p>Strona Główna</p>
    </a></li>
    <li><a href="contact.php">
        <i class="fa-solid fa-phone"></i>
        <p>Kontakt</p>
    </a></li>

    <hr>
    <li onclick="togglePopup('settings', 'flex')"><a>
        <i class="fa-solid fa-cog"></i>
        <p>Ustawienia</p>
    </a></li>
    <li><a>
        <img src="<?php echo $_SESSION['user']['avatar']?>" alt="Avatar" class="avatar">
        <p><?php echo $_SESSION['user']['username'] ?></p>
    </a></li>
</ul>