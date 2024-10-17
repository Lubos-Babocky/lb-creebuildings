<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$start = microtime(true);
require_once './autoload.php';
$mid = microtime(true);
echo sprintf("autoload %s<br>", $mid - $start);
$import = LB\CreeBuildings\Import::GetInstance()->runImport();