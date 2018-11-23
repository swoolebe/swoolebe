<?php
require __DIR__ . '/vendor/autoload.php';

$runtime = \System\Be::getRuntime();
$runtime->setRootPath(__DIR__);
$runtime->execute();

