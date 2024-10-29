<?php
$start = microtime(true);
require_once './autoload.php';
$mid = microtime(true);
echo sprintf("autoload %s<br>", $mid - $start);
$import = LB\CreeBuildings\Import::GetInstance()->runImport();