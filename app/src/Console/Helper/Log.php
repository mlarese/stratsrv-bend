<?php

namespace Console\Helper;


use Logger;

class Log {

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Log constructor.
     */
    function __construct()
    {
        $now = new \DateTime('now');

        $this->logger = Logger::getLogger("main");
        $this->logger->configure(array(
            'rootLogger' => array(
                'appenders' => array('default'),
            ),
            'appenders' => array(
                'default' => array(
                    'class' => 'LoggerAppenderFile',
                    'layout' => array(
                        'class' => 'LoggerLayoutPattern',
                        'params' => array(
                            'conversionPattern' => '%date [%level] %message%newline'
                        )
                    ),
                    'params' => array(
                        'file' => sprintf(
                            '%s/%s.log',
                            realpath('/tmp/'),
                            $now->format('Ymd')
                        ),
                        'append' => true
                    )
                )
            )
        ));
    }

    /**
     * Functions wrapper
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(
        $name,
        $arguments = array()
    ) {
        if (count($arguments) == 1)
            $arguments[] = null;
        return $this->logger->$name(
            $arguments[0],
            $arguments[1]
        );
    }
}