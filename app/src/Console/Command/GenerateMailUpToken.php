<?php

namespace App\Console\Command;

use Console\Exception;
use App\Service\MailUP\Token as MailUPTokenService;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMailUpToken extends Base
{
    protected function configure()
    {
        $this->setName('mail:mailup:generate_token')
            ->setDescription('Generate a new MailUP token')
            ->addArgument(
                'owner',
                InputArgument::REQUIRED,
                'The the destination owner'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'The customer MailUP username'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'The customer current MailUP password'
            )
            ->addArgument(
                'clientid',
                InputArgument::REQUIRED,
                'The customer MailUP client ID'
            )
            ->addArgument(
                'clientsecret',
                InputArgument::REQUIRED,
                'The customer MailUP client secret'
            )->addArgument(
                'alertemail',
                InputArgument::REQUIRED,
                'The alert email when will any problem occur with API calls'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws Exception\WrongCommandParam
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Get console arguments
        $ownerId = $input->getArgument('owner');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $clientId = $input->getArgument('clientid');
        $clientSecret = $input->getArgument('clientsecret');
        $alertEmail = $input->getArgument('alertemail');
        $this->validateArguments(
            $ownerId,
            $username,
            $password,
            $clientId,
            $clientSecret,
            $alertEmail
        );


        $service = new MailUPTokenService();
        /** @var \App\Entity\Privacy\MailUpToken $token */
        try {
            $token = $service->getNewTokenByOwnerId (
                $ownerId,
                $username,
                $password,
                $clientId,
                $clientSecret,
                $alertEmail
            );
        } catch (\Exception $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));
        }
        $output->writeln(sprintf('A new token was generated'));
    }

    /**
     * Validate command line arguments
     *
     * @param int $ownerId
     * @param string $username
     * @param string $password
     * @param string $clientId
     * @param string $clientSecret
     * @param string $alertEmail
     *
     * @throws Exception\WrongCommandParam
     */
    private function validateArguments (
        $ownerId,
        $username,
        $password,
        $clientId,
        $clientSecret,
        $alertEmail
    ) {

        if ((int)$ownerId <= 0) {
            throw new Exception\WrongCommandParam(sprintf(
                'The Owner ID must be a positive integer'
            ));
        }
        if (empty($username)) {
            throw new Exception\WrongCommandParam(sprintf(
                'Empty or wrong Customer Username'
            ));
        }
        if (empty($password)) {
            throw new Exception\WrongCommandParam(sprintf(
                'Empty or wrong Customer Password'
            ));
        }
        if (empty($clientId)) {
            throw new Exception\WrongCommandParam(sprintf(
                'Empty or wrong Customer Client ID'
            ));
        }
        if (empty($clientSecret)) {
            throw new Exception\WrongCommandParam(sprintf(
                'Empty or wrong Customer Client Secret'
            ));
        }
        if (empty($alertEmail)) {
            throw new Exception\WrongCommandParam(sprintf(
                'Empty or wrong Alert Email'
            ));
        }
    }
}