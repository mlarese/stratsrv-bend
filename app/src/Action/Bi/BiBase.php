<?php
namespace App\Action\Bi;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

trait BiBase {
    public $sqlCasePaxtype = "
        CASE dm.paxtype
            WHEN 'Couples with child' THEN 'Families'
            WHEN 'Adult with child' THEN 'Families'
            WHEN 'Three Adults' THEN 'Families'
            WHEN 'Big family' THEN 'Families'
            ELSE dm.paxtype
        END  
    ";

    public $sqlCaseOpenedMonth = "
        CASE dm.opened_month
            WHEN 1 THEN 'GEN'
            WHEN 2 THEN 'FEB'
            WHEN 3 THEN 'MAR'
            WHEN 4 THEN 'APR'
            WHEN 5 THEN 'MAG'
            WHEN 6 THEN 'GIU'
            WHEN 7 THEN 'LUG'
            WHEN 8 THEN 'AGO'
            WHEN 9 THEN 'SET'
            WHEN 10 THEN 'OTT'
            WHEN 11 THEN 'NOV'
            WHEN 12 THEN 'DIC' 
        END  
    ";

    public $sqlCaseOrigin = "reservation_origin";

}
