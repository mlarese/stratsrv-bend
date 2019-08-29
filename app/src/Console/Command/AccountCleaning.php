<?php

namespace App\Console\Command;


use App\Service\DataOneCleanAccount;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AccountCleaning extends Base
{
    protected function configure()
    {
        $this->setName('clean:account')
            ->setDescription('Cancellation of accounts linked to the "No Consent" statement and associated with other information that they gave consent')
            ->addArgument(
                'owner',
                InputArgument::REQUIRED,
                'Select the owner'
            )
            ->addArgument(
                'termId',
                InputArgument::REQUIRED,
                'Insert the "No consent" information\'s term Id'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $ownerId = $input->getArgument('owner');
        $termId = $input->getArgument('termId');

        $this->validateArguments(
            $ownerId,
            $termId
        );

        $cleanService = new DataOneCleanAccount();
        $cleanService->removeAccount($ownerId, $termId);
    }

    protected function validateArguments(
        $ownerId,
        $termId
    )
    {
        // Filter owner ID
        if (!is_integer($ownerId)) {
            $ownerId = intval($ownerId);
            if ($ownerId < 1) {
                throw new \Console\Exception\WrongCommandParam(sprintf(
                    "Wrong owner ID. the owner ID must be grater than 0"
                ));
            }
        }

        // Filter term ID
        if (empty($termId)) {
            throw new \Console\Exception\WrongCommandParam(sprintf(
                "Wrong term ID"
            ));
        }
    }
}