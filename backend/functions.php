<?php

/**
 * Redirects the user to the specified URL.
 * @param string $url The URL to redirect to.
 *
 * @return void
 */
function redirect($url)
{
    if (!headers_sent())
    {
        header('Location: '.$url);
        exit;
    }
    else
    {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
        exit;
    }
}

/**
 * Creates an alert with the specified type and message, and adds it to the alert queue.
 * @param string $type The type of alert:
 *                     - success
 *                     - info
 *                     - warning
 *                     - error
 * @param string $message The message to display in the alert.
 *
 * @return string Returns the alert as a ready to be echoed JavaScript string.
 */
function sendAlert($type, $message) {
	$alert = "<script>sendAlert('" . $type . "', '" . $message . "');</script>";
	return $alert;
}


/**
 * Retrieves posts from the database based on the specified sorting and search criteria.
 *
 * @param mysqli $conn The database connection object.
 * @param string $sort The sorting criteria for the posts. Default is 'newest'.
 * @param string $search The search keyword to filter the posts. Default is an empty string.
 * @return array The array of posts retrieved from the database.
 * @throws Exception If there is an error retrieving posts from the database.
 */
function getPosts($conn, $sort = 'newest', $search = '', $user_id = null) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'search' => $search,
            'user_id' => $user_id
        )
    );

    switch($sort) {
        case "newest":
            $sort = "updated_at DESC";
            break;
        case "oldest":
            $sort = "updated_at ASC";
            break;
        case "popular":
            $sort = "likes DESC";
            break;
        default:
            $sort = "updated_at DESC";
            break;
    }

    if ($variables['search'] != '') {
        $sql = "SELECT * FROM posts WHERE title LIKE '%" . $variables['search'] . "%' OR content LIKE '%" . $variables['search'] . "%' ORDER BY " . $sort . "";
    } else {
        $sql = "SELECT * FROM posts ORDER BY " . $sort . "";
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $posts = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $comments = getComments($conn, $row['id']);
            $row['comments'] = count($comments);

            $images = getImages($conn, $row['id']);
            $row['images'] = $images;

            $likes = getLikesAndIfLiked($conn, $row['id']);
            $row['likes'] = count($likes['list']) ? count($likes['list']) : 0;
            $row['liked'] = userLikedPost($conn, $row['id'], $variables['user_id']) ? true : false;

            $posts[] = $row;
        }
        return $posts;
    } else if ($result->num_rows == 0) {
        return [];
    } else {
        logError("Błąd podczas pobierania postów z bazy danych: " . mysqli_error($conn), 'error', 'getPosts()', 'Database');
        throw new Exception("Błąd podczas pobierania postów z bazy danych.");
    }
}

function getPost($conn, $post_id, $user_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id,
            'user_id' => $user_id
        )
    );

    $sql = "SELECT * FROM posts WHERE id = " . $variables['post_id'] . "";

    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $post = $result->fetch_assoc();

        $comments = getComments($conn, $post['id']);
        $post['comments'] = count($comments);

        $images = getImages($conn, $post['id']);
        $post['images'] = $images;

        $likes = getLikesAndIfLiked($conn, $post['id']);
        $post['likes'] = count($likes['list']) ? count($likes['list']) : 0;
        $post['liked'] = userLikedPost($conn, $post_id, $user_id) ? true : false;

        return $post;
    } else if ($result->num_rows == 0) {
        throw new Exception("Nie znaleziono postu o id: " . $variables['post_id']);
    } else {
        logError("Błąd podczas pobierania postów z bazy danych: " . mysqli_error($conn), 'error', 'getPosts()', 'Database');
        throw new Exception("Błąd podczas pobierania postów z bazy danych.");
    }
}

function getLikesAndIfLiked($conn, $post_id, $user_id = null) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id,
            'user_id' => $user_id
        )
    );

    $sql = "SELECT * FROM likes WHERE post_id = " . $variables['post_id'] . "";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $likes = array();
        $liked = false;
        while ($row = mysqli_fetch_assoc($result)) {
            $likes[] = $row;
        }

        return array(
            'list' => $likes,
        );
    } else if ($result->num_rows == 0) {
        return array(
            'list' => [],
        );
    } else {
        logError("Błąd podczas pobierania polubień z bazy danych: " . mysqli_error($conn), 'error', 'getLikesAndIfLiked()', 'Database');
        throw new Exception("Błąd podczas pobierania polubień z bazy danych.");
    }
}

function userLikedPost($conn, $post_id, $user_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id,
            'user_id' => $user_id
        )
    );

    $sql = "SELECT * FROM likes WHERE post_id = " . $variables['post_id'] . " AND user_id = " . $variables['user_id'] . "";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return true;
    } else if ($result->num_rows == 0) {
        return false;
    } else {
        logError("Błąd podczas pobierania polubienia z bazy danych: " . mysqli_error($conn), 'error', 'userLikedPost()', 'Database');
        throw new Exception("Błąd podczas pobierania polubienia z bazy danych.");
    }
}



function likePost($conn, $post_id, $user_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id,
            'user_id' => $user_id
        )
    );

    if (userLikedPost($conn, $variables['post_id'], $variables['user_id'])) {
        $sql = "DELETE FROM likes WHERE post_id = " . $variables['post_id'] . " AND user_id = " . $variables['user_id'] . "";
        if ($conn->query($sql) !== TRUE) {
            logError("Błąd podczas usuwania polubienia z bazy danych: " . mysqli_error($conn), 'error', 'likePost()', 'Database');
            throw new Exception("Błąd podczas usuwania polubienia z bazy danych.");
        }

        $sql = "UPDATE posts SET likes = likes - 1 WHERE id = " . $variables['post_id'] . "";

        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            logError("Błąd podczas aktualizacji liczby polubień w bazie danych: " . mysqli_error($conn), 'error', 'likePost()', 'Database');
            throw new Exception("Błąd podczas aktualizacji liczby polubień w bazie danych.");
        }
    }

    // User didn't like the post yet
    $sql = "INSERT INTO likes (post_id, user_id) VALUES (" . $variables['post_id'] . ", " . $variables['user_id'] . ")";
    if ($conn->query($sql) !== TRUE) {
        logError("Błąd podczas dodawania polubienia do bazy danych: " . mysqli_error($conn), 'error', 'likePost()', 'Database');
        throw new Exception("Błąd podczas dodawania polubienia do bazy danych.");
    }

    $sql = "UPDATE posts SET likes = likes + 1 WHERE id = " . $variables['post_id'] . "";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        logError("Błąd podczas aktualizacji liczby polubień w bazie danych: " . mysqli_error($conn), 'error', 'likePost()', 'Database');
        throw new Exception("Błąd podczas aktualizacji liczby polubień w bazie danych.");
    }
}

function publishPost($conn, $post_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id
        )
    );

    $sql = "UPDATE posts SET is_published = 1 WHERE id = " . $variables['post_id'] . "";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        logError("Błąd podczas publikowania postu nr. " . $variables['post_id'] . " w bazie danych: " . mysqli_error($conn), 'error', 'publishPost()', 'Database');
        throw new Exception("Błąd podczas publikowania postu nr. " . $variables['post_id'] . " w bazie danych.");
    }
}

function hidePost($conn, $post_id) {
    logError("Ukryto post nr. " . $post_id . ".", 'info', 'hidePost()', 'Database');
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id
        )
    );

    $sql = "UPDATE posts SET is_published = 0 WHERE id = " . $variables['post_id'] . "";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        logError("Błąd podczas ukrywania postu nr. " . $variables['post_id'] . " w bazie danych: " . mysqli_error($conn), 'error', 'hidePost()', 'Database');
        throw new Exception("Błąd podczas ukrywania postu nr. " . $variables['post_id'] . " w bazie danych.");
    }
}

/**
 * Removes a post from the database along with its associated images, comments, and likes.
 *
 * @param mysqli $conn The database connection object.
 * @param int $post_id The ID of the post to be removed.
 * @return bool Returns true if the post is successfully removed, otherwise throws an exception.
 * @throws Exception Throws an exception if there is an error while removing the post or its associated data.
 */
function removePost($conn, $post_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id
        )
    );

    $sql = "DELETE FROM posts WHERE id = " . $variables['post_id'] . "";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas usuwania postu nr. " . $variables['post_id'] . " z bazy danych: " . mysqli_error($conn), 'error', 'removePost()', 'Database');
        throw new Exception("Błąd podczas usuwania postu nr. " . $variables['post_id'] . " z bazy danych.");
    }

    $sql = "DELETE FROM post_images WHERE post_id = " . $variables['post_id'] . "";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas usuwania obrazów postu nr. " . $variables['post_id'] . " z bazy danych: " . mysqli_error($conn), 'error', 'removePost()', 'Database');
        throw new Exception("Błąd podczas usuwania obrazów postu nr. " . $variables['post_id'] . " z bazy danych.");
    }

    $sql = "DELETE FROM comments WHERE post_id = " . $variables['post_id'] . "";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas usuwania komentarzy postu nr. " . $variables['post_id'] . " z bazy danych: " . mysqli_error($conn), 'error', 'removePost()', 'Database');
        throw new Exception("Błąd podczas usuwania komentarzy postu nr. " . $variables['post_id'] . " z bazy danych.");
    }

    $sql = "DELETE FROM likes WHERE post_id = " . $variables['post_id'] . "";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas usuwania polubień postu nr. " . $variables['post_id'] . " z bazy danych: " . mysqli_error($conn), 'error', 'removePost()', 'Database');
        throw new Exception("Błąd podczas usuwania polubień postu nr. " . $variables['post_id'] . " z bazy danych.");
    }

    return true;
}

/**
 * Creates a new post with the provided information.
 *
 * @param mysqli $conn The database connection object.
 * @param int $user_id The ID of the user creating the post.
 * @param string $title The title of the post.
 * @param string $content The content of the post.
 * @param array $file The uploaded file information.
 * @return int|false The ID of the newly created post or false if an error occurred.
 * @throws Exception If an error occurs while adding the post or the image to the database.
 */
function addPost($conn, $user_id, $title, $content, $file) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'user_id' => $user_id,
            'title' => $title,
            'content' => $content,
        )
    );

    if ($variables['title'] == '' || $variables['title'] == null) {
        http_response_code(400);
        echo sendAlert('error', 'Tytuł nie może być pusty.');
        throw new Exception("Tytuł nie może być pusty.");
    }

    if ($variables['content'] == '' || $variables['content'] == null) {
        http_response_code(400);
        echo sendAlert('error', 'Treść nie może być pusta.');
        throw new Exception("Treść nie może być pusta.");
    }

    if ($variables['user_id'] == '' || $variables['user_id'] == null) {
        http_response_code(400);
        echo sendAlert('error', 'Nieprawidłowy użytkownik.');
        throw new Exception("Nieprawidłowy użytkownik.");
    }

    // Check if the file was uploaded without errors
    if ($file != null && $file['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($file['name']);
        $file_tmp_name = $file['tmp_name'];
        $file_size = $file['size'];
        $file_type = $file['type'];

        $image_name = $file_name;
        $image_name = explode('.', $image_name)[0];
        $variables['image_name'] = sanitizeSQL(
            $conn,
            array(
                'image_name' => $image_name
            )
        )['image_name'];

        if ($file_size > 5242880) { // 5MB
            http_response_code(400);
            echo sendAlert('error', 'Plik jest za duży. Limit wynosi 5MB.');
            throw new Exception("Plik jest za duży. Limit wynosi 5MB.");
        } else {
            $allowed_types = array('image/jpeg', 'image/png', 'video/mp4', 'video/webm');
            if (!in_array($file_type, $allowed_types)) {
                http_response_code(400);
                echo sendAlert('error', 'Nieprawidłowy typ pliku. Dozwolone typy to JPEG, PNG, MP4 i WebM.');
                logError('Error uploading file: ' . $file_name . " (" . $file_type . ", " . $file_size . ")", 'error', 'createPost()', 'Website');
                throw new Exception("Nieprawidłowy typ pliku. Dozwolone typy to JPEG, PNG, MP4 i WebM.");
            } else {
                $unique_file_name = uniqid('file_', true) . '_' . $file_name;
                $target_directory = dirname(__DIR__) . '/uploads/';

                $target_file = $target_directory . $unique_file_name;
                if (move_uploaded_file($file_tmp_name, $target_file)) {
                    $variables['file_path'] = "uploads/" . $unique_file_name;
                } else {
                    logError('Error uploading file: ' . $target_file . " (" . $file_type . ", " . $file_size . ")", 'error', 'createPost()', 'Website');
                    http_response_code(400);
                    echo sendAlert('error', 'Wystąpił błąd podczas przesyłania pliku.');
                    throw new Exception("Błąd podczas przesyłania pliku.");
                }
            }
        }
    } else if ($file != null || (is_array($file) && $file['error'] !== UPLOAD_ERR_NO_FILE)) {
        logError('Error uploading file: ' . $file['error'], 'error', 'createPost()', 'Website');
        http_response_code(400);
        echo sendAlert('error', 'Wystąpił błąd podczas przesyłania pliku.');
        return false;
    }

    // Add new post
    $sql = "INSERT INTO posts (title, content, author) VALUES ('" . $variables['title'] . "', '" . $variables['content'] . "', " . $variables['user_id'] . ")";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas dodawania postu do bazy danych: " . mysqli_error($conn), 'error', 'createPost()', 'Database');
        throw new Exception("Błąd podczas dodawania postu do bazy danych.");
    }

    // Get post ID
    $sql = "SELECT id FROM posts WHERE title = '" . $variables['title'] . "' AND content = '" . $variables['content'] . "' AND author = " . $variables['user_id'] . " ORDER BY created_at DESC LIMIT 1";
    $result = $conn->query($sql);

    // Add image to post
    if ($file != null && $file['error'] === UPLOAD_ERR_OK) {
        $sql = "INSERT INTO post_images (post_id, author_id, image_name, image_path) VALUES (" . $result->fetch_assoc()['id'] . ", " . $variables['user_id'] . ", '" . $variables['image_name'] . "', '" . $variables['file_path'] . "')";
        if ($conn->query($sql) === TRUE) {
            return $result->fetch_assoc()['id'];
        } else {
            logError("Błąd podczas dodawania obrazu do postu do bazy danych: " . mysqli_error($conn), 'error', 'createPost()', 'Database');
            throw new Exception("Błąd podczas dodawania obrazu do postu do bazy danych.");
        }
    } else {
        return $result->fetch_assoc()['id'];

    }

}



function getPostLink($post_id, $site_url) {
    return $site_url . '?mode=post&post_id=' . $post_id;
}



/**
 * Retrieves comments for a specific post from the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $post_id The ID of the post.
 * @return array The array of comments for the post.
 * @throws Exception If there is an error retrieving the comments.
 */
function getComments($conn, $post_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id
        )
    );

    if ($variables['post_id'] != '' || $variables['post_id'] != null) {
        $sql = "SELECT * FROM comments WHERE post_id = " . $variables['post_id'] . " ORDER BY created_at ASC";
    } else {
        return [];
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $comments = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $comments[] = $row;
        }
        return $comments;
    } else if ($result->num_rows == 0) {
        return [];
    } else {
        logError("Błąd podczas pobierania komentarzy dla " . $post_id . " z bazy danych: " . mysqli_error($conn), 'error', 'getComments()', 'Database');
        throw new Exception("Błąd podczas pobierania komentarzy dla postu  nr. " . $post_id . " z bazy danych.");
    }
}

/**
 * Adds a comment to the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $post_id The ID of the post the comment belongs to.
 * @param int $author_id The ID of the comment author.
 * @param string $content The content of the comment.
 * @return bool Returns true if the comment is successfully added, otherwise throws an exception.
 * @throws Exception Throws an exception if the post ID, author ID, or content is invalid.
 */
function addComment($conn, $post_id, $author_id, $content) {
    logError('addComment executed', 'error', 'addComment()', 'Website');
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id,
            'author_id' => $author_id,
            'content' => $content
        )
    );

    if ($variables['post_id'] == '' || $variables['post_id'] == null) {
        http_response_code(400);
        echo sendAlert('error', 'Nieprawidłowy post.');
        logError('Nieprawidłowy post.', 'error', 'addComment()', 'Website');
        throw new Exception("Nieprawidłowy post.");
    }

    if ($variables['author_id'] == '' || $variables['author_id'] == null) {
        http_response_code(400);
        echo sendAlert('error', 'Nieprawidłowy użytkownik.');
        logError('Nieprawidłowy użytkownik.', 'error', 'addComment()', 'Website');
        throw new Exception("Nieprawidłowy użytkownik.");
    }

    if ($variables['content'] == '' || $variables['content'] == null) {
        http_response_code(400);
        echo sendAlert('error', 'Treść nie może być pusta.');
        logError('Treść nie może być pusta.', 'error', 'addComment()', 'Website');
        throw new Exception("Treść nie może być pusta.");
    }

    $sql = "INSERT INTO comments (post_id, author_id, content) VALUES (" . $variables['post_id'] . ", " . $variables['author_id'] . ", '" . $variables['content'] . "')";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas dodawania komentarza do bazy danych: " . mysqli_error($conn), 'error', 'addComment()', 'Database');
        throw new Exception("Błąd podczas dodawania komentarza do bazy danych. [SQL-Fail]");
    }

    return true;
}

/**
 * Removes a comment from the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $comment_id The ID of the comment to be removed.
 * @return bool Returns true if the comment is successfully removed, otherwise throws an exception.
 * @throws Exception Throws an exception if there is an error while removing the comment.
 */
function removeComment($conn, $comment_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'comment_id' => $comment_id
        )
    );

    $sql = "DELETE FROM comments WHERE id = " . $variables['comment_id'] . "";
    if ($conn->query($sql) != TRUE) {
        logError("Błąd podczas usuwania komentarza nr. " . $variables['comment_id'] . " z bazy danych: " . mysqli_error($conn), 'error', 'removeComment()', 'Database');
        throw new Exception("Błąd podczas usuwania komentarza nr. " . $variables['comment_id'] . " z bazy danych.");
    }

    return true;
}

/**
 * Publishes a comment.
 *
 * @param mysqli $conn The database connection object.
 * @param int $comment_id The ID of the comment to be published.
 * @return bool Returns true if the comment is successfully published, otherwise throws an exception.
 * @throws Exception Throws an exception if there is an error while publishing the comment.
 */
function publishComment($conn, $comment_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'comment_id' => $comment_id
        )
    );

    $sql = "UPDATE comments SET is_approved = 1 WHERE id = " . $variables['comment_id'] . "";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        logError("Błąd podczas publikowania komentarza nr. " . $variables['comment_id'] . " w bazie danych: " . mysqli_error($conn), 'error', 'publishComment()', 'Database');
        throw new Exception("Błąd podczas publikowania komentarza nr. " . $variables['comment_id'] . " w bazie danych.");
    }
}

/**
 * Hides a comment.
 *
 * @param mysqli $conn The database connection object.
 * @param int $comment_id The ID of the comment to be hidden.
 * @return bool Returns true if the comment is successfully hidden, otherwise throws an exception.
 * @throws Exception Throws an exception if there is an error while hiding the comment.
 */
function hideComment($conn, $comment_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'comment_id' => $comment_id
        )
    );

    $sql = "UPDATE comments SET is_approved = 0 WHERE id = " . $variables['comment_id'] . "";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        logError("Błąd podczas ukrywania komentarza nr. " . $variables['comment_id'] . " w bazie danych: " . mysqli_error($conn), 'error', 'hideComment()', 'Database');
        throw new Exception("Błąd podczas ukrywania komentarza nr. " . $variables['comment_id'] . " w bazie danych.");
    }
}



/**
 * Retrieves images associated with a specific post from the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $post_id The ID of the post.
 * @return array An array of image data.
 * @throws Exception If there is an error retrieving the images.
 */
function getImages($conn, $post_id) {
    $variables = sanitizeSQL(
        $conn,
        array(
            'post_id' => $post_id
        )
    );

    if ($variables['post_id'] != '' || $variables['post_id'] != null) {
        $sql = "SELECT * FROM post_images WHERE post_id = " . $variables['post_id'] . " ORDER BY id ASC";
    } else {
        return [];
    }

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $images = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $images[] = $row;
        }
        return $images;
    } else if ($result->num_rows == 0) {
        return [];
    } else {
        logError("Błąd podczas pobierania obrazów dla " . $post_id . " z bazy danych: " . mysqli_error($conn), 'error', 'getPosts()', 'Database');
        throw new Exception("Błąd podczas pobierania obrazów dla postu  nr. " . $post_id . " z bazy danych.");
    }
}