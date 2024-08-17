<?php //Found cost 13.
$algo = PASSWORD_DEFAULT;

//Try to find a cost 

$timeTarget = 0.350; // 350 milliseconds

$cost = 10;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", $algo, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);

echo "Appropriate Cost Found: " . $cost;