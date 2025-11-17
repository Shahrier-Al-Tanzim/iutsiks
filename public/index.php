<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());


// ===================================
// cwp working
// <?php

// use Illuminate\Foundation\Application;
// use Illuminate\Http\Request;

// define('LARAVEL_START', microtime(true));

// // Determine if the application is in maintenance mode...
// if (file_exists($maintenance = __DIR__.'/../iut_siks/storage/framework/maintenance.php')) {
//     require $maintenance;
// }

// // Register the Composer autoloader...
// require __DIR__.'/../iut_siks/vendor/autoload.php';

// // Bootstrap Laravel and handle the request...
// /** @var Application $app */
// $app = require_once __DIR__.'/../iut_siks/bootstrap/app.php';

// $app->handleRequest(Request::capture());


// redirect working
// <?php
// // Redirect to the Vercel deployment
// header('Location: https://iut-siks-user.vercel.app/');
// exit();
