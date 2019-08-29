<?php

namespace App\Console\Command;

use App\Resource\OwnerResource;
use App\Exception\MailUPListException;
use Console\Exception;
use App\Service\MailUP\Token as MailUPTokenService;
use App\Service\MailUP\Lists as MailUPListService;
use App\Service\MailUP\Recipient as MailUPRecipientService;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceMailUp extends Base
{
    protected function configure()
    {
        $this->setName('mail:mailup:maintenance')
            ->setDescription('MailUP maintenance service')
	        ->addArgument(
		        'owner',
		        InputArgument::OPTIONAL,
		        'The destination owner'
	        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws \App\Exception\MailUPException
     * @throws Exception\WrongCommandParam
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

	    // Get console arguments
	    $ownerId = $input->getArgument('owner');
	    try {
		    $this->validateArguments(
			    $ownerId
		    );
		    $this->maintenanceByOwnerId(
			    $ownerId,
			    $output
		    );
	    } catch (\Exception $e) {

		    // Get all owners
		    $service = new MailUPTokenService();
		    $or = new OwnerResource($service->getContainer()['em-config']);
		    $owners = $or->geOwnersFW();
		    foreach ($owners as $owner) {
			    /** @var \App\Entity\Proxy\OwnerProxy $owner */
			    $this->maintenanceByOwnerId(
				    $owner->getId(),
				    $output
			    );
		    }
	    }
    }

    /**
     * Run maintenance by Owner ID
     *
     * @param int $ownerId
     * @param OutputInterface $output
     * @throws Exception\WrongCommandParam
     */
    private function maintenanceByOwnerId (
    	int $ownerId,
	    OutputInterface $output
    ) {
	    $this->validateArguments(
		    $ownerId
	    );

	    // Token maintenance
	    $service = new MailUPTokenService();
	    try {
		    $service->maintenanceTokenByOwnerId (
			    $ownerId
		    );
	    } catch (\Exception $e) {
		    $output->writeln(sprintf('Error: %s', $e->getMessage()));
	    }

	    // List maintenance
	    $service = new MailUPListService();
	    try {
		    $service->maintenanceListsByOwnerId (
			    $ownerId
		    );
	    } catch (\Exception $e) {
		    $output->writeln(sprintf('Error: %s', $e->getMessage()));
	    }

	    // Recipient maintenance
	    $service = new MailUPRecipientService();
	    try {
		    $service->maintenanceRecipientsByOwnerId (
			    $ownerId
		    );
	    } catch (\Exception $e) {
		    $output->writeln(sprintf('Error: %s', $e->getMessage()));
	    }
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