<?php

namespace App\Console\Command;

use Console\Exception;
use App\Service\MailUP\Resource as MailUPResourceService;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshMailUpResources extends Base
{
    protected function configure()
    {
        $this->setName('mail:mailup:refresh_resources')
            ->setDescription('Refresh MailUP resources (Country code list and Time Zone list)')
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

	    $service = new MailUPResourceService();
	    try {
		    $service->refreshCountryCodesListByOwnerId (
			    $ownerId
		    );
	    } catch (\Exception $e) {
		    $output->writeln(sprintf('Error: %s', $e->getMessage()));
		    return;
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