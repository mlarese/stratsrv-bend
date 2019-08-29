<?php
namespace App\Action;

use App\Action\Bi\BiAgenationTrait;
use App\Action\Bi\BiDemograficTrait;
use App\Action\Bi\BiMonthYearTrait;
use App\Action\Bi\BiQBaseTrait;
use App\Action\Bi\BiReturnsTrait;
use App\Entity\Privacy\Configuration;
use function count;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use function json_decode;
use Slim\Http\Request;
use Slim\Http\Response;


class Bi extends AbstractAction
{
    use BiDemograficTrait;
    use BiAgenationTrait;
    use BiMonthYearTrait;
    use BiReturnsTrait;
    use BiQBaseTrait;


    const BI_QUERIES_LIST = 'bi-queries-list';

    private function generateQueryFilterOptionsPax () {
        return [
            ["value"=>'Adult with child', "label" => 'Adulti con bambino'],
            ["value"=>'Couples', "label"  => 'Coppie'],
            ["value"=>'Couples with child' , "label" => 'Coppie con bambino'],
            ["value"=>'Families' , "label" => 'Famiglie'],
            ["value"=>'Big family' , "label" => 'Famiglia numerosa'],
            ["value"=>'Single' , "label" => 'Singoli'],
            ["value"=>'Three Adults' , "label" => 'Tre adulti']

        ];
    }
    private function generateQueryFilterOptionsLeadtime () {
        return [
            "Between 1 and 3 days",
            "Between 4 and 7 days",
            "Between 8 and 14 days",
            "Between 15 days and 1 month",
            "Between 1 and 2 months",
            "Between 2 and 3 months",
            "Between 3 and 6 months",
            "Over 6 Months"
        ];
    }
    private function generateQueryFilterOptionsOrigin () {
        return [
            ["value"=>"OB","label" =>"Online - Booking"],
            ["value"=>"PMS_M","label" =>"PMS Terzi - PMSONE - Manuale"],
            ["value"=>"CRO" ,"label" => "CRO"]
            // "PMS Terzi - PMSONE - Manuale",
            // "OTA",
            // "CRO - Walkin",
            // "CRO - Telefono",
            // "CRO - Form",
            // "CRO - Email"
        ];
    }
    private function generateQueryFilterOptionsChannel () {
        return [
          "C",
          "D",
          "G",
          "I",
          "L",
          "M",
          "R",
          "T",
          "V"
        ];
    }
    private function generateQueryFilterOptionsLanguage ($em,$portalCode, $structureId) {
        $structureWhere = '';
        // if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = "SELECT  distinctrow raw.reservation_guest_language
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw  ON SUBSTRING_INDEX(fact.related_reservation_code,'-',-1) = raw.reservation_id
            
            WHERE $structureWhere (not raw.reservation_guest_language is null and not raw.reservation_guest_language='-')
            ORDER BY raw.reservation_guest_language";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('reservation_guest_language', 'language', 'string');
        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function generateQueryFilterOptionsCountry ($em,$portalCode, $structureId) {
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = " SELECT  distinct country FROM abs_datamart.dm_reservation_$portalCode dm where $structureWhere not  country is null  order by country";
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('country', 'country', 'string');
        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }


    private function generateQueryFilterOptionsProduct ($em,$portalCode, $structureId) {
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";
        $sql = "SELECT  distinct room_code FROM abs_datamart.dm_reservation_$portalCode dm where $structureWhere not  room_code is null     order by room_code";
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('room_code', 'product', 'string');
        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    private function generateQueryFilterOptions ($ownerId) {
        $res = [];
        $em = $this->getContainer()->get('em-bi');

        $options = Auth::getOptionsSt($ownerId);
        $structures = $this->getStructures($em, $ownerId);
        $structure = $structures[0];

        if($options['isPortal'])
            $structure = [
                'portal_code'=>$structure['portal_code'],
                'structure_id'=>null,
                'portal_id'=>$structure['portal_id']
            ];
        else
            $structure = $structures[0];

        ;

        $portalCode = $structure['portal_code'];
        $structureId = $structure['structure_id'];

        $res['channel'] = $this->generateQueryFilterOptionsChannel();
        $res['leadtime'] = $this->generateQueryFilterOptionsLeadtime();
        $res['origin'] = $this->generateQueryFilterOptionsOrigin();
        $res['product'] = $this->generateQueryFilterOptionsProduct($em, $portalCode, $structureId);
        $res['paxType'] = $this->generateQueryFilterOptionsPax();
        $res['language'] = $this->generateQueryFilterOptionsLanguage($em, $portalCode, $structureId);
        $res['country'] = $this->generateQueryFilterOptionsCountry($em, $portalCode, $structureId);

        return $res;
    }

    public function retrieveQueryFilterOptionsTest (Request $request, Response $response, $args) {
        $structureId = '36';
        $portalCode = 'res';
        $ownerId = 9;

        try {
            $res = $this->generateQueryFilterOptions($ownerId);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $response->withJson($this->toJson($res));
    }

    public function retrieveQueryFilterOptions (Request $request, Response $response, $args) {
        try {
            $ownerId = $this->getOwnerId($request);
            $em =$this->getEmPrivacy($ownerId);

            $id = 'bi-query-filters-options';

            /** @var Configuration $rec */
            $rec = $em->find(Configuration::class, $id);
            if(!isset($rec)) {
                $rec = new Configuration();
                $rec->setCode($id)
                    ->setDescription('Query filter values')
                    ->setData($this->generateQueryFilterOptions($ownerId))
                ;
            }

            $list=$rec->getData();
            return $response->withJson($this->toJson($list));
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error loading options');
        }

    }
    public function retrieveResultListRecord (Request $request, Response $response, $args) {
        try {
            $ownerId = $this->getOwnerId($request);
            $em =$this->getEmPrivacy($ownerId);

            $id = $args['id'];

            /** @var Configuration $rec */
            $rec = $em->find(Configuration::class, $id);
            if(!isset($rec)) {
                return $response->withStatus(500, 'Error loading result - record not found');
            }

            $list=$rec->getData();

            return $response->withJson($this->toJson($list));


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error loading results');
        }
    }


    public function retrieveResultList (Request $request, Response $response, $args) {
        try {
            $ownerId = $this->getOwnerId($request);
            $em =$this->getEmPrivacy($ownerId);

            /** @var Configuration $rec */
            $rec = $em->find(Configuration::class, self::BI_QUERIES_LIST);
            if(!isset($rec)) $list=[];
            else $list=$rec->getData();

            return $response->withJson($this->toJson($list));


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error loading results');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     *
     * body {'description':string, id: string, creationDate: date, 'description': string}
     */
    public function saveResultList (Request $request, Response $response, $args) {
        try {
            $ownerId = $this->getOwnerId($request);
            $em =$this->getEmPrivacy($ownerId);

            /** @var Configuration $recConfig_queryList */
            $recConfig_queryList = $em->find(Configuration::class, self::BI_QUERIES_LIST);
            if(!isset($recConfig_queryList)) {
                $recConfig_queryList=new Configuration();
                $recConfig_queryList->setCode(self::BI_QUERIES_LIST);
                $recConfig_queryList->setData([]);
                $recConfig_queryList->setDescription('Bi queries list');
            };

            $queryListData = $recConfig_queryList->getData();

            $body = $request->getParsedBody();

            $list_newRecord = [
                'id'=>$body['id'],
                'creationDate'=>new \DateTime(),
                'description'=>$body['description']
            ];
            $queryListData[] = $list_newRecord;
            $recConfig_queryList->setData($queryListData);

            $recConfig_biresult = new Configuration();
                $recConfig_biresult->setCode($body['id']);
                $recConfig_biresult->setData([
                    'data'=>$body['data'],
                    'filter'=>$body['filter']
                ]);
                $recConfig_biresult->setDescription($body['description']);

            $em->merge($recConfig_queryList);
            $em->merge($recConfig_biresult);
            $em->flush();

            return $response->withJson($this->toJson($this->success()));


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error loading results');
        }
    }

    public function ownerPing (Request $request, Response $response, $args) {

        try {
            $em =$this->getEmConfig();
            $rep =$em->getRepository(Privacy::class);
            $privacy = $rep->findBy();
            return $response->withJson($this->toJson($privacy));


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }
    }
    public function ownerPrivacy (Request $request, Response $response, $args) {

        try {
            $id = $args['id'];
            $privacy = $this->getEmConfig();
            $privacy->getRepository(Privacy::class)->find($id);
            return $response->withJson( $this->toJson($privacy));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }

    }
    public function ownerPrivacies (Request $request, Response $response, $args) {

        try {
            $em =$this->getEmConfig();
            $rep =$em->getRepository(Privacy::class);
            $privacy = $rep->findBy();
            return $response->withJson($this->toJson($privacy));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }

    }
    public function ownerPrivacyAdd (Request $request, Response $response, $args) {

        try {
            $body = $request->getParsedBody();

            $this->getEmConfig()->persist();
            $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Missing Parameter');
        }

    }

    public function ownerVerifyFlags (Request $request, Response $response, $args) {

        try {
            $id = $args['id'];
            $privacy = $this->getEmConfig();
            $privacy->getRepository(Privacy::class)->find($id);
            return $response->withJson( $this->toJson($privacy));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }

    }

    public function ownerProfile (Request $request, Response $response, $args) {

        try {
            $id = $args['id'];
            $privacy = $this->getEmConfig();
            $privacy->getRepository(Privacy::class)->find($id);
            return $response->withJson( $this->toJson($privacy));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }

    }

    public function ownerSearch (Request $request, Response $response, $args) {

        try {
            $body = $request->getParsedBody();

            $this->getEmConfig()->persist();
            $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Missing Parameter');
        }

    }

    public function ownerSearchCollection (Request $request, Response $response, $args) {

        try {
            $id = $args['id'];
            $privacy = $this->getEmConfig();
            $privacy->getRepository(Privacy::class)->find($id);
            return $response->withJson( $this->toJson($privacy));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }

    }

    public function ownerSaveCollection (Request $request, Response $response, $args) {
        try {

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error searching owners');
        }

    }


    public function retrieveDimensions (Request $request, Response $response, $args) {
        try {
            if (isset($args['ownerId']))
                $ownerId = $args['ownerId'];
            else
                $ownerId = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmBi($ownerId);
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('code', 'code');
            $rsm->addScalarResult('data', 'data');
            $query = $em->createNativeQuery('SELECT code, data FROM dimensions', $rsm);
            $result = $query->getResult();
            // die("done $ownerId");
            foreach ($result as &$record) {
                $record['data'] = json_decode($record['data']);
            }
            return $response->withJson($result);
        } catch (Exception $e) {
            echo $e->getMessage();

            return $response->withStatus(500, 'Error retriving data');
        }
    }

    private function getStructures(EntityManager $em, $ownerId ) {
        $sql = "
            SELECT * FROM abs_datawarehouse.sys_owners_map where owner_id = $ownerId and active = 1;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('portal_code', 'portal_code');
        $rsm->addScalarResult('structure_id', 'structure_id');
        $rsm->addScalarResult('structure_name', 'structure_name');
        $rsm->addScalarResult('portal_id', 'portal_id');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }


    public function retrieveDatamart (Request $request, Response $response, $args) {

        try {
            $dataDomain = $args['domain'];

            if(isset($args['ownerId']))
                $ownid = $args['ownerId'];
            else
                $ownid = $this->getOwnerId($request);

            /** @var EntityManager $emDirectBi */
            $emDirectBi = $this->getContainer()->get('em-bi');
            $structures = $this->getStructures($emDirectBi, $ownid);
            $biResponse = [];
            $result = [];

            $privacyEm = $this->getEmPrivacy($ownid);
            $options = Auth::getOptionsSt($ownid);


            if(count($structures) == 0) {
                return $response->withStatus(500, 'No configured structure');
            }

            $structure = $structures[0];

            if($options['isPortal'])
                $structure = [
                    'portal_code'=>$structure['portal_code'],
                    'structure_id'=>null,
                    'portal_id'=>$structure['portal_id']
                    ];
            else
                $structure = $structures[0];

            switch ($dataDomain) {
                case 'demografic': $biResponse = $this->biResponseDemografic($structure, $emDirectBi); break;
                case 'agenation': $biResponse = $this->biResponseAgeNation($structure, $emDirectBi); break;
                case 'monthyear': $biResponse = $this->biResponseMonthYear($structure, $emDirectBi); break;
                case 'returns': $biResponse = $this->biResponseReturns($structure, $emDirectBi); break;
                case 'qbase': $biResponse = $this->biResponseQBase($structure, $emDirectBi, $privacyEm, $request); break;
            }


            return $response->withJson($biResponse);

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error retriving data');
        }
    }
}
