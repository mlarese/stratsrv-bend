<?php

namespace App\Action\MailUP;

use App\Action\AbstractAction;
use App\Entity\Config\OwnerRepository;
use App\Entity\Privacy\Configuration;
use App\Exception\MailUPException;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Service\MailUP\Token as TokenService;
use App\Service\MailUP\Lists as ListService;

/**
 * Class Token
 * @package App\Action\MailUP
 * @todo manca un audit delle azioni
 * @todo manca un controllo degli accessi
 */
class Token extends AbstractAction {

    /**
     * Get a token by current owner
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getByCurrentOwner (
        Request $request,
        Response $response,
        array $args
    ) {
        // Get owner ID
        $ownerId = $this->getOwnerId($request);

        return $this->get(
            $ownerId,
            $request,
            $response,
            $args
        );
    }

    /**
     * Get a token by specific owner
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getByOwner (
        Request $request,
        Response $response,
        array $args
    ) {
        if (empty($request->getAttribute('owner_id'))) {
            $ownerId = 0;
        } else {
            $ownerId = (int)$request->getAttribute('owner_id');
        }

        return $this->get(
            $ownerId,
            $request,
            $response,
            $args
        );
    }

    /**
     * Get a token by owner
     *
     * @param $ownerId int
     * @param $request Request
     * @param $response Response
     * @param array $args
     * @return Response
     * @todo eventualmente staccare logica in service a livello di DataONE (il service a livello di MailUP già esiste)
     */
    protected function get (
        int $ownerId = 0,
        Request $request,
        Response $response,
        array $args
    ) {
        try {
            if (empty($ownerId)) {
                throw new MailUPException(sprintf(
                    'The owner ID is empty'
                ));
            }

            $tokenService = new TokenService();
            $token = $tokenService->getTokenByOwnerId($ownerId);
            $responseBody = [
                'token' => '',
                'status' => '',
                'client_id' => '',
                'client_secret' => '',
                'alert_email' => '',
                'create_date' => '',
                'update_date' => ''
            ];

            if (is_null($token)) {
                /** @var \Doctrine\ORM\EntityManager $em */
                $em = $this->getContainer()['em-config'];
                $defaultConfig = $em->getRepository(OwnerRepository::class)->findOneBy([
                    'code' => 'EXTMODULE',
                    'description' => 'MailUP'
                ]);
                if (empty($defaultConfig->getData())) {
                    throw new MailUPException(sprintf(
                        'Default data not found'
                    ));
                }

                $responseBody['client_id'] = $defaultConfig->getData()['client_id'];
                $responseBody['client_secret'] = $defaultConfig->getData()['client_secret'];
            } else {
                $responseBody = [
                    'token' => $token->getToken()['access_token'],
                    'status' => (bool)$token->getStatus(),
                    'client_id' => $token->getClientId(),
                    'client_secret' => $token->getClientSecret(),
                    'alert_email' => $token->getAlertEmail(),
                    'create_date' => (!is_null($token->getCreated())) ? $token->getCreated()->format('Y-m-d H:i:s') : '',
                    'update_date' => (!is_null($token->getUpdated())) ? $token->getUpdated()->format('Y-m-d H:i:s') : ''
                ];
            }

            return $response->withJson(json_encode($responseBody));
        }  catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }
    }

    /**
     * Create a token using current owner
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function createByCurrentOwner (
        Request $request,
        Response $response,
        array $args
    ) {
        // Get owner ID
        $ownerId = $this->getOwnerId($request);

        return $this->maintenance (
            $ownerId,
            $request,
            $response,
            $args
        );
    }

    /**
     *  Create a token by specific owner
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function createByOwner (
        Request $request,
        Response $response,
        array $args
    ) {
        if (empty($request->getAttribute('owner_id'))) {
            $ownerId = 0;
        } else {
            $ownerId = (int)$request->getAttribute('owner_id');
        }

        return $this->maintenance (
            $ownerId,
            $request,
            $response,
            $args
        );
    }


    /**
     * Update a token using current owner ID
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function updateByCurrentOwner (
        Request $request,
        Response $response,
        array $args
    ) {
        // Get owner ID
        $ownerId = $this->getOwnerId($request);

        return $this->maintenance (
            $ownerId,
            $request,
            $response,
            $args
        );
    }

    /**
     * Update a token by owner ID
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function updateByOwner (
        Request $request,
        Response $response,
        array $args
    ) {
        if (empty($request->getAttribute('owner_id'))) {
            $ownerId = 0;
        } else {
            $ownerId = (int)$request->getAttribute('owner_id');
        }

        return $this->maintenance (
            $ownerId,
            $request,
            $response,
            $args
        );
    }

    /**
     * Create a token by owner
     *
     * @param int $ownerId
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @todo eventualmente staccare logica in service a livello di DataONE (il service a livello di MailUP già esiste)
     */
    protected function maintenance (
        int $ownerId = 0,
        Request $request,
        Response $response,
        array $args
    ) {
        try {
            $data = $request->getParsedBody();

            // Get owner ID
            if (empty($ownerId)) {
                throw new MailUPException(sprintf(
                    'The owner ID is empty'
                ));
            }

            $tokenService = new TokenService();
            $tokenService->getNewTokenByOwnerId (
                $ownerId,
                $data['username'],
                $data['password'],
                $data['client_id'],
                $data['client_secret'],
                $data['alert_email']
            );
            $this->saveOwnerConfiguration (
                $ownerId,
                $data['username'],
                $data['password'],
                $data['client_id'],
                $data['client_secret'],
                $data['alert_email']
            );
            $this->saveListConfiguration($ownerId);

            return $this->get(
                $ownerId,
                $request,
                $response,
                $args
            );
        }  catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }
    }

    /**
     * Save owner configuration
     *
     * @param int $ownerId
     * @param string $username
     * @param string $password
     * @param string $clientId
     * @param string $clientSecret
     * @param string $alertEmail
     * @throws \Doctrine\ORM\ORMException
     * @todo eventualmente staccare logica in service a livello di DataONE (il service a livello di MailUP già esiste)
     */
    protected function saveOwnerConfiguration (
        int $ownerId,
        string $username,
        string $password,
        string $clientId,
        string $clientSecret,
        string $alertEmail
    ) {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEmConfig();
        /** @var OwnerRepository $config */
        $config = $em->getRepository(OwnerRepository::class)
            ->findOneBy([
                'code' => 'EXTMODULE',
                'description' => 'MailUP',
                'ownerId' => $ownerId
            ]);
        if (is_null($config)) {
            $config = new OwnerRepository();
            $config->setCode('EXTMODULE')
                ->setDescription('MailUP')
                ->setOwnerId($ownerId);
        }
        $config->setData([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $username,
            'password' => $password,
            'alert_email' => $alertEmail
        ]);
        $em->persist($config);
        $em->flush();
    }

    /**
     * Save list configuration
     *
     * @param int $ownerId
     * @throws MailUPException
     * @throws \App\Exception\MailUPListException
     * @throws \App\Exception\MailUPTokenException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function saveListConfiguration (
        int $ownerId
    ) {

        // Fetch list details from MailUP
        $listService = new ListService();
        $lists = $listService->readByOwnerId (
            $ownerId
        );
        $listId = reset($lists)['IdList'];
        $listDetails = $listService->getDetails (
            $ownerId,
            $listId
        );

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);
        /** @var Configuration $ownerConfig */
        $ownerConfig = $em->getRepository(Configuration::class)
            ->findOneBy([
                'code' => 'mailup',
                'description' => 'mailup'
            ]);
        if (is_null($ownerConfig)) {
            $ownerConfig = new Configuration();
            $ownerConfig->setCode('mailup')
                ->setDescription('mailup');
        }
        $ownerConfig->setData([
            'address' => $listDetails['Address'],
            'city' => $listDetails['City'],
            'companyName' => $listDetails['CompanyName'],
            'contactName' => $listDetails['ContactName'],
            'countryCode' => $listDetails['CountryCode'],
            'expireDate' => '',
            'listName' => $listDetails['Name'],
            'ownerEmail' => $listDetails['OwnerEmail'],
            'ownerId' => $ownerId,
            'permissionReminder' => $listDetails['PermissionReminder'],
            'replyToEmail' => $listDetails['ReplyTo'],
            'senderName' => $listDetails['ContactName'],
            'usedForBusiness' => $listDetails['Business'],
            'usedForPrivate' => $listDetails['Public'],
            'websiteUrl' => $listDetails['WebSiteUrl']
        ]);
        $em->persist($ownerConfig);
        $em->flush();
    }
}
