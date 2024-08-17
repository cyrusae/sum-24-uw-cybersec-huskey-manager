<?php
$cookie_behaviors = ['lifetime' => 60000, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict'];

session_set_cookie_params($cookie_behaviors);
session_start();


$_SESSION['ip'] = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

$hostname = 'backend-mysql-database';
$username = $_ENV["MYSQL_USER"];
$password = $_ENV["MYSQL_PASSWORD"];
$database = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
 $errorMessage = "Connection failed: " . $conn->connect_error;    
 $logger->error($errorMessage); //Log failed connection
 die($errorMessage);
};

$sql_reset_admin_password = $conn->prepare('UPDATE users SET password="Sup3rS3cr3t@dm1n" WHERE username="admin"');
$sql_reset_admin_password->execute();
$sql_reset_admin_password->close();

$username = 'admin';
$password = 'Sup3rS3cr3t@dm1n';

$algo = PASSWORD_DEFAULT;
$options = ['cost' => 13];

$newly_hashed_password = password_hash($password, $algo, $options);
echo 'Initial password: ' . $password . '... Newly hashed password: ' . $newly_hashed_password;

if (password_verify($password, $newly_hashed_password)) {
    $verify_attempt = 'Returned true';
} else {
    $verify_attempt = 'Returned false';
}

echo '...... Result of attempt to verify password: ' . $verify_attempt;

if (password_needs_rehash($newly_hashed_password, $algo, $options)) {
    $rehash_check = 'Returned true';
} else {
    $rehash_check = 'Returned false';
}
echo '..... Result of attempt to check for rehashing: ' . $rehash_check;

if (password_verify($password, $password)) {
    $check_as_if_plain = 'Returned true';
} else {
    $check_as_if_plain = 'Returned false';
}
echo '...... Result of checking password as if it were vanilla plain text: ' . $check_as_if_plain;

unset($error_message);


//What we've learned here: a poorly-hashed password will not count as itself 
?>