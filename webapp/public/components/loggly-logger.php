<?php

require __DIR__ . '/../../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\LogglyHandler;
use Monolog\Formatter\LogglyFormatter;

$logglyToken = $_ENV["LOGGLY_TOKEN"];

$logger = new Logger('UW Password Manager');
$logger->pushHandler(new LogglyHandler($logglyToken.'/tag/monolog', Logger::INFO));

$logger->info('Loggly Sending Informational Message');

//TODO: Actually take some kind of bloody advantage of the fact that this is sending JSON instead of barfing up verbose text only
?>