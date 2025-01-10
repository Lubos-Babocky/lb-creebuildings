<?php

require_once './autoload.php';

/*
  $partners = \LB\CreeBuildings\Service\CreeApiService::GetInstance()->loadAllPartners();
  ob_clean();
  header('Content-Type: application/json');
  die(json_encode($partners));
  /* */



LB\CreeBuildings\Import::GetInstance()
    ->runImport();
