<?php

if ($_SESSION['isSiteAdministrator'] !== true) {
    header('Location: /index.php');
    exit;
}

?>