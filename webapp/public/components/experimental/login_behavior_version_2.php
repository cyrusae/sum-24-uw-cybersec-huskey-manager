<?php
$algo = PASSWORD_DEFAULT;
$options = ['cost' => 10];

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$sql_get_password = $conn->prepare("SELECT password FROM users WHERE username = ? AND approved = 1");
	$sql_get_password->bind_param("s", $username);
	$sql_get_password->execute();
	$sql_get_password->store_result();
	$sql_get_password->bind_result($password_expected);
	$sql_get_password->fetch();
	$sql_get_password->close();

 if (empty($password_expected) || isset($password_expected) != true || is_null($password_expected) || password_verify($password, $password_expected) != true) { //These are two ways for the login attempt to fail: either the username doesn't meaningfully exist or the password isn't usable
  $error_message = 'Invalid username or password.';
 } else if (password_verify($password, $password_expected)) { //A password exists and matches the input
  if (password_needs_rehash($password_expected, $algo, $options)) {
   $newly_hashed_password = password_hash($password, $algo, $options);
   $sql_update_user_password = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
   $sql_update_user_password->bind_param("ss", $newly_hashed_password, $username);
   $sql_update_user_password->execute();
   $logger->notice("Password for " . $username . " was rehashed and updated.");
   $sql_update_user_password->close();
  }
  $sql_find_known_user = "SELECT user_id, username, first_name, last_name, email, default_role_id, approved FROM users WHERE username = '$username'"; //This is only going to be triggered when we have a known good user, so (I think) we can get away with using the otherwise-unsafe query format. (Because trying to do it as a prepared statement kept creating empty arrays.) 
  $result = $conn->query($sql_find_known_user);
  if ($result->num_rows == 1) {
   $userFromDB = $result->fetch_assoc();
   session_regenerate_id(true);
   $_SESSION['authenticated'] = $username;
   if ($userFromDB['default_role_id'] == 1) {
    $_SESSION['isSiteAdministrator'] = true;
   } else {
    $_SESSION['isSiteAdministrator'] = false;
   }
   if (isset($_SESSION['login_attempts'])) {
    unset($_SESSION['login_attempts']);
   }
   header("Location: index.php");
   exit();
  } else {
   $error_message = 'Invalid username or password.';
  }
  
 }





?>