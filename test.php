<?php
require './autoload.php';


$creeapiService = \LB\CreeBuildings\Service\CreeApiService::GetInstance();
$project = $creeapiService->loadProject('8EPHN5M14AIIQ');

unset($project['description']);
unset($project['embeddedImages']);
unset($project['attachments']);

echo '<pre>';
var_dump($project);
die(__METHOD__ . '::' . __LINE__);