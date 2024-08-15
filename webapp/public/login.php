<?php
$cookie_behaviors = ['lifetime' => 60000, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict'];

session_set_cookie_params($cookie_behaviors);
session_start();
//NOTE: This throws a fatal error with "Undefined constant 'session'"
//ini_set(session.cookie_secure, 'on');
//TODO: can I get away with this?
//ini_set(session.use_strict_mode, 1);
//ini_set(session.cookie_samesite, 'Strict');
//ini_set(session.cookie_httponly, true);

include './components/loggly-logger.php';
include './components/console-logger.php';

$_SESSION['ip'] = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
 //Count session user-side to make credential-stuffing harder (this is a work in progress, don't look at me.)

$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$conn = new mysqli($hostname, $username, $password, $database);

//This seems wrong? 
// if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}

unset($error_message);

if ($conn->connect_error) {
    $errorMessage = "Connection failed: " . $conn->connect_error;    
    $logger->error($errorMessage); //Log failed connection
    die($errorMessage);
}
//TODO:
//setcookie()

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
//    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql_password = $conn->prepare("SELECT password FROM users WHERE username = ? AND approved = 1");
    $sql_password->bind_param("s", $username);
    $sql_password->execute();
    $sql_password->store_result();
    $sql_password->bind_result($password_expected);
    $sql_password->fetch();
    //Test!
    //echo 'TEST: there was an expected password, ' . $password_expected;
    //echo var_dump($password_expected);
    //echo gettype($password_expected);
    //Finding: if it's not there it returns NULL with type NULL 
    if ($password_expected === NULL) {
//        echo 'This failed based on the password, which is the desired behavior.';
        $error_message = 'Invalid username or password.'; 
    }
    $algo = PASSWORD_DEFAULT; //trust PHP to use the gnarliest encryption it has available
    $options = ['cost' => 10]; //recommended as a default
    if (password_verify($password, $password_expected) || ($password === $password_expected)) {
        $hashed_password = password_hash($password, $algo, $options);
        echo 'The password verification is working?? Expected hash: ' . $hashed_password;
        
    }



    $sql = "SELECT username FROM users WHERE username = '$username'";
    $sql_exists = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND approved = 1"; //TODO: not star
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        $result = $conn->query($sql_exists); 
       if ($result->num_rows < 1) {
        $warningMessage = 'Login failed with incorrect password for username ' . $username;
        $logger->warning($warningMessage);
        $error_message = 'Invalid username or password.';
       } else {
        $userFromDB = $result->fetch_assoc();
        session_regenerate_id(true);

        $_SESSION['authenticated'] = $username;
    //    $logger->info('New session begun for user: ' . $username);   

        if ($userFromDB['default_role_id'] == 1) {        
            $_SESSION['isSiteAdministrator'] = true;
            $logger->info('Administrator login by ' . $username);                
        }else{
            $_SESSION['isSiteAdministrator'] = false;
        }
        header("Location: index.php");
        exit(); }
    } else {
        $error_message = 'Invalid username or password.'; 
        $logger->warning('Login failed for nonexistent user: ' . $username); 
        //TODO: Track number of failed login attempts separately for rejection purposes 
    }



    $conn->close();
}
  //  $session_info = var_export($_SESSION, true);
    //SESSION TELL ME YOUR SECRETS
//    $logger->info('A session exists: ' . time() . ' : ' . $session_info);
//    echo('PANTS?'); //echo works
//echo('Contents of var_export this time: ' . $session_info);


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
