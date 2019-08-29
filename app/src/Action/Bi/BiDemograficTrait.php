<?php
namespace App\Action\Bi;


use App\Resource\Privacy\GroupByEmail;
use App\Resource\PrivacyResource;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

trait BiDemograficTrait{
    use BiBase;
    private function getDemograficDimMonthSerOriginFilterPaxType(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                $sqlCaseOpenedMonth AS dimension,
                sum(dm.price) AS value,
                $sqlCasePaxType AS filter1,
                $sqlCaseOrigin AS serie
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            -- dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere 
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year, dm.opened_month, reservation_origin, dm.paxtype 
            ORDER BY dm.opened_year, dm.opened_month, reservation_origin, dm.paxtype
        ";

        $rsm = $this->createDemograficBiResultsetMapping();
        $rsm->addScalarResult('filter1', 'filter1');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getDemograficDimMonthSerPaxTypeOriginFilterOrigin(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                $sqlCaseOpenedMonth AS dimension,
                sum(dm.price) AS value,
                $sqlCasePaxType AS serie,
                $sqlCaseOrigin AS filter1
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            -- dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year, dm.opened_month, reservation_origin, dm.paxtype 
            ORDER BY dm.opened_year, dm.opened_month, reservation_origin, dm.paxtype
        ";

        $rsm = $this->createDemograficBiResultsetMapping();
        $rsm->addScalarResult('filter1', 'filter1');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getDemograficDimPaxTypeSerOrigin(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;

        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                sum(dm.price) AS value,
                $sqlCasePaxType AS dimension,
                $sqlCaseOrigin AS serie
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND
            $structureWhere 
            -- dm.structure_uid = '$portalCode-$structureId' and  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year, reservation_origin, dm.paxtype 
            ORDER BY dm.opened_year, reservation_origin, dm.paxtype
        ";

        $rsm = $this->createDemograficBiResultsetMapping();

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getDemograficDimOriginSerPaxType(EntityManager $em, $portalCode, $structureId, $portalId = 1) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                sum(dm.price) AS value, 
                $sqlCasePaxType AS serie,
                $sqlCaseOrigin AS dimension
                
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            -- dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year,  dm.paxtype,reservation_origin 
            ORDER BY dm.opened_year,  dm.paxtype,reservation_origin
        ";

        $rsm = $this->createDemograficBiResultsetMapping();

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function createDemograficBiResultsetMapping ($dimensionType='string', $valueType='integer') {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('items', 'items', 'integer');
        $rsm->addScalarResult('filter', 'filter', 'integer');
        $rsm->addScalarResult('value', 'value',$valueType);
        $rsm->addScalarResult('dimension', 'dimension', $dimensionType);
        $rsm->addScalarResult('serie', 'serie');

        return $rsm;
    }

    private function biResponseDemografic ($structure, $emDirectBi) {

        $biResponse = [];
        $biResponse['structure'] = $structure;
        $biResponse['paxtype-origin'] = $this->getDemograficDimPaxTypeSerOrigin(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['origin-paxtype'] = $this->getDemograficDimOriginSerPaxType(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['month-paxtype-origin'] = $this->getDemograficDimMonthSerOriginFilterPaxType(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['month-origin-paxtype'] = $this->getDemograficDimMonthSerPaxTypeOriginFilterOrigin(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        return $biResponse;
    }

}
