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

<?php
    if (!isset($database)) $database = connectToDB($database_info['host'], $database_info['username'], $database_info['password'], $database_info['database']);
    
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
        if (isset($_POST['addComment']) && $_POST['addComment'] == 'true' && isset($_POST['post_id']) && isset($_POST['commentContent']) && isset($_POST['user_id'])) {
            addComment($database, $_POST['post_id'], $_POST['user_id'], $_POST['commentContent']);

            echo sendAlert('success', 'Dodano komentarz.');
        } else if (isset($_POST['removeComment']) && $_POST['removeComment'] == 'true' && isset($_POST['comment_id'])) {
            removeComment($database, $_POST['comment_id']);

            echo sendAlert('success', 'Usunięto komentarz.');
        } else if (isset($_POST['publishComment']) && $_POST['publishComment'] == 'true' && isset($_POST['comment_id'])) {
            publishComment($database, $_POST['comment_id']);

            echo sendAlert('success', 'Opublikowano komentarz.');
        } else if (isset($_POST['hideComment']) && $_POST['hideComment'] == 'true' && isset($_POST['comment_id'])) {
            hideComment($database, $_POST['comment_id']);

            echo sendAlert('success', 'Ukryto komentarz.');
        } else {
            http_response_code(400);
            logError('Request POST but no data. POST:' . var_export($_POST, true), 'error', 'addComment()', 'Website');
        }
    }
?>

<article id="commentsList" class="commentsList">
    <?php
        if (isset($_GET['post_id']) && !empty($_GET['post_id'])) {            
            try {
                $comments = getComments($database, $_GET['post_id']);
            } catch (Exception $e) {
                $comments = array();
                echo "<p>Wystąpił problem przy wyszukiwaniu komentarzy .</p>";
                echo sendAlert('error', $e->getMessage());
                throw new Exception($e->getMessage());
            }

            if (empty($comments)) {
                echo "<p>Nie znaleziono komentarzy.</p>";
                return false;
            }

            foreach ($comments as $comment) {
                $class = "";

                // Jeśli user jest administratorem, wyświetl komentarz, a następnie opcjonalne opcje ich zatwierdzenia.
                // Jeśli nie jest, nie wyświetlaj komentarza, chyba że jest jego autorem.
                if ($comment['is_approved'] == 0) {
                    if (!$is_staff) continue;
                    if ($comment['author_id'] != $_SESSION['user']['id']) continue;
                    
                    $class = "unpublished";
                }

                echo "<div class='post comment {$class}' data-comment_id='{$comment['id']}'>"; // For comments we can basically use the same classes as for posts
                    echo "<div class='postHeader'>";
                        echo "<div class='postHeaderLeft'>";
                            echo "<img src='" . getUserAvatar($database, $comment['author_id']) . "' alt='Avatar' class='avatar'>";
                            echo "<div class='postHeaderLeftText'>";
                                echo "<p class='postHeaderLeftTextName'>" . getUserName($database, $comment['author_id']) . "</p>";
                                echo "<p class='postHeaderLeftTextDate'>{$comment['created_at']}</p>";
                            echo "</div>"; // postHeaderLeftText
                        echo "</div>"; // postHeaderLeft
                        echo "<div class='postHeaderTitle'>";
                        echo "</div>"; // postHeaderTitle
                        echo "<div class='postHeaderRight dropdown'>
                                <i class='fa-solid fa-ellipsis-v dropdown-toggle' onclick='toggleDropdown(\"postModeration-{$comment['id']}\")'></i>
                                    <div id='postModeration-{$comment['id']}' class='dropdown-content'>";
                                    if ($is_staff) {
                                        if ($comment['is_approved'] == 0) {
                                            echo "<p class='dropdown-item' data-comment_action='publish' onclick='publishComment({$comment['id']})'>Opublikuj</p>";
                                            echo "<p class='dropdown-item' style='display: none;' data-comment_action='hide' onclick='hideComment({$comment['id']})'>Ukryj</p>";
                                        } else {
                                            echo "<p class='dropdown-item' style='display: none;' data-comment_action='publish' onclick='publishComment({$comment['id']})'>Opublikuj</p>";
                                            echo "<p class='dropdown-item' data-comment_action='hide' onclick='hideComment({$comment['id']})'>Ukryj</p>";
                                        }
                                            echo "<p class='dropdown-item' data-comment_action='remove' onclick='removeComment({$comment['id']})'>Usuń</p>";
                                    }
                                    echo "</div>"; // postModeration
                        echo "</div>"; // postHeaderRight
                    echo "</div>"; // postHeader
                    echo "<div class='postContent'>";
                        echo "<p class='postContentText'>{$comment['content']}</p>";
                    echo "</div>"; // postContent
                echo "</div>"; // post
            }
        } else {
            echo "<p>Nie znaleziono postu.</p>";
        }
    ?>
</article>