<?php

namespace App\Action;


use App\DoctrineEncrypt\Encryptors\EncryptorInterface;
use App\Resource\MandatoryFieldMissingException;
use App\Resource\OwnerExistException;
use Closure;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use function print_r;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AbstractAction
{
    protected $container;

    protected $session;

    protected $context;

    /**
     * @return string
     */
    public function getIp () {
        if(isset($_SERVER['HTTP_X_REAL_IP'])) {
            $remoteIp = $_SERVER['HTTP_X_REAL_IP'];
        } else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else {
            $remoteIp = $_SERVER['REMOTE_ADDR'];
        }

        return $remoteIp;
    }

    /**
     * @param $response Response
     */
    public function addP3P ($response) {
        header('P3P: CP="ALL IND DSP COR ADM CONo CUR CUSo IVAo IVDo PSA PSD TAI TELo OUR SAMo CNT COM INT NAV ONL PHY PRE PUR UNI"');
        // $response->withAddedHeader('P3P','CP="ALL IND DSP COR ADM CONo CUR CUSo IVAo IVDo PSA PSD TAI TELo OUR SAMo CNT COM INT NAV ONL PHY PRE PUR UNI"');
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function postActionPrototype($request, $response, $args, Closure $closure, $mandatoryFields = []) {
        try {
            $body = $request->getParsedBody();
            $res = $closure($body);
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, "Server error on closure execution");
        }

        return $response->withJson($this->success());
    }

    /**
     * @param string $token
     * @param $algorithm
     * @return array
     * @throws Exception
     */
    protected function decodeToken(string $token)
    {
        $settings=$this->container->get('settings');
        $auth = $settings['auth'];
        $secret=$auth['secret'];

        $algorithm = ["HS256", "HS512", "HS384"];

        try {
            $decoded = JWT::decode(
                $token,
                $secret,
                (array) $algorithm
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            echo $exception->getMessage();
            throw $exception;
        }
    }

    public function toDateTime($date) {
        if(!isset($date)) {
            return $date;
        }
        if(is_string($date)) {
            $date = new DateTime($date);
        }

        return $date;
    }

    /**
     * @param $obj
     *
     * @return array|bool|float|int|object|string
     */
    public function toJson($obj) {
        $on = new ObjectNormalizer();
        $on->setCallbacks([
            'attachments' => function ($val) {
                if(!$val) return [];
                return json_decode( $val , true);
            }
        ]);
        $on->setCircularReferenceLimit(1);
        $on->setCircularReferenceHandler(function ($object) { return $object->getId(); });

        $dtn = new DateTimeNormalizer();
        $s = new Serializer(array($dtn, $on), array(new JsonEncoder()) );

        return $s->normalize($obj,'json');
    }

    public static function toJsonStatic($obj) {
        $on = new ObjectNormalizer();
        $on->setCallbacks([
            'attachments' => function ($val) {
                if(!$val) return [];
                return json_decode( $val , true);
            }
        ]);
        $on->setCircularReferenceLimit(1);
        $on->setCircularReferenceHandler(function ($object) { return $object->getId(); });

        $dtn = new DateTimeNormalizer();
        $s = new Serializer(array($dtn, $on), array(new JsonEncoder()) );

        return $s->normalize($obj,'json');
    }

    /**
     * @param $hash
     *
     * @return int
     * @throws OwnerExistException
     */
    public function findOwnerIdFromHash($hash) {
        $ownerId = 0;
        for($i=0;$i<900000;$i++) {
            $m = md5($i);
            if($hash === $m) {
                $ownerId = $i;
                break;
            }
        }

        if($ownerId === 0) {
            throw new OwnerExistException('Owner not found');
        }

        return $ownerId;
    }

    /**
     * @param      $name
     * @param      $collection
     * @param bool $mandatory
     *
     * @return null
     * @throws MandatoryFieldMissingException
     */
    protected function getAttribute($name, $collection, $mandatory=false, $default=null) {
        if(isset($collection[$name])) {
            return $collection[$name];
        }

        if($mandatory) {
            throw new MandatoryFieldMissingException("$name not found");
        } else {
            return $default;
        }
    }

    /**
     * AbstractAction constructor.
     *
     * @param $container
     */
    public function __construct( $container) {
        $this->container = $container;
        $this->session = $container->get('session');
        $this->context = $container->get('settings')['applicationContext'];
    }

    protected function getBiDbCredentials($ownerId) {
        $settings = $this->container['settings'];
        $dynaDb = $this->container['dyn-privacy-db'];

        return [
            "db" => 'dwr' . "_$ownerId",
            "user" => $settings[$this->context]['connection']['user'],
            "password" => $settings[$this->context]['connection']['password']
        ];

    }

    protected function getGuestDbCredentials($ownerId) {
        if($ownerId!==null) {


            $dynaDb = $this->container['dyn-privacy-db'];

            return [
                "db" => $dynaDb['db'] . "_$ownerId",
                "user" => $dynaDb['user'] . "_$ownerId",
                "password" => md5($dynaDb['password'] . "Fx8k_${ownerId}_5tFg")
            ];

        }
        else{
            $settings = $this->container['settings'];

            return [
                "db" => $settings[$this->context]['connection']['dbname'],
                "user" => $settings[$this->context]['connection']['user'],
                "password" => $settings[$this->context]['connection']['password']
            ];

        }
    }
    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return EntityManager
     */
    public function getEmConfig(): EntityManager
    {
        return $this->container['em-config'];
    }

    /**
     * @return EncryptorInterface
     */
    public function getEncryptor():EncryptorInterface
    {
        return $this->container['encryptor'];
    }

    /**
     * @return Client
     */
    public function getEmailClient(): Client
    {
        return $this->container['email_client'];
    }
    /**
     * @param $ownerId
     * @param null $user
     * @param null $pwd
     * @return EntityManager
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getEmPrivacy($ownerId, $user=null, $pwd=null)
    {
        return $this->buildEntityManager($ownerId, $user, $pwd);
    }

    public function getEmBi($ownerId)
    {
        return $this->buildBiManager($ownerId);
    }


    /**
     * @param Request $request
     *
     * @return string
     */
    public function getActionUser(Request $request) {
        $ud = $this->getUserData($request);
        $user = 'surfer';

        if(isset($ud)) {
            $user = $ud->user;
        }

        return $user;
    }

    /**
     * @param $request Request
     * @return array
     */
    public function getToken ($request) {
       $token = $request->getAttribute("token");
       return $token;
    }

    /**
     * @param $request Request
     * @return \stdClass
     */
    public function getUserData ($request) {
        $token = $this->getToken($request);
        return  $token['user'];
    }

    /**
     * @param $request Request
     * @return string
     */
    public function getOwnerId ($request) {
        $user = $this->getUserData($request);
        return $user->ownerId;
    }

    /**
     * @param      $ownerId
     * @param null $user
     * @param null $pwd
     *
     * @return EntityManager
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    private function buildBiManager($ownerId) {
        $settings = $this->container['settings'];
        $guestCredentials = $this->getBiDbCredentials($ownerId);

        $dbname = $guestCredentials['db'];
        $user = $guestCredentials['user'];
        $pwd = $guestCredentials['password'];

        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            $settings[$this->context]['meta']['entity_path'],
            $settings[$this->context]['meta']['auto_generate_proxies'],
            $settings[$this->context]['meta']['proxy_dir'],
            $settings[$this->context]['meta']['cache'],
            false
        );


        $connection = array(
            'driver'   => $settings[$this->context]['connection']['driver'],
            'host'     => $settings[$this->context]['connection']['host'],
            'dbname'   => $dbname,
            'user'     => $user,
            'password' => $pwd
        );



        $em = \Doctrine\ORM\EntityManager::create($connection , $config);

        $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_privacy']['encryption_key'])
        );

        $eventManager = $em->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        return $em;
    }

    /**
     * @param      $ownerId
     * @param null $user
     * @param null $pwd
     *
     * @return EntityManager
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    private function buildEntityManager($ownerId) {
        $settings = $this->container['settings'];


        $guestCredentials = $this->getGuestDbCredentials($ownerId);


        $dbname = $guestCredentials['db'];

        $user = $guestCredentials['user'];

        $pwd = $guestCredentials['password'];


        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            $settings[$this->context]['meta']['entity_path'],
            $settings[$this->context]['meta']['auto_generate_proxies'],
            $settings[$this->context]['meta']['proxy_dir'],
            $settings[$this->context]['meta']['cache'],
            false
        );


        $connection = array(
            'driver'   => $settings[$this->context]['connection']['driver'],
            'host'     => $settings[$this->context]['connection']['host'],
            'dbname'   => $dbname,
            'user'     => $user,
            'password' => $pwd
        );



        $em = \Doctrine\ORM\EntityManager::create($connection , $config);

        $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_privacy']['encryption_key'])
        );

        $eventManager = $em->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        return $em;

    }

    protected function success ($options=[]) {
        return ["success"=>true, "options"=>$options];
    }


    protected function error ($options=[]) {
        return ["success"=>false, "options"=>$options];
    }

    /**
     * @param $sql
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeConfigSql($sql) {
        $stmt = $this->getEmConfig()->getConnection()->prepare($sql);
        return $stmt->execute();
    }
}
