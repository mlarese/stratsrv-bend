<?php
/**
 * Created by PhpStorm.
 * User: mauroadmin
 * Date: 26/09/2019
 * Time: 10:32
 */

require __DIR__ . '/../vendor/autoload.php';
$logger = Logger::getLogger("main");
$logger->info("This is an informational message.");
$logger->warn("I'm not feeling so good...");
