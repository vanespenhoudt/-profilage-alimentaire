<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ⚠️ Remplace ce chemin par le chemin absolu réel sur ton serveur one.com
// Ex : /customers/a1b2c3/mve-nu.one.com/laravel_profilage
$laravelBase = '/REMPLACE/PAR/CHEMIN/ABSOLU/laravel_profilage';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelBase.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelBase.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelBase.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
