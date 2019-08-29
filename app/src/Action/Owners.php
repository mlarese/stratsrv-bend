<?php

namespace App\Action;


use App\Entity\Config\ActionHistory;
use App\Entity\Config\Owner;
use App\Entity\Config\User;
use App\Entity\Privacy\Configuration;
use App\Entity\Privacy\Domain;
use App\Entity\Privacy\Operator;
use App\Entity\Proxy\OwnerProxy;
use App\Resource\DomainResource;
use App\Resource\OperatorResource;
use App\Resource\OwnerResource;
use App\Resource\UserExistException;
use App\Resource\UserResource;
use function array_push;
use DateTime;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use function is_array;
use function print_r;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Resource\CompanyExistException;
use App\Resource\EmailExistException;

class Owners extends AbstractAction
{
    /**
     * @param $ownerId
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createOwnerDb ($db) {
        return $this->executeConfigSql("CREATE DATABASE $db;");
        $this->executeConfigSql("GRANT ALL PRIVILEGES ON $db.* to $user@localhost IDENTIFIED BY '$password';");
        $this->executeConfigSql("GRANT ALL PRIVILEGES ON $db.* to $user@'%' IDENTIFIED BY '$password';");
    }

    /**
     * @return Domain[]
     * @throws ORMException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function ownersStats (Request $request, Response $response, $args) {


        $em = $this->getEmConfig();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('tot', 'tot', 'integer');

        $sql = "SELECT count(active) as tot FROM privacy_config.owner where deleted = 0 and active = 0 ;";
        $query = $em->createNativeQuery($sql, $rsm);
        $totDeactive = $query->getResult();

        $sql = "SELECT count(active) as tot FROM privacy_config.owner where deleted = 0 and active = 1 ;";
        $query = $em->createNativeQuery($sql, $rsm);
        $totActive = $query->getResult();

        $res = [
            "total"=> $totDeactive[0]['tot'] + $totActive[0]['tot'],
            "active"=> $totActive[0]['tot'],
            "notactive"=> $totDeactive[0]['tot']
        ];
        return $response->withJson($res);
    }
    /**
     * @return Domain[]
     * @throws ORMException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function loadAllDomains (Request $request, Response $response, $args) {
        try {
            $ows = $this->getEmConfig()->getRepository(OwnerProxy::class)->findBy(['deleted' => 0]);
            /** @var Domain[] $domains */
            $domains = [];
            /** @var OwnerProxy $o */


            foreach ($ows as $o) {
                $em = $this->getEmPrivacy($o->getId());

                $ds = DomainResource::getDomainRefs($em, $o->getId());
                if (isset($ds) && is_array($ds) && count($ds) > 0) {
                    array_push($domains, ...$ds);
                }
                $em->close();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error loading domains');
        }

        return $response->withJson($this->toJson($domains));
    }
    /**
     * @param $dbName
     * @param $user
     * @param $password
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createDbUser($user, $password, $db) {
        $this->executeConfigSql("CREATE USER '$user'@'localhost' IDENTIFIED BY '$password'");
        $this->executeConfigSql("CREATE USER '$user'@'%' IDENTIFIED BY '$password'");
        $this->executeConfigSql("GRANT SELECT, INSERT, UPDATE, DELETE ON $db.* to $user@localhost IDENTIFIED BY '$password';");
        $this->executeConfigSql("GRANT SELECT, INSERT, UPDATE, DELETE ON $db.* to $user@'%' IDENTIFIED BY '$password';");
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \RuntimeException
     */
    private function createPrivacyTables($db) {
        $sql = "
        CREATE TABLE $db.mailup_list_ttl (
          id int(11) NOT NULL,
          guid varchar(64) COLLATE utf8_unicode_ci NOT NULL,
          created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          expire datetime NOT NULL,
          updated datetime DEFAULT NULL,
          PRIMARY KEY (id),
          KEY mailup_list_ttl_guid (guid),
          KEY mailup_list_expired_values (expire)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        CREATE TABLE $db.mailup_token (
          id int(11) NOT NULL AUTO_INCREMENT,
          alertemail varchar(128) COLLATE utf8_unicode_ci NOT NULL,
          clientid varchar(128) COLLATE utf8_unicode_ci NOT NULL,
          clientsecret varchar(128) COLLATE utf8_unicode_ci NOT NULL,
          token longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
          created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated datetime DEFAULT NULL,
          status int(11) NOT NULL,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

        CREATE TABLE $db.mailup_recipient_ttl (
          id int(11) NOT NULL,
          list int(11) NOT NULL,
          created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          expire datetime NOT NULL,
          updated datetime DEFAULT NULL,
          PRIMARY KEY (id,list),
          KEY mailup_recipient_expired_values (expire)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        CREATE TABLE $db.privacy_attachment (
          uid varchar(128) COLLATE utf8_unicode_ci NOT NULL,
          deleted tinyint(1) NOT NULL DEFAULT '0',
          created datetime DEFAULT CURRENT_TIMESTAMP,
          attachments longtext COLLATE utf8_unicode_ci,
          PRIMARY KEY (uid),
          KEY privacy_created (created)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;   
        CREATE TABLE $db.privacy( id INT AUTO_INCREMENT NOT NULL, created DATETIME DEFAULT CURRENT_TIMESTAMP, privacy LONGTEXT DEFAULT NULL , privacy_id INT NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, INDEX privacy_history_created (created), INDEX privacy_history_privacy_id (privacy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;      
        CREATE TABLE $db.domain (name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, active TINYINT(1) DEFAULT '1' NOT NULL, deleted TINYINT(1) DEFAULT '0' NOT NULL, INDEX domain_active (active), PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.action_history (
            id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, 
            description VARCHAR(255) NOT NULL, 
            date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            history LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            properties LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            user_name VARCHAR(50) NOT NULL, 
            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.term (
            uid VARCHAR(128) NOT NULL, 
            options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)',
            name VARCHAR(255) NOT NULL, 
            paragraphs LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            version INT DEFAULT NULL, 
            status VARCHAR(30) NOT NULL, 
            published DATETIME DEFAULT NULL, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP, modified DATETIME DEFAULT CURRENT_TIMESTAMP, suspended DATETIME DEFAULT NULL, deleted TINYINT(1) DEFAULT '0' NOT NULL, 
            INDEX term_created (created), 
            INDEX version (version), 
            INDEX term_suspended (suspended), INDEX term_published (published), INDEX term_modified (modified), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.term_history (
            id INT AUTO_INCREMENT NOT NULL, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP, 
            term LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            term_uid VARCHAR(128) NOT NULL, 
            modifier INT NOT NULL, type VARCHAR(255) NOT NULL, 
            description VARCHAR(255) NOT NULL, 
            version INT DEFAULT NULL, 
            INDEX term_history_created (created), 
            INDEX version (version), 
            INDEX term_history_modifier (modifier), INDEX term_history_term_uid (term_uid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.privacy_entry( 
            uid VARCHAR(128) NOT NULL, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP, 
            language VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
            email VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, 
            status VARCHAR(20) DEFAULT NULL, 
            version INT DEFAULT NULL, 
            note text CHARACTER SET utf8,
            ref VARCHAR(100) DEFAULT NULL, 
            surname VARCHAR(100) NOT NULL, 
            page varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            properties LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            form LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            crypted_form LONGTEXT DEFAULT NULL, 
            privacy LONGTEXT DEFAULT NULL, 
            privacy_flags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            term_id VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, 
            site VARCHAR(255) NOT NULL, ip VARCHAR(100) DEFAULT NULL, 
            telephone VARCHAR(120) DEFAULT NULL, 
            deleted TINYINT(1) DEFAULT '0' NOT NULL, 
            INDEX privacy_created (created), 
            INDEX privacy_name_surname (name, surname), 
            INDEX privacy_ref (ref), 
            INDEX privacy_version (version), 
            INDEX privacy_term_id (term_id), 
            INDEX privacy_language (language),  
            INDEX privacy_page (page),  
            INDEX privacy_status (status), 
            INDEX privacy_email (email), 
            PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.operator (
            id INT NOT NULL, 
            name VARCHAR(80) NOT NULL, 
            surname VARCHAR(80) NOT NULL, 
            zip VARCHAR(10) DEFAULT NULL, 
            email VARCHAR(150) DEFAULT NULL, 
            telephone VARCHAR(100) DEFAULT NULL, 
            city VARCHAR(100) DEFAULT NULL, 
            address VARCHAR(255) DEFAULT NULL, 
            role VARCHAR(50) NOT NULL, 
            profile LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            period_from DATETIME NOT NULL, 
            period_to DATETIME NULL, 
            domains LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            acl longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)',
            deleted TINYINT(1) DEFAULT '0' NOT NULL, 
            active TINYINT(1) DEFAULT '1' NOT NULL, 
            INDEX operator_name (name), INDEX operator_role (role), INDEX operator_period_from (period_from), INDEX operator_period_to (period_to), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.term_page (term_uid VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, page VARCHAR(255) NOT NULL, deleted TINYINT(1) DEFAULT '0' NOT NULL, PRIMARY KEY(term_uid, domain, page)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.treatment (
            code VARCHAR(30) NOT NULL, 
            name VARCHAR(255) NOT NULL,
            group_code varchar(60) DEFAULT NULL, 
            options longtext COMMENT '(DC2Type:json)',
            note LONGTEXT DEFAULT NULL, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            creator LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json)', 
            history longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)',
            deleted TINYINT(1) DEFAULT '0' NOT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        CREATE TABLE $db.user_request( uid varchar(128) COLLATE utf8_unicode_ci NOT NULL, mail varchar(100) COLLATE utf8_unicode_ci NOT NULL, note longtext COLLATE utf8_unicode_ci NOT NULL, type varchar(50) COLLATE utf8_unicode_ci NOT NULL, created datetime DEFAULT CURRENT_TIMESTAMP, domain varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, site varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, status varchar(50) COLLATE utf8_unicode_ci NOT NULL, history longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', flow longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', last_access datetime DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (uid), KEY user_request_mail (mail), KEY user_request_type (type), KEY user_request_domain_site (domain,site), KEY user_request_created (created), KEY user_request_last_access (domain,last_access), KEY user_request_status (domain,status)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        CREATE TABLE $db.treatment_history( id int(11) NOT NULL AUTO_INCREMENT, created datetime DEFAULT CURRENT_TIMESTAMP, treatment longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', treatment_code varchar(128) COLLATE utf8_unicode_ci NOT NULL, modifier int(11) NOT NULL, type varchar(255) COLLATE utf8_unicode_ci NOT NULL, description varchar(255) COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY (id), KEY treatment_history_created (created), KEY treatment_history_modifier (modifier), KEY treatment_history_treatment_code (treatment_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;    
        CREATE TABLE $db.dictionary( code varchar(50) COLLATE utf8_unicode_ci NOT NULL, description varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, data longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', PRIMARY KEY (code)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        CREATE TABLE $db.configuration( code varchar(50) COLLATE utf8_unicode_ci NOT NULL, description varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, data longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', PRIMARY KEY (code)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        CREATE TABLE $db.group( code varchar(50) COLLATE utf8_unicode_ci NOT NULL, description varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, acl longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', options longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', profile longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:json)', PRIMARY KEY (code)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";

        $this->executeConfigSql($sql);
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     *
     * @return mixed
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function newOwner($request, $response, $args) {
        $newOwner = new Owner();
        try {
            $body = $request->getParsedBody();
            $userName = $this->getAttribute('user',$body,true);
            $userPassword = $this->getAttribute('password',$body, true);
            $language = $this->getAttribute('language',$body);
            $email = $this->getAttribute('email',$body, true);
            $name =  $this->getAttribute('name',$body);
            $surname=$this->getAttribute('surname',$body);
            $city = $this->getAttribute('city',$body);
            $zip = $this->getAttribute('zip',$body);
            $address=$this->getAttribute('address',$body);
            $country=$this->getAttribute('country',$body);
            $profile=$this->getAttribute('profile',$body);
            $county=$this->getAttribute('county',$body);
            $company=$this->getAttribute('company',$body,true);

            $domains=$this->getAttribute('domains',$body);

        } catch(Exception $e) {
            return $response->withStatus(500, 'Missing parameter ' . $e->getMessage());
        }


        $this->getEmConfig()->getConnection()->beginTransaction();

        try {
            /***************************************************
             * creating Owner
             **************************************************/
            $ownRes = new OwnerResource($this->getEmConfig());
            $newOwner = $ownRes->insert($email,$company,$name,$surname, $city,$zip,$address,$county,$county,$language,$profile );
            $currentOwnerId = $newOwner->getId();

            /***************************************************
             * creating User
             **************************************************/
            $userRes = new UserResource($this->getEmConfig());
            $md5UserPassword = md5($userPassword);
            $newUser = $userRes->insert($userName, $md5UserPassword, 'owners', $currentOwnerId, $company . ' owner');
            $currentUserId = $newUser->getId();
            $dynaDb = $this->getGuestDbCredentials($currentOwnerId);

            $newDb =  $dynaDb['db'] ;
            $dbUser = $dynaDb['user'] ;
            $dbpwd = $dynaDb['password']  ;

            /***************************************************
             * creating Privacy db
             **************************************************/
            $done = $this->createOwnerDb($newDb);

            $this->createDbUser($dbUser, $dbpwd, $newDb);
            /***************************************************
             * adding tables to Privacy db
             **************************************************/

            $this->createPrivacyTables($newDb);
            /***************************************************
             * creating privacy owner Operator
             **************************************************/
            $prEm = $this->getEmPrivacy($currentOwnerId);
            $prEm->getConnection()->beginTransaction();

            $operatorRes = new OperatorResource($prEm);
            $newOperator = $operatorRes->insert($currentUserId,'owner', new \DateTime(),$email, $name, $surname);

            try {
                if(isset($body['domains'])){
                    $domRes = new DomainResource($prEm);
                    $domRes->merge($body['domains']);
                }

            } catch(Exception $e) {
                // return $response->withStatus(500, 'Error updating record');
            }


            $ud = $this->getUserData($request);
            $ah = new ActionHistory();
            $ah->setType('owner_creation')
                ->setDate(new DateTime())
                ->setDescription("Created owner $currentOwnerId - " .   $newOwner->getCompany())
                ->setUserName($ud->userName);

            $this->getEmConfig()->persist($ah);
            $this->getEmConfig()->flush();
            $this->getEmConfig()->commit();
            $prEm->commit();

            return $response->withJson($this->toJson($newOwner));

        } catch (UserExistException $e) {
            $this->getEmConfig()->getConnection()->rollBack();
            return $response->withStatus(500, $e->getMessage());
        } catch (DBALException $e) {
            echo $e->getMessage();
            $this->getEmConfig()->getConnection()->rollBack();
            return $response->withStatus(500, "DBALException creating owner ");
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            $this->getEmConfig()->getConnection()->rollBack();
            if(isset($prEm))
                $prEm->getConnection()->rollBack();
            return $response->withStatus(500, "OptimisticLockException creating owner ");
        } catch (ORMException $e) {
            echo $e->getMessage();
            $this->getEmConfig()->getConnection()->rollBack();
            if(isset($prEm))
                $prEm->getConnection()->rollBack();
            return $response->withStatus(500, "ORMException creating owner ");
        } catch (CompanyExistException $e) {
            echo $e->getMessage();
            $this->getEmConfig()->getConnection()->rollBack();
            if(isset($prEm))
                $prEm->getConnection()->rollBack();
            return $response->withStatus(500, "Company Exist Exception");
        } catch (EmailExistException $e) {
            echo $e->getMessage();
            $this->getEmConfig()->getConnection()->rollBack();
            if(isset($prEm))
                $prEm->getConnection()->rollBack();
            return $response->withStatus(500, "Email Exist Exception");
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->getEmConfig()->getConnection()->rollBack();
            if(isset($prEm))
                $prEm->getConnection()->rollBack();

            // echo $e->getMessage();
            return $response->withStatus(500, "Exception creating owner " .$e->getMessage());
        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getOwners($request, $response, $args) {
        $owR = $this->getEmConfig()->getRepository(Owner::class);
        $owners = [];

        try{
            $owners = $owR->findBy(["deleted"=>0]);

        }catch(Exception $e) {
            echo($e->getMessage());
            return $response->withStatus(500, 'Exception loading owners');
        }

        return $response->withJson( $this->toJson($owners));
    }

    private function getEmptyLayout () {
        return  ["default"=>
                    [
                        "code"=>"default",
                        "logo"=>"",
                        "structure"=>"",
                        "text"=>[
                            "it"=>"",
                            "en"=>"",
                            "de"=>""
                        ],
                        "subject"=>[
                            "it"=>"",
                            "en"=>"",
                            "de"=>""
                        ],
                        "domain"=>"",
                        "termid"=>""
                    ]
                ];

    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setOwnerLayouts($request, $response, $args) {
        try{
            if(isset($args['ownerId'])) $ownerId = $args['ownerId'];
            else  $ownerId = $this->getOwnerId($request);



            /** @var EntityManager $emp */
            $emp = $this->getEmPrivacy($ownerId);

            $currId = 'dbloptin-email-template';
            /** @var Configuration $rec */
            $rec = $emp->find(Configuration::class, $currId);

            if($rec==null) {
                $rec = new Configuration();
                $rec->setCode('dbloptin-email-template')
                    ->setDescription('Layout per double optin')
                ;
            }

            $body = $request->getParsedBody();
            $rec->setData($body);

            $emp->merge($rec);
            $emp->flush();

        }catch(Exception $e) {
            echo($e->getMessage());
            return $response->withStatus(500, 'Exception saving layouts');
        }

        return $response->withJson( $this->toJson($this->success()));
    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getOwnerLayouts($request, $response, $args) {
        try{
            if(isset($args['ownerId'])) $ownerId = $args['ownerId'];
            else  $ownerId = $this->getOwnerId($request);

            /** @var EntityManager $emp */
            $emp = $this->getEmPrivacy($ownerId);

            $currId = 'dbloptin-email-template';
            /** @var Configuration $rec */
            $rec = $emp->find(Configuration::class, $currId);

            if($rec==null) {
                $layouts = $this->getEmptyLayout();
            } else {
                $data = $rec->getData();
                if($data==null)
                    $layouts = $this->getEmptyLayout();
                else
                    $layouts = $data;
            }
        }catch(Exception $e) {
            echo($e->getMessage());
            return $response->withStatus(500, 'Exception loading layouts');
        }

        return $response->withJson( $this->toJson($layouts));
    }

    protected function emptyProfile () {
        return ["marker"=>"marker"];
    }
    protected function dictionary () {
        return;
    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getOwnerById($request, $response, $args) {
        $currId = $args['id'];

        /**
         * @var EntityManager $em
         */
        $em = $this->getEmConfig();

        /** @var EntityManager $em */
        $emp = $this->getEmPrivacy($currId);

        try {
            /** @var Owner $res */
            $res = $em->find(Owner::class, $currId);
            $curp = $res->getProfile();
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'OptimisticLockException adding domains');
        } catch (TransactionRequiredException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'TransactionRequiredException adding domains');
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException adding domains');
        }catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception adding domains');
        }

        if(!isset($curp)) {
            $res->setProfile( $this->emptyProfile());
        }

        if(!$res) {
            return $response->withStatus(500, 'Operator Not found');
        }


        try {
            $domainsRes = new DomainResource($emp);
            $domains = $domainsRes->findAll();
            if ($domains)
                $res->setDomains($domains);
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting domains');
        }


        $js = $this->toJson($res);
        return $response->withJson( $js);
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function updateOwner($request, $response, $args) {
        $currId = $args['id'];
        /**
         * @var EntityManager $em
         */
        $em = $this->getEmConfig();

        /**
         * @var Owner $res
         */
        $res = $em->find(Owner::class, $currId);
        $oldRes = clone $res;
        if(!$res) {
            return $response->withStatus(500, 'Not found');
        }

        try {
            $body = $request->getParsedBody();
            $language = $this->getAttribute('language',$body);
            $email = $this->getAttribute('email',$body, true);
            $name =  $this->getAttribute('name',$body);
            $surname=$this->getAttribute('surname',$body);
            $city = $this->getAttribute('city',$body);
            $zip = $this->getAttribute('zip',$body);
            $address=$this->getAttribute('address',$body);
            $country=$this->getAttribute('country',$body);
            $profile=$this->getAttribute('profile',$body);
            $county=$this->getAttribute('county',$body);
            $company=$this->getAttribute('company',$body,true);

            $domains=$this->getAttribute('domains',$body);

            $res
                ->setName($name)
                ->setSurname($surname)
                ->setEmail($email)
                ->setCity($city)
                ->setAddress($address)
                ->setZip($zip)
                ->setLanguage($language)
                ->setCountry($country)
                ->setCounty($county)
                ->setProfile($profile)
            ;

        } catch(Exception $e) {
            return $response->withStatus(500, 'Missing parameter ' . $e->getMessage());
        }


        try {

            if(isset($body['domains'])){
                $em = $this->getEmPrivacy($currId);

                $domRes = new DomainResource($em);
                $domRes->merge($body['domains']);
            }

            $this->getEmConfig()->merge($res);


            $ud = $this->getUserData($request);

            $ah = new ActionHistory();
            $ah->setType('owner_modified')
                ->setDate(new DateTime())
                ->setDescription("Modified owner " . $res->getId() . " - " .   $res->getCompany())
                ->setUserName($ud->userName)
                ->setHistory($this->toJson($oldRes))
            ;
            $this->getEmConfig()->persist($ah);
            $this->getEmConfig()->flush();


        } catch(Exception $e) {
            return $response->withStatus(500, 'Error updating record');
        }

        $js = $this->toJson($this->success());
        return $response->withJson( $js);
    }

    public function updateOwnerProfile($request, $response, $args) {
        $currId = $args['id'];
        /**
         * @var EntityManager $em
         */
        $em = $this->getEmConfig();

        /**
         * @var Owner $res
         */
        $res = $em->find(Owner::class, $currId);

        if(!$res) {
            return $response->withStatus(500, 'Not found');
        }

        try {
            $body = $request->getParsedBody();
            $profile=$this->getAttribute('profile',$body);
            $res
                ->setProfile($profile)
            ;

        } catch(Exception $e) {
            return $response->withStatus(500, 'Missing parameter ' . $e->getMessage());
        }

        try {
            $this->getEmConfig()->merge($res);
            $this->getEmConfig()->flush();
        } catch(Exception $e) {
            return $response->withStatus(500, 'Error updating record');
        }

        $js = $this->toJson($this->success());
        return $response->withJson( $js);
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws ORMException
     */
    public function getDomains ($request, $response, $args) {
        if(isset($args['ownerId'])) $ownerId = $args['ownerId'];
        else  $ownerId = $this->getOwnerId($request);

        /** @var EntityManager $em */
        $emp = $this->getEmPrivacy($ownerId);

        $domRes = new DomainResource($emp);

        $currentDomains = $domRes->findAll();
        $js = $this->toJson($currentDomains);
        return $response->withJson( $js);
    }
}
