<?php

namespace App\Console\Command;

use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\ABSImport;

class ImportStoreONE extends Base
{
    protected function configure()
    {
        $this->setName('import:abs:storeone')
            ->setDescription('Import store one clients from ABS')
            ->addArgument(
                'owner',
                InputArgument::REQUIRED,
                'Select the destination owner'
            )
            ->addArgument(
                'termId',
                InputArgument::REQUIRED,
                'Term Id'
            )
            ->addArgument(
                'registrationUrl',
                InputArgument::REQUIRED,
                'Select the registration URL'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The CSV file path'
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        // Validate arguments
        $ownerId = $input->getArgument('owner');
        $termId = $input->getArgument('termId');
        $registrationUrl = $input->getArgument('registrationUrl');
        $file = realpath($input->getArgument('file'));
        $this->validateArguments(
            $ownerId,
            $termId,
            $registrationUrl,
            $file
        );

        // Get import service
        $ABSImport = new ABSImport();
        $ABSImport->importStoreOne(
            $ownerId,
            $termId,
            $registrationUrl,
            $file
        );

    }

    /**
     * Validate arguments
     *
     * @param int $ownerId
     * @param string $termId
     * @param string $registrationUrl
     * @param string $file
     * @throws \Exception
     */
    protected function validateArguments(
        $ownerId,
        $termId,
        $registrationUrl,
        $file
    ) {

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

        // Filter registration URL
        if (filter_var($registrationUrl, FILTER_VALIDATE_URL) === false) {
            throw new \Console\Exception\WrongCommandParam(sprintf(
                "Wrong registration url %s",
                $registrationurl
            ));
        }

        // Filter file
        if (empty($file) ||
            !file_exists($file)
        ) {
            throw new \Console\Exception\WrongCommandParam(sprintf(
                "The file `%s` not exists",
                $file
            ));
        }
    }
}