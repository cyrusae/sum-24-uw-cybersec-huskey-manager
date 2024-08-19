<?php
//session_start();
include '../components/loggly-logger.php';
include '../components/console-logger.php';

$servername = "backend-mysql-database";
$username = "user";
$password = "supersecretpw";
$dbname = "password_manager";

$conn = new mysqli($servername, $username, $password, $dbname);

unset($error_message);

if ($conn->connect_error) {    
    //die('A fatal error occurred and has been logged.');
    $errorMessage = "Connection failed: " . $conn->connect_error;
    $logger->error($errorMessage);
    die($errorMessage);
}
$limitAccountRequestsTo = 1;
$waitBeforeAllowingNewAccount = 600;
//TODO log number of account creation attempts (form submissions)
//TODO throttle account creation 
//TODO limit account name collisions (need a message reflecting it)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['hasRequestedAccount'])) {
        $_SESSION['hasRequestedAccount']++;
    } else {
        $_SESSION['hasRequestedAccount'] = 1;
        $_SESSION['accountRequestMade'] = time();
    }

    if ($_SESSION['hasRequestedAccount'] > $limitAccountRequestsTo) {
        $currentTime = time();
        $timeSinceLastAttempt = $currentTime - $_SESSION['accountRequestMade'];
        if ($timeSinceLastAttempt < $waitBeforeAllowingNewAccount) {
            $error_message('You have already requested an account. If you believe you are seeing this message in error, contact your network administrator.');
            $logger->warning('Additional account requests prevented due to being #' . $_SESSION['hasRequestedAccount'] . ' from this user in the past ' . $timeSinceLastAttempt . ' seconds.');
            exit();
        } else {
            $logger->notice('This user has made ' . $_SESSION['hasRequestedAccount'] . ' account requests, but is following timeout rules.');
            $_SESSION['accountRequestMade'] = $currentTime;
        }
    }

    $algo = PASSWORD_DEFAULT;
    $options = ['cost' => 13];
    
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], $algo, $options);
    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    //TODO: Parameterize this query!
    $sql = "INSERT INTO users (username, first_name, last_name, email, password, default_role_id, approved) 
            VALUES ('$username', '$firstName', '$lastName', '$email', '$password', 3, 0)";


    if ($conn->query($sql) === TRUE) {
        $logger->notice('Account creation request received for user ' . $username . ' made by ' . $firstName . ' ' . $lastName);
        header("Location: /login.php");
        exit();
    } else {
        $error_message = 'Error creating account: ' . $conn->error;
        $logger->error($error_message);
        die($error_message);
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
    <title>Request Account</title>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center">Request Account</h2>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="request_account.php" method="post">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" pattern="\w" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Request Account</button>
            </form>
        </div>
    </div>
</body>
</html>
