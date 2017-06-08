<?php

namespace obo\Tests\Assets\Entities\Contacts\AdditionalInformation;

class PhoneManager extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformationManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone
     */
    public static function phone($specification) {
        return static::entity($specification);
    }

}
