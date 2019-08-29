<?php

namespace Console\Command;


use Symfony\Component\Console\Command\Command;
use Console\Helper\Log as LogHelper;

class Base extends Command
{


    /**
     * @var LogHelper
     */
    protected $log;

    /**
     * Base constructor.
     */
    function __construct()
    {

        $this->log = new LogHelper();
        parent::__construct();
    }



}