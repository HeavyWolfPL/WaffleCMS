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
        if (isset($_POST['likePost']) && $_POST['likePost'] == 'true' && isset($_POST['post_id']) && isset($_POST['user_id'])) {
            likePost($database, $_POST['post_id'], $_POST['user_id']);

            echo sendAlert('success', 'Polubiono post.');
        } else if (isset($_POST['hidePost']) && $_POST['hidePost'] == 'true' && isset($_POST['post_id'])) {
            hidePost($database, $_POST['post_id']);

            echo sendAlert('success', 'Ukryto post.');
        } else if (isset($_POST['removePost']) && $_POST['removePost'] == 'true' && isset($_POST['post_id'])) {
            removePost($database, $_POST['post_id']);

            echo sendAlert('success', 'Usunięto post.');
        } else if (isset($_POST['publishPost']) && $_POST['publishPost'] == 'true' && isset($_POST['post_id'])) {
            publishPost($database, $_POST['post_id']);

            echo sendAlert('success', 'Opublikowano post.');
        }
    }
?>

<div class="mainContainer">
    <section id="posts" class="posts">
        <article id="postFilters" class="postFilters">
            <div class="btns-horizontal flex-space-around">
                <button class="btn link" id="createPost" onclick="createPost()">
                    <span class="btn_icon">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                    <span class='btn_text'>Dodaj post</span>
                </button>
            </div>
        </article>

        <article id="postList" class="postList">
            <?php
                if (isset($_GET['post_id']) && !empty($_GET['post_id'])) {
                    try {
                        $comment = getPost($database, $_GET['post_id'], $_SESSION['user']['id']);
                    } catch (Exception $e) {
                        echo sendAlert('error', $e->getMessage());
                    }

                    if (!isset($comment) || empty($comment) || $comment == null) {
                        echo "<p>Nie znaleziono postu.</p>";
                    } else {
                        $class = "";

                        // Jeśli user jest administratorem, wyświetl posty, a następnie opcjonalne opcje ich zatwierdzenia.
                        // Jeśli nie jest, nie wyświetlaj posta, chyba że jest jego autorem.
                        if ($comment['is_published'] == 0) {
                            if (!$is_staff || $comment['author'] != $_SESSION['user']['id']) {
                                echo "<p>Nie znaleziono postu.</p>";
                                return;
                            }                             
                            $class = "unpublished";
                        }

                        echo "<div class='post {$class}' data-id='{$comment['id']}'>";
                            echo "<div class='postHeader'>";
                                echo "<div class='postHeaderLeft'>";
                                    echo "<img src='" . getUserAvatar($database, $comment['author']) . "' alt='Avatar' class='avatar'>";
                                    echo "<div class='postHeaderLeftText'>";
                                        echo "<p class='postHeaderLeftTextName'>" . getUserName($database, $comment['author']) . "</p>";
                                        echo "<p class='postHeaderLeftTextDate'>{$comment['updated_at']}</p>";
                                    echo "</div>"; // postHeaderLeftText
                                echo "</div>"; // postHeaderLeft
                                echo "<div class='postHeaderTitle'>";
                                    echo "<h4 class='postHeaderTitleText'><a href='" . getPostLink($comment['id'], $site_url) . "'>{$comment['title']}</a></h4>";
                                echo "</div>"; // postHeaderTitle
                                echo "<div class='postHeaderRight dropdown'>
                                        <i class='fa-solid fa-ellipsis-v dropdown-toggle' onclick='toggleDropdown(\"postModeration-{$comment['id']}\")'></i>
                                            <div id='postModeration-{$comment['id']}' class='dropdown-content'>";
                                            if ($is_staff) {
                                                if ($comment['is_published'] == 0) {
                                                    echo "<p class='dropdown-item' data-action='publish' onclick='publishPost({$comment['id']})'>Opublikuj</p>";
                                                    echo "<p class='dropdown-item' style='display: none;' data-action='hide' onclick='hidePost({$comment['id']})'>Ukryj</p>";
                                                } else {
                                                    echo "<p class='dropdown-item' style='display: none;' data-action='publish' onclick='publishPost({$comment['id']})'>Opublikuj</p>";
                                                    echo "<p class='dropdown-item' data-action='hide' onclick='hidePost({$comment['id']})'>Ukryj</p>";
                                                }
                                                    echo "<p class='dropdown-item' data-action='remove' onclick='removePost({$comment['id']})'>Usuń</p>";
                                            }
                                                echo "<p class='dropdown-item' data-action='share' onclick='sharePost({$comment['id']})'>Udostępnij</p>";
                                            echo "</div>"; // postModeration
                                echo "</div>"; // postHeaderRight
                            echo "</div>"; // postHeader
                            echo "<div class='postContent'>";
                                echo "<p class='postContentText'>{$comment['content']}</p>";
                                echo "<div class='postContentImage'>";
                                    if ($comment['images'] != null && $comment['images'] != "" && $comment['images'] != "[]") {
                                        $comment['images'] = $comment['images'][0]; // WORKAROUND
                                        echo "<img src='{$comment['images']['image_path']}' alt='{$comment['images']['image_name']}'>";
                                    }
                                echo "</div>"; // postContentImage
                            echo "</div>"; // postContent
                            echo "<div class='postFooter'>";
                            if ($comment['liked']) {
                                echo "<div class='postFooterLeft active' id='likes-container' onclick='likePost(" . $comment['id'] . ", " . $_SESSION['user']['id'] . ")'>";
                            } else {
                                echo "<div class='postFooterLeft' id='likes-container' onclick='likePost(" . $comment['id'] . ", " . $_SESSION['user']['id'] . ")'>";
                            }
                                    echo "<i class='fa-solid fa-heart'></i>";
                                    echo "<p class='postFooterLeftText'>{$comment['likes']}</p>";
                                echo "</div>"; // postFooterLeft
                                echo "<div class='postFooterRight' id='comment-count-container' onclick='goToPost(" . $comment['id'] . ")'>";
                                    echo "<i class='fa-solid fa-comment'></i>";
                                    echo "<p class='postFooterRightText'>{$comment['comments']}</p>";
                                echo "</div>"; // postFooterRight
                            echo "</div>"; // postFooter


                            echo "<hr class='marg-bottom-1em'>";
                            echo "<div class='comments'>";
                                echo "<div class='commentsForm'>";
                                    echo "<div class='commentsFormHeader'>";
                                        echo "<h4 class='commentsFormHeaderTitle'>Dodaj komentarz</h4>";
                                    echo "</div>"; // commentsFormHeader
                                    echo "<div class='commentsFormContent input-container'>";
                                        echo "<textarea class='commentsFormContentTextarea' id='commentContentInput' placeholder='Treść komentarza'></textarea>";
                                        echo "<div class='commentsFormContentBtns'>";
                                            echo "<button class='btn link' id='addComment' onclick='addComment({$comment['id']}, {$_SESSION['user']['id']})'>";
                                                echo "<span class='btn_icon'>";
                                                    echo "<i class='fa-solid fa-plus'></i>";
                                                echo "</span>";
                                                echo "<span class='btn_text'>Dodaj komentarz</span>";
                                            echo "</button>";
                                        echo "</div>"; // commentsFormContentBtns
                                    echo "</div>"; // commentsFormContent
                                    
                            if (1 == 1) { // DEBUG as comments not implemented yet
                            // if (isset($post['comments']) && $post['comments'] > 0) {
                                echo "<div class='commentsHeader'>";
                                    echo "<h4 class='commentsHeaderTitle'>Komentarze</h4>";
                                echo "</div>";
                                include __DIR__ . "/comments.php";
                            }
                            echo "</div>"; // comments

                        echo "</div>"; // post
                    }
                } else {
                    echo "<p>Nie znaleziono postu.</p>";
                }
            ?>
        </article>

    </section>
</div>