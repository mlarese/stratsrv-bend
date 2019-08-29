<?php
namespace App\Action\Bi;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

trait BiMonthYearTrait {
    use BiBase;

    private function getMonthYearDimMonthSerOrigin(EntityManager $em, $portalCode, $structureId, $portalId = 1, $addMonth = false) {
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            SELECT  count(*) AS items,
                dm.opened_year AS filter,
                $sqlCaseOpenedMonth AS dimension,
                sum(dm.price) AS value,
                $sqlCaseOrigin AS serie
            FROM abs_datamart.dm_reservation_$portalCode dm
            LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
            LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            -- dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY dm.opened_year, dm.opened_month, reservation_origin 
            ORDER BY dm.opened_year, dm.opened_month, reservation_origin
        ";

        $rsm = $this->createMonthYearBiResultsetMapping();

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    private function createMonthYearBiResultsetMapping ($dimensionType='string', $valueType='integer') {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('items', 'items', 'integer');
        $rsm->addScalarResult('filter', 'filter', 'integer');
        $rsm->addScalarResult('value', 'value',$valueType);
        $rsm->addScalarResult('dimension', 'dimension', $dimensionType);
        $rsm->addScalarResult('serie', 'serie');

        return $rsm;
    }

    private function biResponseMonthYear ($structure, $emDirectBi) {

        $biResponse = [];
        $biResponse['structure'] = $structure;

        $biResponse['month-origin'] = $this->getMonthYearDimMonthSerOrigin(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        return $biResponse;
    }
}
