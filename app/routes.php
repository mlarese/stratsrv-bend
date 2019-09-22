<?php
// Routes



$app->get('/api/test/welcome', 'App\Action\Test:welcome');
$app->post('/api/channels/prices', 'App\Action\Prices:updatePrices');
