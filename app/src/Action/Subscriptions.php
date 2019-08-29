<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 28/08/2018
 * Time: 11:37
 */

namespace App\Action;

use App\Resource\PrivacyResource;
use App\Traits\UrlHelpers;
use Slim\Http\Request;
use Slim\Http\Response;

class Subscriptions extends AbstractAction  {
    use UrlHelpers;
    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function unsubscribeNewsletters(Request $request, Response $response, $args) {

        try {
            $user = $this->getActionUser($request);

            $privaces = null;
            $_k = $request->getParam('_k');
            $params = $this->urlB64DecodeToArray($_k);
            $email = $params['email'];
            $ownerId = $params['ownerId'];

            $em=$this->getEmPrivacy($ownerId);
            $privacyResource = new PrivacyResource($em );
            $termsObj = $privacyResource->privacyRecord($email);

            foreach ($termsObj as $termKey => $domainObj) {
                foreach ($domainObj as $domainKey => $privacyObj) {
                    $privaces[] = $privacyObj;
                }
            }


            $today = new \DateTime();

            // print_r($privaces);
            //die("$email $ownerId");

            foreach ($privaces as &$priv) {
                // flags
                $flags = &$priv['privacyFlags'];
                foreach ($flags as &$value) {
                    if($value['code'] === 'newsletter' || $value['code'] === 'newsletter' ) {
                        $value['selected'] = false;
                        $value['unsubscribe'] = $today;
                        $value['user'] = $user;

                    }
                }

                // trattamenti da informativa accettata
                $term =  &$priv['privacy'];
                $paragraphs =  &$term['paragraphs'];
                foreach ($paragraphs as &$parag) {
                    $treatments = &$parag['treatments'];
                    foreach ($treatments as $key => &$value) {
                        if($value['code'] === 'newsletter' || $value['code'] === 'newsletter' ) {
                            $value['selected'] = false;
                            $value['unsubscribe'] = $today;
                            $value['user'] = $user;
                        }
                    }
                    unset( $parag['text'] );
                }

                $privacyResource->updateFlags($flags, $term, $priv['id']);

            }
            $em->flush();
            return $response->withJson($this->success()) ;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error unsubscribing') ;
        }


    }
}
