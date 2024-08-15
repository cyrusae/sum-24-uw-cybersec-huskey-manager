<?php
session_start();

include './components/loggly-logger.php';

include './components/console-logger.php';

// Redirect to the login page

session_unset();
session_destroy();
header('Location: /login.php');
exit();

?>