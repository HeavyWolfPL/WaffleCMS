<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
ini_set('log_errors', 'On');
ini_set('error_log', '/logs/PHP.log');
error_reporting(E_ALL);


require_once __DIR__ . "/backend/functions.php";
require_once __DIR__ . "/backend/config.php";
require_once __DIR__ . "/backend/database.php";

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
    include_once "modules/sidebar.php";
?>

<div class="mainContainer">
    <section id="posts" class="posts">
        <article id="postFilters" class="postFilters">
            <div name="postSort" id="postSort">
                <?php 
                    if (isset($_GET['sort']) && !empty($_GET['sort']) && in_array($_GET['sort'], array("oldest", "newest", "popular"))) {
                        switch($_GET['sort']) {
                            case "newest":
                                $_temp = ["yes", "no", "no"];
                                break;
                            case "oldest":
                                $_temp = ["no", "yes", "no"];
                                break;
                            case "popular":
                                $_temp = ["no", "no", "yes"];
                                break;
                            default:
                                $_temp = ["yes", "no", "no"];
                                break;
                        }
                    } else {
                        $_temp = ["yes", "no", "no"];
                    }
                ?>
                <span data-sort="newest" onclick="postSort(this)" data-active="<?php echo $_temp[0] ?>">
                    Najnowsze
                </span>
                <span data-sort="oldest" onclick="postSort(this)" data-active=<?php echo $_temp[1] ?>>
                    Najstarsze
                </span>
                <span data-sort="popular" onclick="postSort(this)" data-active=<?php echo $_temp[2] ?>>
                    Najpopularniejsze
                </span>
            </div>
            <div class="btns-horizontal flex-space-around">
                <button class="btn link" id="createPost" onclick="createPost()">
                    <span class="btn_icon">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                    <span class='btn_text'>Dodaj post</span>
                </button>
                <span id="postSearch" class='btn search_btn' onclick="searchPosts('postSearchInput')">
                     <!-- The onkeydown event is used to prevent the space key (keyCode 32) from being entered in the input field. -->
                    <input type="text" placeholder="Wyszukaj..." class="btn_text btn_input" id='postSearchInput'>
                    <span class='btn_icon'>
                        <i class='fa-solid fa-search'></i>
                    </span>
                </span>
            </div>
        </article>

        <article id="postList" class="postList">

            <?php
                if (isset($_GET['sort']) && !empty($_GET['sort']) && in_array($_GET['sort'], array("oldest", "newest", "popular"))) {
                    $sort = $_GET['sort'];
                } else {
                    $sort = 'newest';
                }

                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = $_GET['search'];
                } else {
                    $search = '';
                }

                $comment = getPosts($database, $sort, $search, $_SESSION['user']['id']);


                if ($comment == []) {
                    echo "<p>Brak postów do wyświetlenia.</p>";
                } else {
                    foreach ($comment as $comment) {
                        $class = "";

                        // Jeśli user jest administratorem, wyświetl posty, a następnie opcjonalne opcje ich zatwierdzenia.
                        // Jeśli nie jest, nie wyświetlaj posta, chyba że jest jego autorem.
                        if ($comment['is_published'] == 0) {
                            if (!$is_staff) continue;
                            if ($comment['author'] != $_SESSION['user']['id']) continue;
                            
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
                        echo "</div>"; // post
                    }
                }
                
            ?>
        </article>

    </section>


</div>