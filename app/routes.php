<?php
// Routes



$app->get('/api/test/welcome', 'App\Action\Test:welcome');
$app->post('/api/channels/ericsoft', 'App\Action\Ericsoft:updatePricesDispo');
