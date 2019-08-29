<?php

namespace App\Action;

use App\Entity\Upgrade\DomainDisclaimer;
use App\Entity\Upgrade\DomainPath;
use App\Entity\Upgrade\Privacydisclaimer;
use App\Helpers\IP;
use App\Helpers\UrlUtils;
use Doctrine\Common\Collections\Expr\Comparison;
use App\Entity\Privacy\Term;
use App\Entity\Upgrade\SubscriberDomainPath;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Slim\Http\Request;
use Slim\Http\Response;

class Subscribers extends AbstractAction
{
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function allow($request, $response, $args)
    {

        if (count($args) !== 3) {
            echo 'error 403 - missing parameter';
            return $response->withStatus(403, 'missing parameter');
        }

        if (!isset($args['domainid'])) {
            echo 'error 403 - missing domain parameter';
            return $response->withStatus(403, 'missing parameter');
        }


        if (!isset($args['pathid'])) {
            echo 'error 403 - missing path parameter';
            return $response->withStatus(403, 'missing parameter');
        }

        if (!isset($args['email'])) {
            echo 'error 403 - missing email parameter';
            return $response->withStatus(403, 'missing parameter');
        }

        $domain = $args['domainid'];
        $pathid = $args['pathid'];
        $email = $args['email'];


        /**
         * @var EntityManager $em
         */
        $em = $this->getEmPrivacy(null);

        $subscriber = null;
        try {

            $domainObject = $em->getRepository(DomainPath::class)->find($domain);


            $pathObject = $em->getRepository(Privacydisclaimer::class)->find($pathid);

            if (!$domainObject || !$pathObject) {
                echo 'error 403 - invalid parameter';
                return $response->withStatus(403, 'missing parameter');
            }

            $criteria = new Criteria ();
            $expr = new Comparison("email", Comparison::EQ, $email);
            $criteria->where($expr);
            $expr = new Comparison ("domainpath", Comparison::EQ, $domainObject);
            $criteria->andWhere($expr);
            $expr = new Comparison ("privacydisclaimer", Comparison::EQ, $pathObject);
            $criteria->andWhere($expr);

            $subscriber = $em->getRepository(SubscriberDomainPath::class)->matching(
                $criteria
            );



        } catch (\Exception $e) {
            echo $e->getMessage();

        }

        if ($subscriber->count() > 0) {
            $subscriber = $subscriber->current();

        } else {
            $subscriber = null;
        }

        if (!$subscriber) return false;


        if ($subscriber->getStatus() != 1) {
            /**
             * @var $subscriber SubscriberDomainPath
             */
            $subscriber->setStatus(1);
            $subscriber->setIp(IP::determineHostIP());
            $subscriber->setUpgradedate(new \DateTime());

            $em->persist($subscriber);

            $em->flush();
            /*
             * Verifica azioni connesse
             */

            $action = $subscriber->getDomainpath()->getAction();


            if ($action !== null && $action->count() == 1) {

                $params =  array();
                if ($request->getParam('action') && strpos($request->getParam('action') ,$subscriber->getDomainpath()->getAlternativeredirurl())!==false) {
                    $link = UrlUtils::repair($request->getParams());
                    $params['%%link_subscriber_info%%']  = $link;
                    $params['%%email%%']  = $email;
                }

                $container = $this->getContainer();
                $service = $container->get('action_handler');
                $service->setConfig($action[0]);
                $service->setParameters($params);


                $service->execute($subscriber);

            }


        }



        $response = $response->withAddedHeader('Cache-Control', 'no-cache, must-revalidate');

        $response = $response->withAddedHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        $tmpRedir = $subscriber->getDomainpath()->getRedirurl();
        $jsonRedir = json_decode($tmpRedir,true);
        if($jsonRedir && is_array($jsonRedir) && !empty($jsonRedir)){

            if(isset($jsonRedir[$subscriber->getLanguage()])){

                $tmpRedir =$jsonRedir[$subscriber->getLanguage()];

            }elseif (isset($jsonRedir['en'])){

                $tmpRedir =$jsonRedir['en'];
            }
            else{
                $tmpRedir =$jsonRedir['it'];
            }
        }

        return $response->withRedirect($tmpRedir . "?action=subscribe&lg=" . $subscriber->getLanguage());

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function disallow($request, $response, $args)
    {

        if (count($args) !== 3) {
            echo 'error 403 - missing parameter';
            return $response->withStatus(403, 'missing parameter');
        }

        if (!isset($args['domainid'])) {
            echo 'error 403 - missing domain parameter';
            return $response->withStatus(403, 'missing parameter');
        }


        if (!isset($args['pathid'])) {
            echo 'error 403 - missing path parameter';
            return $response->withStatus(403, 'missing parameter');
        }

        if (!isset($args['email'])) {
            echo 'error 403 - missing email parameter';
            return $response->withStatus(403, 'missing parameter');
        }

        $domain = $args['domainid'];
        $pathid = $args['pathid'];
        $email = $args['email'];


        /**
         * @var EntityManager $em
         */
        $em = $this->getEmPrivacy(null);

        $subscriber = null;
        try {

            $domainObject = $em->getRepository(DomainPath::class)->find($domain);


            $pathObject = $em->getRepository(Privacydisclaimer::class)->find($pathid);

            if (!$domainObject || !$pathObject) {
                echo 'error 403 - invalid parameter';
                return $response->withStatus(403, 'missing parameter');
            }

            $criteria = new Criteria ();
            $expr = new Comparison("email", Comparison::EQ, $email);
            $criteria->where($expr);
            $expr = new Comparison ("domainpath", Comparison::EQ, $domainObject);
            $criteria->andWhere($expr);
            $expr = new Comparison ("privacydisclaimer", Comparison::EQ, $pathObject);
            $criteria->andWhere($expr);

            $subscriber = $em->getRepository(SubscriberDomainPath::class)->matching(
                $criteria
            );


        } catch (\Exception $e) {
            echo $e->getMessage();

        }

        if ($subscriber->count() > 0) {
            $subscriber = $subscriber->current();

        } else {
            $subscriber = null;
        }


        if (!$subscriber) return false;

        /**
         * @var $subscriber SubscriberDomainPath
         */
        $subscriber->setStatus(0);
        $subscriber->setIp(IP::determineHostIP());
        $subscriber->setUpgradedate(new \DateTime());

        $em->persist($subscriber);

        $em->flush();


        $response = $response->withAddedHeader('Cache-Control', 'no-cache, must-revalidate');

        $response = $response->withAddedHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');


        if ($request->getParam('action') && strpos($request->getParam('action') ,$subscriber->getDomainpath()->getAlternativeredirurl())!==false) {

           $link = UrlUtils::repair($request->getParams());


           return $response->withRedirect($link);
        }


        return $response->withRedirect($subscriber->getDomainpath()->getAlternativeredirurl() . "?action=unsubscribe&lg=" . $subscriber->getLanguage());


    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function list($request, $response, $args)
    {


        $js = $this->toJson();
        return $response->withJson($js);
    }


    public function domainList($request, $response, $args)
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->getEmPrivacy(null);

        $subscriber = null;
        try {

            $domainObject = $em->getRepository(DomainPath::class)->findAll();
        } catch (\Exception $e) {
            echo $e->getMessage();

        }

        $response->getBody()->write("<html><body>");

        if ($domainObject && count($domainObject) > 0) {
            /**
             * @var $v DomainPath
             */
            foreach ($domainObject as $k => $v) {
                /**
                 * @var $domainElementObject SubscriberDomainPath
                 */
                $domainElementObject = $em->getRepository(SubscriberDomainPath::class)->findOneBy(array('domainpath' => $v));

                $response->getBody()->write($v->getName() . "\n<br/>");

                $path = 'https://privacy.dataone.online/upgrade/allow/' . $domainElementObject->getDomainpath()->getId() . '/' .
                    $domainElementObject->getPrivacydisclaimer()->getId() . '/%%emailaddress%%';

                $response->getBody()->write($path . "\n<br/>");

                $path = 'https://privacy.dataone.online/upgrade/disallow/' . $domainElementObject->getDomainpath()->getId() . '/' .
                    $domainElementObject->getPrivacydisclaimer()->getId() . '/%%emailaddress%%';

                $response->getBody()->write($path . "\n<br/>");
                $response->getBody()->write("-------------------------------------------------------\n<br/>");


            }
        }
        $response->getBody()->write("</body></html>");
    }
}
