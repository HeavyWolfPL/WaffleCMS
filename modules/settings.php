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

<div id="settings" class="popup">
    <div id='flipCardSettings' class='flip-card'>
        <div class='flip-card-inner'>
            <div class='flip-card-front'> <!-- Preferencje -->
                <section class='form-container input-container'>
                    <h3 class='marg-bottom-1em'>Preferencje</h3>

                    <label>Układ daty</label>
                    <input type="text" id="dateFormat" name="dateFormat" placeholder="YYYY.MM.DD" disabled> 

                    <label>Motyw</label>
                    <?php 
                        if (isset($_COOKIE['color_mode']) && $_COOKIE['color_mode'] == 'light') {
                            echo '<button class="btn no_text_btn link" id="updateTheme" onclick="updateTheme(this)" data-current_theme="dark">
                                <span class="btn_icon">
                                    <i class="fa-solid fa-sun"></i>
                                </span>
                                <span class="btn_text">Jasny</span>
                            </button>';
                        } else {
                            echo '<button class="btn no_text_btn link" id="updateTheme" onclick="updateTheme(this)" data-current_theme="light">
                                    <span class="btn_icon">
                                        <i class="fa-solid fa-moon"></i>
                                    </span>
                                    <span class="btn_text">Ciemny</span>
                                </button>';
                        }
                    ?>   
                </section>
            </div>
            <div class='flip-card-back'> <!-- Account & Security -->
                <section class='form-container input-container'>
                    <h3 class='marg-bottom-1em'>Konto</h3>

                    <label>Nazwa użytkownika</label>
                    <input type="text" id="name" name="name" placeholder="<?php echo $_SESSION['user']['username'] ?>">

                    <label>Hasło</label>
                    <div class="input-container password-container">
                        <input type="password" id="password" name="password" placeholder="Hasło">
                        <i class="fas fa-eye" id="password-icon" onclick="togglePassword('password', 'password-icon')"></i>
                    </div>

                    <label>Avatar</label>
                    <img src="<?php echo $_SESSION['user']['avatar']?>" alt="Avatar" class="avatar">
                    <span class='input-container'>
                        <input type='file' id='avatarFile' accept='image/*' capture='user' style='width: 70%'>
                    </span>
                </section>
            </div>
        </div>
        <button id='saveSettingsBtn' class='btn' onclick="saveSettings()">
            <span class='btn_icon'>
                <i class="fa-regular fa-floppy-disk"></i>
            </span>
            <span class='btn_text'>Zapisz</span>
        </button><br>
        <button id='flipCardBtn' class='btn' onclick="flipCard('flipCardSettings', ['Preferencje', 'Konto'])">
            <span class='btn_icon'>
                <i class='fa-solid fa-rotate'></i>
            </span>
            <span class='btn_text'>Konto</span>
        </button>
    </div>
</div>