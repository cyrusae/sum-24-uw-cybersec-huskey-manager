<?php

session_start();

include './components/loggly-logger.php';
include './components/console-logger.php';

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 1;
    $_SESSION['ip'] = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
} else {
    $_SESSION['count']++;
} //Count session user-side to make credential-stuffing harder (this is a work in progress, don't look at me.)

$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$conn = new mysqli($hostname, $username, $password, $database);

//This seems wrong? 
// if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}

//unset($error_message);

if ($conn->connect_error) {
    $errorMessage = "Connection failed: " . $conn->connect_error;    
    $logger->error($errorMessage); //Log failed connection
    die($errorMessage);
}
//TODO:
//setcookie()

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT username FROM users WHERE username = '$username'";
    $sql_exists = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND approved = 1";
    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        $result = $conn->query($sql_exists); 
       if ($result->num_rows < 1) {
        $warningMessage = 'Login failed with incorrect password for username ' . $username;
        $logger->warning($warningMessage);
        $error_message = 'Invalid username or password.';
       } else {
//        $logger->notice('Login attempted for username ' . $username);

//TODO: regenerate session
        $userFromDB = $result->fetch_assoc();

        $_SESSION['authenticated'] = $username;
        $logger->info('New session begun for user: ' . $username);   

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
    $session_info = var_export($_SESSION, true);
    //SESSION TELL ME YOUR SECRETS
    $logger->info('A session exists: ' . time() . ' : ' . $session_info);
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
