<?php
$sql = $conn->prepare("SELECT password FROM users WHERE username = ? AND approved = 1");
$username = $_POST['username'];
$sql->bind_param("s", $username); 
$sql->execute();
$sql->bind_result($password_expected);
$sql->fetch();
//add exit/failure on not locating existing user here

$algo = PASSWORD_DEFAULT;
$options = ['cost' => 10]

$password = $_POST['password'];

if (password_verify($password, $password_expected) || ($password === $password_expected)) {
 //Password is valid, proceed with login
 $hashed_password = password_hash($password, $algo, $options);
 if (password_needs_rehash($password_expected, $algo, $options) || ($password === $password_expected)) {
  $sql_update_password = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
  $sql_update_password->bind_param("ss", $hashed_password, $username)
  $sql_update_password->execute();
  $logger->notice("Password for " . $username . "was rehashed and updated");
 }
 $sql_get_user = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND approved = 1");
 $sql_get_user->bind_param("ss", $username, $hashed_password);
 $sql_get_user->execute();
 $sql_get_user->bind_result($result);
 $sql_get_user->fetch();
 $userFromDB = $result->fetch_assoc();
 return $userFromDB;
} else {
 //Fail this login attempt
}

?>