<?php

$createDataUser = 3500;
$createDataLog = 3500;

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../src/app.php';
require __DIR__ . '/../config/prod.php';

$l = 'Adam
Adrian
Alan
Alexander
Andrew
Anthony
Austin
Benjamin
Blake
Boris
Brandon
Brian
Cameron
Carl
Charles
Christian
Christopher
Colin
Connor
Dan
David
Dominic
Dylan
Edward
Eric
Evan
Frank
Gavin
Gordon
Harry
Ian
Isaac
Jack
Jacob
Jake
James
Jason
Joe
John
Jonathan
Joseph
Joshua
Julian
Justin
Keith
Kevin
Leonard
Liam
Lucas
Luke
Matt
Max
Michael
Nathan
Neil
Nicholas
Oliver
Owen
Paul
Peter
Phil
Piers
Richard
Robert
Ryan
Sam
Sean
Sebastian
Simon
Stephen
Steven
Stewart
Thomas
Tim
Trevor
Victor
Warren
William';

$names = explode('\n', str_replace(array("\n", "\r"), array('\n', '\r'), $l));
$namesc = count($names) - 1;

echo 'go user' . PHP_EOL;

for ($i = 0; $i < $createDataUser; $i++) {
    $name = $names[rand(0, $namesc)];
    $app['entryManager']->addEntry($name . '-' . $i, strtolower($name) . '-' . $i . '@provider.com', md5($i));
}

echo 'go log' . PHP_EOL;

for ($i = 0; $i < $createDataLog; $i++) {
    $name = $names[rand(0, $namesc)];

    $stmt = $app['db']->prepare('INSERT INTO log (`name`, `GUID`, `timestamp`, `logtype`) VALUES (?, ?, ?, ?)');
    $stmt->execute(array(
        $name . '-' . $i,
        md5('wll' . $i),
        date('Y-m-d H:i:s'),
        rand(3, 4)
    ));
}

echo 'done' . PHP_EOL;
