<?php

include './components/loggly-logger.php';

include '../components/console-logger.php';



// Expire the authentication cookie
unset($_COOKIE['authenticated']); 
setcookie('authenticated', '', time() - 3600, '/');

// Expire the Administrator cookie
unset($_COOKIE['isSiteAdministrator']); 
setcookie('isSiteAdministrator', '', -1, '/'); 

$logger->info('Session ended for user ' .  $username); 

unset($_SESSION['count']); //If you successfully logged in and out you get to go home I GUESSS
// Redirect to the login page
header('Location: /login.php');
exit();

?>