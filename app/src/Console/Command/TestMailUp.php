<?php

namespace App\Console\Command;

use App\Exception\MailUPRecipientException;
use Console\Exception;
use App\Service\MailUP\Lists as MailUPListService;
use App\Service\MailUP\Groups as MailUPGroupService;
use App\Service\MailUP\Recipient as MailUPRecipientService;
use Console\Command\Base;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMailUp extends Base
{
    protected function configure()
    {
        $this->setName('mail:mailup:test')
            ->setDescription('Test MailUP function (to be use only for DEV)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $serviceList = new MailUPListService();
        $groupList = new MailUPGroupService();
//		// Read list
        $result = $serviceList->readByOwnerId(2);
        $gresult = false;

        foreach ($result as $k => $list) {

            try {
                $gresult = $groupList->createByOwnerId(2, $list['IdList'], "TEST - MMONE - GRUPPO 1", "TEST DESCRIPTION");
            } catch (\Exception $e) {
                $message = json_decode($e->getMessage());

                if($message!==false && $message->ErrorCode == 400){

                    $groups = $groupList->readByOwnerId(2,$list['IdList']);
                    $deleted = false;
                    foreach ($groups as $kh => $groupdata ){
                        if($groupdata['Name']=="TEST - MMONE - GRUPPO 1"){
                            $gresult = $groupdata;
                            // $groupList->deleteByOwnerId(2,$list['IdList'],$groupdata['idGroup']);
                            // $deleted = true;
                            break;
                        }
                    }
                    if($deleted){
                        //      $gresult =$groupList->createByOwnerId(2, $list['IdList'], "TEST - MMONE - GRUPPO 1", "TEST DESCRIPTION");
                    }

                }
            }
            break;
        }

        echo 1;
        if($gresult!==false && isset($gresult['idGroup']) &&
            intval($gresult['idGroup'])>0
        ){

            $service = new MailUPRecipientService();
            $now = new \DateTime('now');
            $now->modify('+1 days');
            try {
                $service->addMultipleRecipientsToLGroupByOwnerId(
                    2,
                    $gresult['idGroup'],$gresult['idList'],
                    [
                        [
                            'Email' => 'test00@test.com',
                            'nome' => 'Giuseppe',
                            'cognome' => 'Donouts',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test01@test.com',
                            'nome' => 'Mattias',
                            'cognome' => 'Constantin',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test02@test.com',
                            'nome' => 'Giulia',
                            'cognome' => 'Pastrel',
                        ],
                    ]
                );
            }
            catch (\Exception $e ){
                print_r($e->getMessage());
            }

        }
        echo 2;



        if($gresult!==false && isset($gresult['idGroup']) &&
            intval($gresult['idGroup'])>0
        ){

            $service = new MailUPRecipientService();
            $now = new \DateTime('now');
            $now->modify('+1 days');
            try{


                $service->addMultipleRecipientsToLGroupByOwnerId(
                    2,
                    $gresult['idGroup'],$gresult['idList'],
                    [
                        [
                            'Email' => 'test001@test.com',
                            'nome' => 'Giuseppe1',
                            'cognome' => 'Donouts',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test002@test.com',
                            'nome' => 'Mattias2',
                            'cognome' => 'Constantin',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test003@test.com',
                            'nome' => 'Giulia3',
                            'cognome' => 'Pastrel',
                        ],
                    ]
                );
            }
            catch (\Exception $e ){
                print_r($e->getMessage());
            }

        }
        echo 3;
        if($gresult!==false && isset($gresult['idGroup']) &&
            intval($gresult['idGroup'])>0
        ){

            $service = new MailUPRecipientService();
            echo 3;
            $now = new \DateTime('now');
            $now->modify('+1 days');
            echo 3;
            try{
                $service->addMultipleRecipientsToLGroupByOwnerId(
                    2,
                    $gresult['idGroup'],$gresult['idList'],
                    [
                        [
                            'Email' => 'test001@test.com',
                            'nome' => 'Giuseppe1',
                            'cognome' => 'Donouts',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test002@test.com',
                            'nome' => 'Mattias2',
                            'cognome' => 'Constantin',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test003@test.com',
                            'nome' => 'Giulia3',
                            'cognome' => 'Pastrel',
                        ],
                    ]
                );
            }
            catch (\Exception $e ){
                print_r($e->getMessage());
            }

        }
        echo 4;
        if($gresult!==false && isset($gresult['idGroup']) &&
            intval($gresult['idGroup'])>0
        ){

            $service = new MailUPRecipientService();
            $now = new \DateTime('now');
            $now->modify('+1 days');
            try {
                $service->addMultipleRecipientsToLGroupByOwnerId(
                    2,
                    $gresult['idGroup'],$gresult['idList'],
                    [
                        [
                            'Email' => 'test004@test.com',
                            'nome' => 'Giuseppe4',
                            'cognome' => 'Donouts',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test005@test.com',
                            'nome' => 'Mattias5',
                            'cognome' => 'Constantin',
                            'expireDate' => $now
                        ],
                        [
                            'Email' => 'test006@test.com',
                            'nome' => 'Giulia6',
                            'cognome' => 'Pastrel',
                        ],
                    ]
                );
            }
            catch (\Exception $e ){
                print_r($e->getMessage());
            }
        }

        echo 5;
    }

}