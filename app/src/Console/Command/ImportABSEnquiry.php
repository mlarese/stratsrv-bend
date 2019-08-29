<?php

namespace App\Console\Command;

use Doctrine\ORM\EntityManager;
use App\Entity\Privacy\Privacy as PrivacyEntity;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\ABSImport;

class ImportABSEnquiry extends Base
{
    protected function configure()
    {
        $this->setName('import:abs:enquiry')
            ->setDescription('Import enquiry from ABS')
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
                'enquiryUrl',
                InputArgument::REQUIRED,
                'Enquiry URL'
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
        $enquiryUrl = $input->getArgument('enquiryUrl');
        $file = realpath($input->getArgument('file'));
        $this->validateArguments(
            $ownerId,
            $termId,
            $enquiryUrl,
            $file
        );

        // Get import service
        $ABSImport = new ABSImport();
        $ABSImport->importEnquiry(
            $ownerId,
            $termId,
            $enquiryUrl,
            $file
        );

    }

    /**
     * Validate arguments
     *
     * @param int $ownerId
     * @param string $termId
     * @param string $enquiryUrl
     * @param string $file
     * @throws \Exception
     */
    protected function validateArguments(
        $ownerId,
        $termId,
        $enquiryUrl,
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

        // Filter enquiry URL
        if (filter_var($enquiryUrl, FILTER_VALIDATE_URL) === false) {
            throw new \Console\Exception\WrongCommandParam(sprintf(
                "Wrong enquiry url %s",
                $enquiryUrl
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