<?php
session_start();

include './components/loggly-logger.php';

include './components/console-logger.php';


 

$logger->info('Session ended for user ' .  $username); 

// Redirect to the login page

session_unset();
session_destroy();
session_regenerate_id(true);
header('Location: /login.php');
exit();

?>