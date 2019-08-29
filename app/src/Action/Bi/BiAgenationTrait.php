<?php
namespace App\Action\Bi;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

trait BiAgenationTrait{
    use BiBase;

    private function createAgeNationsBiResultsetMapping ($dimensionType='string', $valueType='integer') {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('items', 'items', 'integer');
        $rsm->addScalarResult('filter', 'filter', 'integer');
        $rsm->addScalarResult('filter2', 'filter2');
        $rsm->addScalarResult('value', 'value',$valueType);
        $rsm->addScalarResult('dimension', 'dimension', $dimensionType);
        $rsm->addScalarResult('serie', 'serie');

        return $rsm;
    }
    private function getAgeNationsDimMonthSerOriginFilterPaxType(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                $sqlCaseOpenedMonth AS dimension,
                sum(dm.price) AS value,
                $sqlCasePaxType AS filter1,
                dm.country AS filter2,
                $sqlCaseOrigin AS serie
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            --  dm.structure_uid = '$portalCode-$structureId' and
            --  dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year, dm.opened_month, dm.country, reservation_origin, dm.paxtype 
            ORDER BY dm.opened_year, dm.opened_month, dm.country, reservation_origin, dm.paxtype
        ";

        $rsm = $this->createAgeNationsBiResultsetMapping();
        $rsm->addScalarResult('filter1', 'filter1');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getAgeNationsDimMonthSerPaxTypeOriginFilterOrigin(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;

        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                dm.country AS filter2,
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
            GROUP BY dm.opened_year, dm.opened_month, dm.country,reservation_origin, dm.paxtype 
            ORDER BY dm.opened_year, dm.opened_month, dm.country,reservation_origin, dm.paxtype
        ";

        $rsm = $this->createAgeNationsBiResultsetMapping();
        $rsm->addScalarResult('filter1', 'filter1');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getAgeNationsDimPaxTypeSerOrigin(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                dm.country AS filter2,
                sum(dm.price) AS value,
                $sqlCasePaxType AS dimension,
                $sqlCaseOrigin AS serie
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year, dm.country, reservation_origin, dm.paxtype 
            ORDER BY dm.opened_year, dm.country, reservation_origin, dm.paxtype
        ";

        $rsm = $this->createAgeNationsBiResultsetMapping();

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getAgeNationsDimOriginSerPaxType(EntityManager $em, $portalCode, $structureId, $portalId = 1) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                dm.country AS filter2,
                sum(dm.price) AS value, 
                $sqlCasePaxType AS serie,
                $sqlCaseOrigin AS dimension
                
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND  
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year,  dm.country,dm.paxtype,reservation_origin 
            ORDER BY dm.opened_year,  dm.country,dm.paxtype,reservation_origin
        ";

        $rsm = $this->createAgeNationsBiResultsetMapping();

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function getAgeNationsCountries (EntityManager $em, $portalCode, $structureId, $portalId = 1) {
        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;

        $structureWhere = '';
        if("$structureId"!="" ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            SELECT  count(country) AS items, country
            FROM abs_datamart.dm_reservation_$portalCode dm
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            $structureWhere
            dm.opened_year >= '2016'
            GROUP BY country ORDER BY count(country) desc,country
            LIMIT 0,10
        ";

        // die('<pre>'.$sql . ' ----- |'.$structureId.'|');
        $rsm = $rsm = new ResultSetMapping();
        $rsm->addScalarResult('country', 'country');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();

    }
    private function biResponseAgeNation ($structure, $emDirectBi) {

        $biResponse = [];
        $biResponse['structure'] = $structure;
        $biResponse['countries'] = $this->getAgeNationsCountries(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['paxtype-origin'] = $this->getAgeNationsDimPaxTypeSerOrigin(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['origin-paxtype'] = $this->getAgeNationsDimOriginSerPaxType(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['month-paxtype-origin'] = $this->getAgeNationsDimMonthSerOriginFilterPaxType(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        $biResponse['month-origin-paxtype'] = $this->getAgeNationsDimMonthSerPaxTypeOriginFilterOrigin(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        return $biResponse;
    }
}
