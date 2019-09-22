<?php
// DIC configuration
use App\Action\Emails\PlainTemplateBuilder;
use App\Batch\DeferredPrivacyBatch;
use App\Batch\EmailSender;
use App\Batch\EntityManagerBuilder;
use App\DoctrineEncrypt\Encryptors\OpenSslEncryptor;
use App\Helpers\StringTemplate\Engine;
use App\Service\AttachmentsService;
use App\Service\DeferredPrivacyService;
use GuzzleHttp\Client;
use Slim\App;

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    return $logger;
};


// Doctrine
$container['em-config'] = function ($c) {
    $settings = $c->get('settings');
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['doctrine_config']['meta']['entity_path'],
        $settings['doctrine_config']['meta']['auto_generate_proxies'],
        $settings['doctrine_config']['meta']['proxy_dir'],
        $settings['doctrine_config']['meta']['cache'],
        false
    );

    $em = \Doctrine\ORM\EntityManager::create($settings['doctrine_config']['connection'], $config);

    return $em;
};



$container['customers'] = function ($c) {
    $db = new \MicroDB\Database('data/customers'); // data directory
    return $db;
};

$container['gclient'] = function ($c) {
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');


    return $client;


    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
};
