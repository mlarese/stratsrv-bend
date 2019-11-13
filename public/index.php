<?php
use Doctrine\Common\Annotations\AnnotationRegistry;
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

// session_start();


// Instantiate the app

$localSettingsPath = __DIR__ . '/../app/settings_local.php';
$settingsPath = __DIR__ . '/../app/settings.php';

if(file_exists($localSettingsPath)) {
    $settings = require $localSettingsPath;
} else {
    $settings = require $settingsPath;
}

// print_r($settings);  die('settings');


$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../app/dependencies.php';

// Register middleware
require __DIR__ . '/../app/middleware.php';

// Register routes
require __DIR__ . '/../app/routes.php';


$logConfig = array(
    'appenders' => array(
        'default' => array(
            'class' => 'LoggerAppenderConsole',
            'layout' => array(
                'class' => 'LoggerLayoutPattern',
            ),
        ),
        'file' => array(
            'class' => 'LoggerAppenderFile',
            'layout' => array(
                'class' => 'LoggerLayoutPattern',
            ),
            'params' => array(
                'file' => 'main.log',
                'append' => true,
                'conversionPattern' => '%date %logger %-5level %msg%n'
            )
        ),
    ),
    'rootLogger' => array(
        'appenders' => array('default'),
    ),
);


Logger::configure($logConfig);


// Run!
$app->run();
