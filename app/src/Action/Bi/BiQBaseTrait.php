<?php
namespace App\Action\Bi;


use App\Manager\ApplicationMiddleware;
use App\Resource\Privacy\GroupByEmail;
use App\Resource\PrivacyResource;
use function array_key_exists;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use function explode;
use function print_r;
use Slim\App;
use Slim\Http\Request;

trait BiQBaseTrait{

    /**
     {
        "filter":{},
        "pagination": {"page": 0,"records":0}
     }
     */

    use BiBase;

    public function getBiBaseCountries (EntityManager $em, $portalCode, $structureId, $portalId = 1) {
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";

        $sql = "
            SELECT  count(country) AS items, country, country_iso2
            FROM abs_datamart.dm_reservation_$portalCode dm
            WHERE dm.portal_uid = '$portalCode-$portalId' AND 
            -- dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere  
            dm.opened_year >= '2016'
            GROUP BY country ORDER BY count(country) desc,country
        ";

        $rsm = $rsm = new ResultSetMapping();
        $rsm->addScalarResult('country', 'country');
        $rsm->addScalarResult('country_iso2', 'country_iso2');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();

    }

    public function getBiBaseCities (EntityManager $em, $portalCode, $structureId, $portalId = 1) {
        $structureWhere = '';
        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and";
        $sql = "
            SELECT distinct reservation_city as city 
              FROM abs_datamart.dm_reservation_res dm
                   LEFT JOIN abs_datawarehouse.fact_reservation_res AS fact ON dm.sync_code = fact.related_sync_code
                   LEFT JOIN abs_datawarehouse.raw_reservation_res AS raw ON fact.related_reservation_code = raw.sync_code
             WHERE 
                -- dm.structure_uid = '$portalCode-$structureId' and
                $structureWhere  
                dm.opened_year >= '2016'
                    
            ORDER BY  reservation_city 
        ";

        $rsm = $rsm = new ResultSetMapping();
        $rsm->addScalarResult('city', 'city');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    private function buildWhere_filter_in ($fieldName, $queryConfig, $dbName = 'dm') {
        if(!isset($queryConfig["filter"][$fieldName])) return "";
        $filter = $queryConfig["filter"];
        $result = "";
        if( isset($filter[$fieldName]) && count($filter[$fieldName])>0 ){
            $result =  " and $dbName.$fieldName IN ('".implode("','", $filter[$fieldName])."')";
        }
        return $result;
    }


    private function normalizeDate($data, $name) {
        if(!isset($data[$name])) return null;
        $res = explode("T",$data[$name]);
        return $res[0];
    }
    private function buildWhere_betweenDate($fieldName, $filterFrom, $filterTo, $data,$dbName = 'dm') {

        $filterFrom = $this->normalizeDate($data, $filterFrom);
        $filterTo = $this->normalizeDate($data, $filterTo);

        if(!isset($filterFrom)) {
            if(!$filterTo) return "";
            return " AND $fieldName <= '$filterTo' ";
        }else {
            if(!isset($filterTo)) return " AND $fieldName >= '$filterFrom' ";
        }
        return " AND $fieldName between  '$filterFrom' AND '$filterTo'";

    }

    private function buildWhere_filter_ext ($fieldName, $filterName, $data, $dbName = 'dm') {

        if(!isset($data[$filterName])) return "";
        $filter = $data;
        $result = "";
        if( isset($filter[$filterName]) && count($filter[$filterName])>0 ){
            $result =  " and $dbName.$fieldName IN ('".implode("','", $filter[$filterName])."')";
        }
        return $result;
    }

    /**
     * @param $data
     * @param $criteria
     * @param $privacyEm
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    private function filterByPrivacy (&$data, $criteria, $privacyEm ) {
        $excluded=[];
        $priRes = new PrivacyResource($privacyEm);
        $list = $priRes->privacyListIds($criteria, new GroupByEmail());
        // return $list;

        //die($data['Chris.franek44@gmail.com']);

        $listByEmail = [];

        foreach ($data as &$record) {

            if(array_key_exists($record['email'], $list) || array_key_exists(strtolower ($record['email']), $list) ){
                $list[ $record['email'] ]['language'] = $record['language'];
                $listByEmail[]=&$list[ $record['email'] ];
            } else {
                $excluded[] = $record['email'];
            }
        }

        return ['listByEmail'=>$listByEmail, 'excluded'=>$excluded];
    }

    private function buildOriginWhere ($filterGlobal) {
        if(!isset($filterGlobal['origin'])) return "";

        $filter = $filterGlobal['origin'];
        $where = "";
        $count=0;
        foreach ($filter as $record) {
            if($count>0) $where.=" OR ";
            else $where = " AND ( ";
            if($record=="OB")  $where.=" reservation_number NOT LIKE 'M%' AND dm.origin = 'BOOKINGONE' ";
            else if($record=="PMS_M")  $where.=" reservation_number LIKE 'M%' ";
            else if($record=="CRO")  $where.=" dm.origin='CRO' ";
            $count++;
        }

        if($count>0) $where.= ")";
        return $where;
    }

    /**
     * @param EntityManager $em
     * @param               $portalCode
     * @param               $structureId
     * @param int           $portalId
     * @param array         $queryConfig
     *
     * @return array
     *
     */

    private function getResponseQBaseData(EntityManager $em, EntityManager $privacyEm, $portalCode, $structureId, $portalId = 1, $queryConfig=[]) {
        $structureWhere = '';

        if($structureId!=null ) $structureWhere="dm.structure_uid = '$portalCode-$structureId' and ";

        $sqlCasePaxType = $this->sqlCasePaxtype;
        $sqlCaseOrigin = $this->sqlCaseOrigin;
        $sqlCaseOpenedMonth = $this->sqlCaseOpenedMonth;

        // $fieldName, $filterName, $data, $dbName

        // time_range_type: "days"
        // time_range_value: "2"

        $whereTimeRange="";
        if(isset($queryConfig['bi']['time_range_value']) && !$queryConfig['bi']['time_range_value']=='') {
            $time_range_value = $queryConfig['bi']['time_range_value']  ;
            $time_range_type = $queryConfig['bi']['time_range_type']  ;
            $today = date('Y-m-d');

            if($time_range_type == 'months' ) $time_range_value = $time_range_value * 30;
            else if($time_range_type == 'years' ) $time_range_value = $time_range_value * 365;
            $whereTimeRange=" AND DATEDIFF('$today', dm.opened_date ) <= $time_range_value ";
        }

        $whereCheckin =$this->buildWhere_betweenDate('checkin_date', 'checkin_from', 'checkin_to', $queryConfig['bi']);
        $whereCheckout =$this->buildWhere_betweenDate('checkout_date', 'checkout_from', 'checkout_to', $queryConfig['bi']);
        $whereOpenedDate =$this->buildWhere_betweenDate('opened_date', 'opendate_from', 'opendate_to', $queryConfig['bi']);

        $whereCountry =  $this->buildWhere_filter_ext("country","nationality",$queryConfig['bi']);
        $wherePax =  $this->buildWhere_filter_ext("paxtype","paxtype",$queryConfig['bi']);
        $whereProduct =  $this->buildWhere_filter_ext("room_code","product",$queryConfig['bi']);
        $whereOrigin =  $this->buildOriginWhere($queryConfig['bi']) ;
        $whereChannel =  $this->buildWhere_filter_ext("reservation_origin","channel",$queryConfig['bi'], "raw");
        $whereLanguage =  $this->buildWhere_filter_ext("reservation_guest_language","language",$queryConfig['bi'],"raw");
        $whereLeadTime =  $this->buildWhere_filter_ext("lead_time","leadtime",$queryConfig['bi']);

        $whereNights="";
        if(isset($queryConfig['bi']['nights']) && $queryConfig['bi']['nights']!='' ) {
            $nights = $queryConfig['bi']['nights'];
            $whereNights=" AND dm.nights = $nights" ;
        }
        $whereCity = "";

        $sql = "
            SELECT

               (select
                    GROUP_CONCAT(DISTINCT  DATE_FORMAT(reservation_opened_date,'%d/%m/%y')  ORDER BY reservation_opened_date DESC SEPARATOR ' - ')
                    from abs_datawarehouse.raw_reservation_$portalCode as raw2
                    where raw2.reservation_email=raw.reservation_email
                    and reservation_deleted = 0
               ) as return_dates,
               count(*),
               raw.sync_code,
               reservation_email AS email,
               'reservation' AS product,
               reservation_origin AS origin,
               country,
               reservation_guest_language as language,
               country_iso2,
               reservation_city AS city,
               dm.paxtype,
               dm.lead_time,
               dm.opened_date AS opened,
               dm.checkin_date AS checkin,
               dm.checkout_date AS checkout,
               dm.nights,
               reservation_name AS name,
               reservation_surname AS surname,
               reservation_number as code

          FROM abs_datamart.dm_reservation_$portalCode dm
          LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
          INNER JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw  ON fact.related_reservation_code = raw.sync_code
          INNER JOIN
            (
              select max(sync_code) as sync_code
              from abs_datawarehouse.raw_reservation_$portalCode
              group by reservation_email order by reservation_email
            ) AS raw1
            ON fact.related_reservation_code = raw1.sync_code
         WHERE
            -- dm.structure_uid = '$portalCode-$structureId' and
            $structureWhere
            dm.opened_year >= '2016'

            $whereCountry
            $whereProduct
            $whereOrigin
            $whereChannel
            $whereLanguage
            $whereCity
            $wherePax
            $whereCheckin
            $whereCheckout
            $whereOpenedDate
            $whereLeadTime
            $whereNights
            $whereTimeRange
            
         group by reservation_email

        ORDER BY
                reservation_email,
                dm.checkin_date,
                dm.checkout_date,
                dm.sync_code,
                dm.opened_year,
                dm.paxtype,
                reservation_origin

        ";

        $sql = "
          SELECT  distinct reservation_email AS email,
          min(reservation_guest_language) as language
          FROM abs_datamart.dm_reservation_$portalCode dm
          LEFT JOIN abs_datawarehouse.fact_reservation_$portalCode AS fact ON dm.sync_code = fact.related_sync_code
          INNER JOIN abs_datawarehouse.raw_reservation_$portalCode AS raw  ON SUBSTRING_INDEX(fact.related_reservation_code,'-',-1) = raw.reservation_id

         WHERE
            -- dm.structure_uid = '$portalCode-$structureId'
            dm.opened_year >= '2016'
            -- AND dm.type in('RESERVATION')
            $whereCountry
            $whereProduct
            $whereOrigin
            $whereChannel
            $whereLanguage
            $whereCity
            $structureWhere
            $wherePax
            $whereCheckin
            $whereCheckout
            $whereOpenedDate
            $whereLeadTime
            $whereNights
            $whereTimeRange
            
        GROUP BY reservation_email
        ORDER BY reservation_email
         
        ";

       // $this->filterByPrivacy([], $queryConfig,$privacyEm ); die("1");

        // die("<pre>$sql");
        $rsm = new ResultSetMapping();

        // serve solo email


        $rsm->addScalarResult('email', 'email');
        $rsm->addScalarResult('language', 'language');

        $query = $em->createNativeQuery($sql, $rsm);
        $result = $query->getResult();

        // $result = $this->filterByPrivacy($result, $queryConfig,$privacyEm );
        // ['listByEmail'=>$listByEmail, 'excluded'=>$excluded];

        $data = $this->filterByPrivacy($result, $queryConfig,$privacyEm );

        return [
            'q'=>$sql,
            'result'=>$result,
            "excluded"=>&$data['excluded'],
            "pv"=>&$data['listByEmail']
        ];
    }

    /**
     * @param $structure
     * @param $emDirectBi
     * @param $request Request
     *
     * @return mixed
     */
    private function biResponseQBase ($structure, $emDirectBi, $privacyEm, $request) {
        $queryConfig = $request->getParsedBody();

        $biResponse = [];
        $biResponse['structure'] = $structure;
        $biResponse['countries'] = $this->getBiBaseCountries(
            $emDirectBi,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id']
        );
        // $biResponse['cities'] = $this->getBiBaseCities(
        //     $emDirectBi,
        //     $structure['portal_code'],
        //     $structure['structure_id'],
        //     $structure['portal_id']
        // );
        $biResponse['qbase'] = $this->getResponseQBaseData(
            $emDirectBi,
            $privacyEm,
            $structure['portal_code'],
            $structure['structure_id'],
            $structure['portal_id'],
            $queryConfig
        );
        return $biResponse;
    }
}
