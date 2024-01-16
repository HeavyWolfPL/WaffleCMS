<?php

/**
 * Connect to Database and eventually handle errors
 * 
 * @param string $db_host       Database Host
 * @param string $db_username   Database Username
 * @param string $db_password   Database Password
 * @param string $database      Database Name
 * 
 * @return mysqli Database Connection
 */
function connectToDB($db_host, $db_username, $db_password, $database) {
    $conn = new mysqli($db_host, $db_username, $db_password, $database);
    if ($conn->connect_error) {
        logError("Connection failed: " . $conn->connect_error);

        die("Connection failed: " . $conn->connect_error);
    } else if ($conn->connect_error == null) {
        // logError("Connected to database");
        return $conn;
    }
    return $conn;
}


/**
 * Close Database Connection
 * 
 * @param mysqli $conn  MySQLi Database Connection
 * 
 * @return none
 */
function closeDB ($conn) {
    $conn->close();
}

/**
 * Log error to file.
 *
 * @param string $error Error message
 * @param string $type Type of error
 *                     - Info
 *                     - Warning
 *                     - Error
 * @param string $function Function name
 * @param string $filetype File type
 *                         - Database,
 *                         - Website
 * @return bool Returns true if error was logged successfully, false if not
 */
function logError($error, $type = "error", $function = null, $filetype = "website") {
    # Append error to file with date
    if ($error == '' || $error == null || $error == false || $error == 'NULL') {
        return false;
    }

    if ($function == null) {
        $function = "";
    } else {
        $function = $function . ": ";
    }

    $error = "[" . $error . "] ";

    $type = strtoupper("[" . $type . "] ");

    if (!file_exists(dirname(__DIR__) . "/logs")) {
        try {
            mkdir(dirname(__DIR__) . "/logs", 0770, true);
        } catch (Exception $e) {
            return false;
        }
    }
    switch(strtolower($filetype)) {
        case "database":
            $path = dirname(__DIR__) . "/logs/Database.log";
            break;
        default:
            $path = dirname(__DIR__) . "/logs/Website.log";
            break;
    }

    try {
        $file = fopen($path, "a");
        fwrite($file, "[" . date("Y-m-d H:i:s") . "] " . $type . $function . $error . "\n");
        fclose($file);
    } catch (Exception $e) {
        return false;
    }
    return true;
}

/**
 * Make variables safe to use in SQL query.
 *
 * @param mysqli $conn  MySQLi Database Connection
 * @param array $variables Array containing variable(s)
 *                         - Indexed array without keys ('value', 'value')
 *                         - Associative array with keys ('key' => 'value')
 * 
 * @return array Array containing sanitized variable(s)
 */ 
function sanitizeSQL($conn, $variables) {
    if (is_array($variables)) {
        if (array_values($variables) === $variables) {
            // Indexed array without keys
            for ($i = 0; $i < count($variables); $i++) {
                $variables[$i] = mysqli_real_escape_string($conn, $variables[$i]);
            }
        } else {
            // Associative array with keys
            foreach ($variables as $key => $value) {
                $variables[$key] = mysqli_real_escape_string($conn, $value);
            }
        }
    }
    return $variables;
}

function createAccountInDB($conn, $login, $password) {

    if (checkIfUserExists($conn, $login)) {
        throw new Exception("User already exists.");
    }

    $password = hashPassword($password);
    if (!$password) return false;

    $variables = sanitizeSQL(
        $conn, 
        array(
            'login' => $login, 
        )
    );

    // Why is password not sanitized? 
    // Due to it being hashed, it's not possible to inject anything into it, 
    // plus the function may introduce some breaking slashes and other symbols.

    $sql = "INSERT INTO users (access, user_login, user_password, username) VALUES (0, '" . $variables['login'] . "', '" . $password . "', '" . $variables['login'] . "')";
    if ($conn->query($sql) !== TRUE) {
        logError(mysqli_error($conn), 'error', 'createAccountInDB', 'database');
        throw new Exception("Error creating account in database.");
    } else {
        return true;
    }
}

/**
 * Hash password.
 * 
 * @param string $password Password
 * 
 * @return string Hashed password
 * 
 * @throws Exception If password hashing failed
 */
function hashPassword($password) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, array('cost' => 14));
    if ($hashedPassword === false) {
        logError("Password hashing failed.", 'error', 'hashPassword', 'database');
        throw new Exception('Password hashing failed.');
    }
    return $hashedPassword;
}

/**
 * Verify if password is equal to hashed password.
 *
 * @param string $password Password
 * @param string $hashedPassword Hashed password
 * @return bool Returns true if password is correct, false if not
 */ 
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

/**
 * Verify if password is equal to hashed password.
 *
 * @param mysqli $conn MySQLi Database Connection
 * @param string $login Login
 * @param string $password Password
 * 
 * @return bool Returns true if login is correct.
 * @throws Exception If data can't be compared or other issues are encountered.
 */ 
function verifyLogin($conn, $login, $password) {
    if ($login == null || $login == "" || $password == null || $password == "") {
        return false;
    } 
    
    $variables = sanitizeSQL(
        $conn, 
        array(
            "login" => $login, 
        )
    );

    # Get Hashed password
    $sql = "SELECT * FROM users WHERE user_login = '" . $variables["login"] . "'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $result = $result->fetch_assoc();
    } else if ($result->num_rows > 1) {
        logError("More than one user with the same login - " . $login, 'error', 'verifyLogin', 'database');
        throw new Exception("Internal Error.");
    } else if ($result->num_rows == 0) {
        return false;
    } else {
        logError(mysqli_error($conn), 'error', 'verifyLogin', 'database');
        throw new Exception("Internal Error.");
    }
    
    # Compare passwords
    if (verifyPassword($password, $result['user_password'])) {
        return $result;
    } else {
        return false;
    }
}

/**
 * Return global ID of the user. ID is an incremented value, basically just order of accounts created. 
 *
 * @param mysqli $conn  MySQLi Database Connection
 * @param string $username Discord ID
 * @param array $db_users Array containing database information
 *                        - database
 *                        - table
 *                        - id_column
 *                        - discord_column
 * @return string
 */ 
function getGlobalID($conn, $username = null, $login = null) {

    if ($username == null && $login == null) {
        logError("getGlobalID - No Username or Login provided", 'error', 'getGlobalID', 'database');
        return false;
    }

    $variables = sanitizeSQL(
        $conn,
        array(
            'username' => $username,
            'login' => $login
        )
    );

    switch(true) {
        case ($username != null):
            $sql = "SELECT id FROM users WHERE username = " . $variables['username'] . "";
            break;
        case ($login != null):
            $sql = "SELECT id FROM users WHERE login = " . $variables['login'] . "";
            break;
    }

    

    if ($username != null && $username != "") {
        $sql = "SELECT id FROM users WHERE username = " . $variables[0] . "";
        logError("getGlobalID - No Username Provided", 'error', 'getGlobalID', 'database');
        return false;
    }

    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $result = $result->fetch_assoc();

        return $result['id'];
    } else {
        logError(mysqli_error($conn));
        return false;
    }
}

/**
 * Retrieves user data from the database based on the provided login.
 * 
 * @param mysqli $conn  MySQLi Database Connection
 * @param string $login The user login.
 * 
 * @return array|false The user data if found, false otherwise.
 * @throws Exception If no login is provided.
 */
function getUserData($conn, $login) {
    $variables = sanitizeSQL(
        $conn, 
        array(
            'login' => $login
        )
    );

    if ($login != null && $login != "") {
        $sql = "SELECT * FROM users WHERE user_login = '" . $variables['login'] . "'";
    } else {
        logError("getUserData - No Login provided", 'error', 'getUserData', 'database');
        throw new Exception("No Login provided");
    }

    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $result = $result->fetch_assoc();

        switch($result['access']) {
            case -1:
                $result['access'] = "Banned";
                break;
            case 0:
                $result['access'] = "User";
                break;
            case 1:
                $result['access'] = "Staff";
                break;
            
            default: 
                $result['access'] = "User";
                break;
        }

        switch($result['color_mode']) {
            case 0:
                $result['color_mode'] = "light";
                break;
            case 1:
                $result['color_mode'] = "dark";
                break;
            
            default: 
                $result['color_mode'] = "dark";
                break;
        }

        if ($result["avatar"] == null || $result["avatar"] == "" || $result["avatar"] == "NULL") {
            $result["avatar"] = "https://place-hold.it/200x200";
        }

        $result['logged_in'] = true;

        return $result;
    } else {
        logError(mysqli_error($conn) . " - " . $sql, 'error', 'getUserData', 'database');
        return false;
    }
}

/**
 * Retrieves the username associated with the given ID from the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $id The ID of the user.
 * @return string|false The username if found, false otherwise.
 * @throws Exception If the ID is missing or empty.
 */
function getUserName($conn, $id) {
    $variables = sanitizeSQL(
        $conn, 
        array(
            'id' => $id
        )
    );

    if ($id != null && $id != "") {
        $sql = "SELECT username FROM users WHERE id = " . $variables['id'] . "";
    } else {
        logError("getUserName - Brak ID", 'error', 'getUserName', 'database');
        throw new Exception("Brak ID");
    }

    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $result = $result->fetch_assoc();

        return $result['username'];
    } else {
        logError(mysqli_error($conn) . " - " . $sql, 'error', 'getUserName', 'database');
        return false;
    }
}

/**
 * Retrieves the user's avatar link from the database.
 *
 * @param mysqli $conn The database connection object.
 * @param int $id The user ID.
 * @return string|array The avatar link if found, or a default placeholder link if not found.
 * @throws Exception If an error occurs while retrieving the avatar link.
 */
function getUserAvatar($conn, $id) { 
    $variables = sanitizeSQL(
        $conn,
        array(
            '$id' => $id
        )
    ); 

    if ($variables['$id'] != '' || $variables['$id'] != null) {
        $sql = "SELECT avatar_link FROM users WHERE id = " . $variables['$id'];
    } else {
        return "https://place-hold.it/200x200";
    }

    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $result = $result->fetch_assoc();

        if ($result['avatar_link'] == null || $result['avatar_link'] == "" || $result['avatar_link'] == "NULL") {
            return "https://place-hold.it/200x200";
        }

        return $result['avatar_link'];
    } else if ($result->num_rows == 0) {
        return "https://place-hold.it/200x200";
    } else {
        logError("Nie udało się pobrać avataru dla " . $id . " Błąd - " . mysqli_error($conn), 'error', 'getPosts()', 'Database');
        throw new Exception("Nie udało się pobrać avataru dla użytkownika " . $id);
    }
}


/**
 * Updates user data in the database.
 *
 * @param mysqli $conn  MySQLi Database Connection
 * @param string $user_id The user login.
 * @param array $data The data to update for the user. In array format of 'column' => 'value'.
 * 
 * @return bool Returns true if the update is successful, false otherwise.
 * @throws Exception If no login is provided.
 */
function updateUserData($conn, $user_id, $data) {
    $variables = sanitizeSQL(
        $conn, 
        array(
            'user_id' => $user_id
        )
    );

    foreach ($data as $key => $value) {
        $data[$key] = sanitizeSQL($conn, array($value))[0];
    }

    if ($user_id != null && $user_id != "") {
        $sql = "UPDATE users SET ";
        foreach ($data as $key => $value) {
            $sql .= $key . " = '" . $value . "', ";
        }
        $sql = substr($sql, 0, -2);
        $sql .= " WHERE id = '" . $variables['user_id'] . "'";
    } else {
        logError("updateUserData - No ID provided", 'error', 'updateUserData', 'database');
        throw new Exception("No ID provided");
    }

    $result = $conn->query($sql);
    logError(gettype($result) . " - " . $sql, 'error', 'updateUserData', 'database');
    if ($result) {
        return true;
    } else {
        logError(mysqli_error($conn) . " - " . $sql, 'error', 'updateUserData', 'database');
        return false;
    }
}

function checkIfUserExists ($conn, $username) {
    $variables = sanitizeSQL(
        $conn, 
        array($username)
    );
    $sql = "SELECT * FROM users WHERE username = '" . $variables[0]. "'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        return true;
    } else if ($result->num_rows > 1) {
        logError("More than one user with the same Username - " . $username);
        return false;
    } else {
        logError(mysqli_error($conn));
        return false;
    }
}

?>