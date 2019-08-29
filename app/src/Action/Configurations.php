<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 14/09/2018
 * Time: 11:03
 */

namespace App\Action;


use App\Base\BaseAction;
use App\Entity\Privacy\Configuration;

class Configurations extends BaseAction {
    public function clazz() { return Configuration::class;   }
    public function baseParams() {  return [];  }
    public function mandatoryFields() { return [];   }
    public function afterGetById(&$record, $args) {
        /** @var Configuration $record */
        if($record === null){
            $ownerId = $this->getOwnerId($this->getRequest());
            $record = new Configuration();
            foreach ($args as $key => $value) break;

            $record->setCode($args[$key])
                ->setData(['ownerId'=>$ownerId])
                ->setDescription($args[$key]);

        }

    }
}


