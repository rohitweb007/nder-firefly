<?php

/*
  |--------------------------------------------------------------------------
  | Register The Laravel Class Loader
  |--------------------------------------------------------------------------
  |
  | In addition to using Composer, you may use the Laravel class loader to
  | load your controllers and models. This is useful for keeping all of
  | your classes in the "global" namespace without Composer updating.
  |
 */

ClassLoader::addDirectories(array(
    app_path() . '/commands',
    app_path() . '/controllers',
    app_path() . '/models',
    app_path() . '/database/seeds',
));

/*
  |--------------------------------------------------------------------------
  | Application Error Logger
  |--------------------------------------------------------------------------
  |
  | Here we will configure the error logger setup for the application which
  | is built on top of the wonderful Monolog library. By default we will
  | build a rotating log file setup which creates a new file each day.
  |
 */

$monolog = Log::getMonolog();
$monolog->pushHandler(new Monolog\Handler\SyslogHandler('intranet', 'user', Monolog\Logger::DEBUG, false, LOG_PID));

/*
  |--------------------------------------------------------------------------
  | Application Error Handler
  |--------------------------------------------------------------------------
  |
  | Here you may handle any errors that occur in your application, including
  | logging them or displaying custom views for specific errors. You may
  | even register several error handlers to handle different types of
  | exceptions. If nothing is returned, the default error view is
  | shown, which includes a detailed stack trace during debug.
  |
 */

App::error(function(Exception $exception, $code) {
          Log::error($exception);
        });

/*
  |--------------------------------------------------------------------------
  | Maintenance Mode Handler
  |--------------------------------------------------------------------------
  |
  | The "down" Artisan command gives you the ability to put an application
  | into maintenance mode. Here, you will define what is displayed back
  | to the user if maintenace mode is in effect for this application.
  |
 */

App::down(function() {
          return Response::make("Be right back!", 503);
        });

/*
  |--------------------------------------------------------------------------
  | Require The Filters File
  |--------------------------------------------------------------------------
  |
  | Next we will load the filters file for the application. This gives us
  | a nice separate location to store our route and application filter
  | definitions instead of putting them all in the main routes file.
  |
 */

function mf($m) {
  return '&euro; ' . number_format(floatval($m), 2, ',', '.');
}

define('CACHE_TODAY', date('Ymd'));
function cacheKey() {
  $keys = func_get_args();
  $cKey = Auth::user()->id;
  foreach ($keys as $key) {
    if (is_string($key)) {
      $cKey .= $key;
    } else if ($key instanceof DateTime) {
      $cKey .= $key->format('Ymd');
    } else if (is_int($key) || is_float($key)) {
      $cKey .= (string) $key;
    } else if (is_null($key)) {
      $cKey .= 'NULL';
    }
  }
  return $cKey;
}

require app_path() . '/filters.php';