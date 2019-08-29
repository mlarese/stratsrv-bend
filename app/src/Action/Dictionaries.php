<?php

namespace App\Action;


use App\Base\BaseAction;
use App\Entity\Privacy\Dictionary;
use App\Entity\Privacy\PrivacyAttachment;
use DateTime;
use function is_string;

class Dictionaries extends BaseAction {
    public function clazz() {
        return Dictionary::class;
    }
    public function baseParams() { return []; }
    public function mandatoryFields() { return [];}
}
