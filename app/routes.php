<?php
// Routes



use App\Action\Attachments;
use App\Action\AttachmentView;
use App\Action\Configurations;
use App\Action\CustomerCares;
use App\Action\DeferredPrivacies;
use App\Action\Dictionaries;
use App\Action\Operators;
use App\Action\Owners;
use App\Action\PrivacyManager;
use App\Action\ShareSubscriberList;
use App\Action\Subscriptions;
use App\Action\Terms;
use App\Action\Users;
use App\Action\UsersRequests;
use App\Base\BaseRoutesManager;
use App\Entity\Privacy\Configuration;
use App\Entity\Privacy\UserRequest;


$app->get('/api/bi/datamart/{domain}', 'App\Action\Bi:retrieveDatamart');
$app->post('/api/bi/datamart/{domain}', 'App\Action\Bi:retrieveDatamart');
