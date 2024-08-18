<?php
$cookie_behaviors = ['lifetime' => 60000, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict'];

session_set_cookie_params($cookie_behaviors);
session_start();

include './components/loggly-logger.php';
include './components/console-logger.php';

$_SESSION['ip'] = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
 //Count session user-side to make credential-stuffing harder (this is a work in progress, don't look at me.)

 include './components/experimental/connection_maker.php';
 
// $sql_reset_admin_password = $conn->prepare('UPDATE users SET password="Sup3rS3cr3t@dm1n" WHERE username="admin"');
// $sql_reset_admin_password->execute();
// $sql_reset_admin_password->close();

unset($error_message);

//if ($conn->connect_error) {
//    $errorMessage = "Connection failed: " . $conn->connect_error;    
//    $logger->error($errorMessage); //Log failed connection
//    die($errorMessage);
//}


$maxAttempts = 3;
$lockoutDuration = 30;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts']++;
    } else {
        $_SESSION['login_attempts'] = 1;
    }
    if ($_SESSION['login_attempts'] > $maxAttempts) {
        if (isset($_SESSION['login_times_warned'])) {
            $_SESSION['login_times_warned']++;
            $total_tries = ($_SESSION['login_times_warned'] * $maxAttempts);
        } else {
            $_SESSION['login_times_warned'] = 1;
            $total_tries = $maxAttempts;
        }
        $total_lockout = $_SESSION['login_attempts'] * $_SESSION['login_times_warned'] * $lockoutDuration * $_SESSION['login_times_warned'];
        $logger->warning('User ' . $username . ' with IP ' . $_SESSION['ip'] . ' was blocked for ' . $total_lockout . ' seconds due to a total of ' ) . $_SESSION['login_attempts'] . ' attempts.';
        $error_message = 'Too many login attempts. Please try again later. If you believe you are seeing this message in error, contact your network administrator.';
//        exit();
    } 
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql_password = $conn->prepare("SELECT password FROM users WHERE username = ? AND approved = 1");
    $sql_password->bind_param("s", $username);
    $sql_password->execute();
    $sql_password->store_result();
    $sql_password->bind_result($password_expected);
    $sql_password->fetch();
    $sql_password->close();

    
    //Finding: if it's not there it returns NULL with type NULL 
    if ($password_expected == NULL) {
        $has_password = FALSE;
        $error_message = 'Invalid username or password.';
//        exit();
    } 
    $algo = PASSWORD_DEFAULT; //trust PHP to use the gnarliest encryption it has available
    $options = ['cost' => 13]; 
    
    if (!isset($has_password) || $has_password != FALSE) {
        if (password_verify($password, $password_expected)) {
            $has_password = TRUE;
            $needs_update = FALSE;
            session_regenerate_id(true);
            $_SESSION['authenticated'] = $username;
        } else if ($password_expected == $password || password_needs_rehash($password_expected, $algo, $options)) {
            $needs_update = TRUE;
            session_regenerate_id(true);
            $_SESSION['authenticated'] = $username;
        } else {
            $has_password = FALSE;$needs_update = FALSE;
            unset($_SESSION['authenticated']);
            $error_message = 'Invalid username or password.';
        }
    } else {
        $has_password = FALSE;
        $needs_update = FALSE;
        unset($_SESSION['authenticated']);
        $error_message = 'Invalid username or password.';
    }

    if (isset($_SESSION['authenticated'])) {
        if (isset($needs_update) && $needs_update == TRUE) {
            $hashed_password = password_hash($password, $algo, $options);
            $sql_update_user_password = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $sql_update_user_password->bind_param("ss", $hashed_password, $username);
            $sql_update_user_password->execute();
            $logger->notice("Password for " . $username . " was rehashed and updated. Logging in now.");
            $sql_update_user_password->close();
        }
        $sql_get_user = $conn->prepare("SELECT * from users WHERE username = ? AND approved = 1");
        $sql_get_user->bind_param('s', $username);
        $sql_get_user->execute();
        $result = $sql_get_user->get_result();
        $userFromDB = $result->fetch_assoc();
        if ($userFromDB !== NULL) {
            if ($userFromDB['default_role_id'] == 1) 
            {
                $_SESSION['isSiteAdministrator'] = TRUE;
            } else {
                $_SESSION['isSiteAdministrator'] = FALSE;
            }
            unset($_SESSION['login_attempts']);
            header("Location: index.php");
            exit();
        }
        
        $sql_get_user->close();
    } else {
        $has_password = FALSE;
        $needs_update = FALSE;
        unset($_SESSION['authenticated']);
        $error_message = 'Invalid username or password.';
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Login Page</title>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center">Login</h2>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="mt-3 text-center">
                <a href="./users/request_account.php" class="btn btn-secondary btn-block">Request an Account</a>
            <!-- TODO add timeout on requesting accounts so that spamming that isn't viable either --> 
            </div>
        </div>
    </div>
</body>
</html>
