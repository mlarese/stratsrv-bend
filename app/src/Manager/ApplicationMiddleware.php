<?php

namespace App\Manager;

use Slim\App;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class ApplicationMiddleware
{

    private static $instance = null;

    /** @var null|App  */
    protected $app = null;

    protected $settings = [];

    protected $applicationRouters = array();

    public static function getInstance() {
        return self::$instance;
    }

    /**
     * @return null|App
     */
    public function getApp(): ?App {
        return $this->app;
    }

    /**
     * @return array
     */
    public function getSettings(): array {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function getApplicationRouters(): array {
        return $this->applicationRouters;
    }


    public function __construct(array $settings = [],\Slim\App $app)
    {
        $this->settings = $settings;
        $this->app = $app;
        self::$instance = $this;
    }

    /**
     * Invoke middleware
     *
     * @param  Request  $request  PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next     Next middleware
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $path='';

        if($request && $request->getUri()){
            $path = $request->getUri()->getPath();

            if($path!='' ){
                $path = explode('/',$path);

                if($path && isset($path[0]) && $path[0] == ''){
                    unset($path[0]);
                    $path =reset($path);
                }

                $context = $this->detectContext($path);

                if(!empty($context)){
                    $request = $request->withAttribute('applicationContext', $context);
                    $this->app->getContainer()->get('settings')->offsetSet('applicationContext', $context);
                }
            }

        }

        // Next middleware
        return $next($request, $response);
    }


    private function detectContext(
        $pathdata
    ){

        if($this->settings && isset($this->settings['apps'])){

            foreach ($this->settings['apps'] as $k => $v) {
                $res = $v($pathdata);

                if($res === true) {

                    return $k;
                }

            }
        }


    }


}
