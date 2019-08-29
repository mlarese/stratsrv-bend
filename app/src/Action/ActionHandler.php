<?php
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 02/05/18
 * Time: 14.45
 */

namespace App\Action;

use App\Entity\Upgrade\SubscriberDomainPath;
use Slim\Container;

class ActionHandler
{

    protected $config = null;

    protected $parameters = null;
    /**
     * @var Container
     */
    protected $container = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param null $config
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * @param null $config
     */
    public function setParameters($config): void
    {
        $this->parameters = $config;
    }


    /**
     * @param $param SubscriberDomainPath
     */
    public function execute($param){

        if($this->config){
            if($this->container->offsetExists($this->config->getFactoryClass())){
                try{

                    $service = $this->container->get($this->config->getFactoryClass());

                    $service->setConfig($this->config->getParameters());

                    $service->setParameters($this->parameters);

                    $command = $this->config->getCommand();
                    if(empty($command)){
                       $command = 'execute';
                    }

                    $service->$command($param);

                }
                catch (\Exception $e){

                }
            }
        }

    }
}