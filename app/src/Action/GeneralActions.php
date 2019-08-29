<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 9/13/18
 * Time: 12:04 PM
 */

namespace App\Action;


use App\Entity\Config\CustomerCare;
use App\Entity\Config\Owner;
use App\Entity\Config\User;
use App\Entity\Privacy\Domain;
use App\Entity\Privacy\Operator;
use Slim\Http\Request;
use Slim\Http\Response;

class GeneralActions extends AbstractAction
{
    public function setNoMoreActiveOwners (Request $request, Response $response, $args) {
        $em = $this->getEmConfig();
        $rep = $em->getRepository(Owner::class);
        $owners = $rep->findBy(['deleted'=>false, 'active'=>true]);

        /** @var Owner $record */
        foreach($owners as $record) {
            $record->setActive(false);
            $record->setAddress('new address');
            $em->merge($record);
        }

        $em->flush();

        return $response->withJson($this->success());
    }

    public function getOwnersFW(Request $request, Response $response, $args) {
        $em = $this->getEmConfig();
        $qb = $em->createQueryBuilder();

        $owners = $qb
                    ->select(['o.name', 'o.surname', 'o.email'])
                    ->from(Owner::class,'o')
                    ->where('o.deleted',false)
                    ->andWhere('o.active', true)
                    ->orderBy('o.id');

    }

    public function getDomainFW(Request $request, Response $response, $args) {
        $em = $this->getEmConfig();
        $qb = $em->createQueryBuilder();

        $domains = $qb
                        ->select(['o.name', 'description'])
                        ->from(Domain::class,'o')
                        ->orderBy('o.id');
    }

    public function findUserEmail(Request $request, Response $response, $args) {
        $em = $this->getEmConfig();
        $rep = $em->getRepository(User::class);
        $users = $rep->findBy(['deleted'=>false, 'active'=>true]);

        /**
         * @var User $user
         */
        foreach ($users as $user) {
            if($user->getOwnerId()===0 ) {
                /** @var CustomerCare $cc */

                $cc = $em->find(CustomerCare::class, $user->getId());
                $email = $cc->getEmail();
            }else {
                $emp = $this->getEmPrivacy($user->getOwnerId());
                /** @var Operator $operator */
                $operator = $emp->find(Operator::class, $user->getId());
                $email = $operator->getEmail();
            }

        }
        return $response->withJson($this->success());
    }

    public function getOwnerById(Request $request, Response $response, $args) {

        // get /api/test/owner/{ownerId}
        // http://localhost/api/test/owner/1
        $id = $args['ownerId'];
        // http://localhost/api/test/owner?ownerId=1
        $id = $request->getParam('ownerId');

        // se invece il percorse [ protetto
        $id = $this->getOwnerById($request);


        $em = $this->getEmConfig();

        $owner = $em->find(Owner::class, $id);
        $em->getRepository(Owner::class)->find($id);
        return $response->withJson(  $this->toJson($owner));

    }

    public function getOwners(Request $request, Response $response, $args) {

        try {
            $em = $this->getEmConfig();
            $rep = $em->getRepository(Owner::class);
            $owners = $rep->findAll();
            return $response->withJson($this->toJson($owners));
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }
    }


}
