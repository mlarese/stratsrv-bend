<?php

namespace App\Console\Command;

use Console\Exception;
use App\Service\MailUP\Token as MailUPTokenService;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshMailUpToken extends Base
{
    protected function configure()
    {
        $this->setName('mail:mailup:refresh_token')
            ->setDescription('Refresh MailUP token')
	        ->addArgument(
		        'owner',
		        InputArgument::REQUIRED,
		        'The destination owner'
	        );
    }

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    	// Get console arguments
        $ownerId = $input->getArgument('owner');
	    $this->validateArguments(
		    $ownerId
	    );


	    $service = new MailUPTokenService();
	    /** @var \App\Entity\Privacy\MailUpToken $token */
	    try {
		    $token = $service->refreshTokenByOwnerId (
			    $ownerId
		    );
	    } catch (\Exception $e) {
		    $output->writeln(sprintf('Error: %s', $e->getMessage()));
		    return;
	    }
	    if (is_null($token)) {
		    $output->writeln(sprintf('No token found. Have you created the token first?'));
	    }
	    $output->writeln(sprintf(
	    	'The token: %s was refreshed on: %s',
		    $token->getToken()['access_token'],
		    $token->getCreated()->format('Y-m-d H:i:s')
	    ));
    }

	/**
	 * Validate command line arguments
	 *
	 * @param int $ownerId
	 *
	 * @throws Exception\WrongCommandParam
	 */
    private function validateArguments (
    	$ownerId
	) {

    	if ((int)$ownerId <= 0) {
    		throw new Exception\WrongCommandParam(sprintf(
    			'The Owner ID must be a positive integer'
		    ));
	    }
    }
}