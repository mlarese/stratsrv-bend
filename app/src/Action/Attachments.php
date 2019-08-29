<?php

namespace App\Action;


use App\Base\BaseAction;
use App\Entity\Privacy\PrivacyAttachment;
use DateTime;
use function is_string;

class Attachments extends BaseAction {
    public function clazz() {
        return PrivacyAttachment::class;
    }
    public function baseParams() { return ['deleted'=>0]; }
    public function mandatoryFields() { return [];}
    public function afterGetById(&$record, $args) {
        /** @var PrivacyAttachment $record */
        if($record === null){
            $record = new PrivacyAttachment();
            $record->setId($args['id'])
                ->setCreated(new DateTime())
                ->setDeleted(false)
                ->setAttachments('[]');
        }
    }
    public function beforeCreate(&$values) {
        $values['deleted'] = 0;
        $values['created'] = new DateTime();
    }
    public function beforeSave(&$values)
    {

        if(!isset($values['attachments'])) $values['attachments']='[]';
        if(!isset($values['attachments'])) $values['attachments']='[]';
        $values['attachments']= json_encode($values['attachments']);

        if(isset($values['created'])) {
            if(is_string( $values['created']))
                $values['created'] = new DateTime($values['created']);
        } else {
            $values['created'] = new DateTime();
        }


    }

}
