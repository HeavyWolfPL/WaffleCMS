<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 'On');
ini_set('error_log', '../logs/PHP.log');
error_reporting(E_ALL);

require_once dirname(__DIR__) . "/backend/functions.php";
require_once dirname(__DIR__) . "/backend/config.php";
require_once dirname(__DIR__) . "/backend/database.php";


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<script src="frontend/posts.js" defer></script>

<?php
    $database = connectToDB($database_info['host'], $database_info['username'], $database_info['password'], $database_info['database']);
    if (!isset($_SESSION['user']) || empty($_SESSION['user']) || !isset($_SESSION['user']['id']) || empty($_SESSION['user']['id'])) {
        redirect('index.php');
    }

    $is_staff = false;
    if (isset($_SESSION['user']['access']) && $_SESSION['user']['access'] == 'Staff') {
        $is_staff = true;
    }
?>

<?php
    include_once __DIR__ . "/sidebar.php";
?>

<?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] == 'true') {
        if (isset($_POST['addPost']) && $_POST['addPost'] == 'true' && isset($_POST['postTitle']) && isset($_POST['postContent'])) {

            if (!isset($_POST['postImageFile']) || $_POST['postImageFile'] == '') {
                $_POST['postImageFile'] = null;
            }

            $postID = addPost($database, $_SESSION['user']['id'], $_POST['postTitle'], $_POST['postContent'], $_FILES['postImageFile']);

            echo "<p id='newPostID'>$postID</p>";

            echo sendAlert('success', 'Dodano post.');
        } else {
            http_response_code(400);
            logError('Request POST but no data. POST:' . var_export($_POST, true), 'error', 'addPost()', 'Website');
        }
    }
?>

<div class="mainContainer">
    <section id="posts" class="posts">
        <article id="postList" class="postList">

            <?php
                echo "<div class='post'><form id='postForm' enctype='multipart/form-data'>";
                    echo "<div class='postHeader'>";
                        echo "<div class='postHeaderLeft'></div>"; // postHeaderLeft
                        echo "<div class='postHeaderTitle'>";
                            echo "<h4 class='postHeaderTitleText'>Dodaj post</h4>";
                            echo "<hr>";
                        echo "</div>"; // postHeaderTitle
                        echo "<div class='postHeaderRight'></div>"; // postHeaderRight
                    echo "</div>"; // postHeader
                    echo "<div class='postContent'>";
                        echo "<div class='postContentText input-container'>";
                            echo "<input type='text' placeholder='Tytuł' id='postTitle' class='postContentTextTitle'>";
                        echo "<div class='postContentText input-container'>";
                            echo "<textarea placeholder='Treść' id='postContent' class='postContentTextContent' rows='15' cols='80'></textarea>";
                        echo "</div>"; // postContentText
                        echo "<div class='postContentImage input-container'>";
                            echo "<input type='file' id='postImageFile' class='postContentImageFile' accept='image/*,video/mp4,video/webm' capture='user'";
                        echo "</div>"; // postContentImage
                    echo "</div>"; // postContent
                    echo "<div class='postFooter'>";
                        echo "<div class='btns-horizontal flex-space-around'>";
                            echo "<button class='btn' id='addPost'>";
                                echo "<span class='btn_icon'>";
                                    echo "<i class='fa-solid fa-plus'></i>";
                                echo "</span>";
                                echo "<span class='btn_text'>Dodaj post</span>";
                            echo "</button>";
                            echo "<button class='btn' id='cancelPost' onclick='previousPage()'>";
                                echo "<span class='btn_icon'>";
                                    echo "<i class='fa-solid fa-times'></i>";
                                echo "</span>";
                                echo "<span class='btn_text'>Anuluj</span>";
                            echo "</button>";
                        echo "</div>"; // btns-horizontal
                    echo "</div>"; // postFooter
                echo "</form></div>"; // post

                // As preventing event should be added also to form.
                echo "<script>";
                    echo "document.getElementById('postForm').addEventListener('submit', function(event) {";
                        echo "event.preventDefault();";
                        echo "addPost();";
                    echo "});";
                echo "</script>";
            ?>
        </article>

    </section>
</div>