<?php

if (!function_exists('get_option')) {
    $wpLoadPath = realpath(__DIR__ . '/../../../wp-load.php');
    if (file_exists($wpLoadPath)) {
        require_once $wpLoadPath;
    } else {
        die('wp-load.php not found!');
    }
}
spl_autoload_register(function ($class) {
    $prefix = 'LB\\CreeBuildings\\';
    $baseDir = __DIR__ . '/includes/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
