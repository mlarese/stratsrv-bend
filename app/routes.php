<?php
// Routes


const API_CHANNELS_ERICSOFTRESP = '/api/channels/ericsoftresp';
$app->get('/api/test/welcome', 'App\Action\Test:welcome');
$app->post('/api/channels/ericsoft', 'App\Action\Ericsoft:updatePricesDispo');
$app->get('/api/channels/ericsoft/occupazione', 'App\Action\Ericsoft:operationResult');
$app->get('/api/channels/ericsoft/prenotazioni', 'App\Action\Ericsoft:operationResult');
$app->get('/api/channels/ericsoft/esitiprezzi', 'App\Action\Ericsoft:operationResult');
$app->get('/api/channels/ericsoft/esitidispo', 'App\Action\Ericsoft:operationResult');

$app->post('/api/channels/ericsoft/occupazione', 'App\Action\Ericsoft:operationResult');
$app->post('/api/channels/ericsoft/prenotazioni', 'App\Action\Ericsoft:operationResult');
$app->post('/api/channels/ericsoft/esitiprezzi', 'App\Action\Ericsoft:operationResult');
$app->post('/api/channels/ericsoft/esitidispo', 'App\Action\Ericsoft:operationResult');

$app->put('/api/channels/ericsoft/occupazione', 'App\Action\Ericsoft:operationResult');
$app->put('/api/channels/ericsoft/prenotazioni', 'App\Action\Ericsoft:operationResult');
$app->put('/api/channels/ericsoft/esitiprezzi', 'App\Action\Ericsoft:operationResult');
$app->put('/api/channels/ericsoft/esitidispo', 'App\Action\Ericsoft:operationResult');
/*
 https://stratservicemanager.scalingo.io/api/channels/ericsoft/occupazione'
 https://stratservicemanager.scalingo.io/api/channels/ericsoft/prenotazzioni'
 https://stratservicemanager.scalingo.io/api/channels/ericsoft/esitiprezzi'
 https://stratservicemanager.scalingo.io/api/channels/ericsoft/esitidispo'
**/
