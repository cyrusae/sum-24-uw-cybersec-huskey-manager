<?php
$sql = $conn->prepare("SELECT password FROM users WHERE username = ? AND approved = 1");
$username = $_POST['username'];
$sql->bind_param("s", $username); //use $username in the query 
$sql->execute();
$sql->bind_result($password_expected); //hold the retrieved password (which, ideally, is hashed)
$sql->fetch(); //tutorials say this is needed to actually pull the value out? is that true

//add exit/failure on not locating existing user here

$algo = PASSWORD_DEFAULT; //PHP will update our hashing to better encryption algorithms as the language gets new ones
$options = ['cost' => 10];

$password = $_POST['password'];

if (password_verify($password, $password_expected) || ($password === $password_expected)) { //Is the password either actually the right hash (desired behavior) or the plaintext version (we can handle that...)
 //Password is valid, proceed with login
 $hashed_password = password_hash($password, $algo, $options);
 if (password_needs_rehash($password_expected, $algo, $options) || ($password === $password_expected)) {
  $sql_update_password = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
  $sql_update_password->bind_param("ss", $hashed_password, $username);
  $sql_update_password->execute();
  $logger->notice("Password for " . $username . "was rehashed and updated");
 }
 $sql_get_user = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND approved = 1");
 $sql_get_user->bind_param("ss", $username, $hashed_password);
 $sql_get_user->execute();
 $sql_get_user->bind_result($result);
 $sql_get_user->fetch();
 $userFromDB = $result->fetch_assoc();
// return $userFromDB; //upgrade to function, in my dreams.
} else {
 //Fail this login attempt
}

?>