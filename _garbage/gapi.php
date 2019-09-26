<?php
/**
 * Created by PhpStorm.
 * User: mauroadmin
 * Date: 25/09/2019
 * Time: 17:30
 */

$key = 'AIzaSyCO2En_rXHFkGeN9NVYQjB-Px8_QcZWugc';
require __DIR__ . '/../vendor/autoload.php';
if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}


$client = new Google_Client();
$client->setApplicationName('Google Sheets API PHP Quickstart');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->setDeveloperKey($key);
$service = new Google_Service_Sheets($client);

// https://docs.google.com/spreadsheets/d/
//   1BgtQNu0Rjy9T0uAqykJDv_uuJ9z8bFj1-ygtCc6P2qE
///edit#gid=0
$spreadsheetId = '1BgtQNu0Rjy9T0uAqykJDv_uuJ9z8bFj1-ygtCc6P2qE';
$range = 'Operazioni!A1:A5';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
