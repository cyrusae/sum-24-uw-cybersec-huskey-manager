<?php
session_start();

include './components/loggly-logger.php';

include './components/console-logger.php';


 

$logger->info('Session ended for user ' .  $username); 

unset($_SESSION['count']); //If you successfully logged in and out you get to go home I GUESSS
// Redirect to the login page

session_unset();
session_destroy();
header('Location: /login.php');
exit();

?>