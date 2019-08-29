<?php
namespace App\Action\Bi;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

trait BiReturnsTrait{
    use BiBase;

    private function getReturns(EntityManager $em, $portalCode, $structureId, $portalId = 1) {
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sql = "
            select count(*) as items, checkin_year as dimension, sum(value) as value, status as serie  from (
            SELECT  
                  case  
                    WHEN count(distinct related_reservation_code) = 1 THEN 'RITORNI'
                    ELSE 'NUOVI'
                  end as status  ,
                    dm.checkin_year,
                    reservation_email,
                    ceiling (sum(dm.price)) AS value
                            
             FROM abs_datamart.dm_reservation_$portalCode dm
             LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
             LEFT JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw ON fact.related_reservation_code = raw.sync_code
             WHERE dm.portal_uid = '$portalCode-$portalId' AND 
             -- dm.structure_uid = '$portalCode-$structureId' and
             $structureWhere  
             dm.checkin_year >= '2016'  
             
            GROUP BY dm.checkin_year , reservation_email
            ORDER BY dm.checkin_year , reservation_email
            ) as returns
            group by checkin_year,serie, dimension
        
        ";

        $rsm = $this->createReturnsBiResultsetMapping();

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }
    private function createReturnsBiResultsetMapping ($dimensionType='string', $valueType='integer') {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('items', 'items', 'integer');
        $rsm->addScalarResult('filter', 'filter', 'integer');
        $rsm->addScalarResult('value', 'value',$valueType);
        $rsm->addScalarResult('dimension', 'dimension', $dimensionType);
        $rsm->addScalarResult('serie', 'serie');

        return $rsm;
    }

    private function biResponseReturns ($structure, $emDirectBi) {

        $biResponse = [];
        $biResponse['structure'] = $structure;
        $biResponse['returns'] = $this->getReturns(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );

        return $biResponse;
    }

}
