<?php

namespace obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact;

class AdministrativeEmailManager extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\EmailManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact\AdministrativeEmail
     */
    public static function administrativeEmail($specification) {
        return static::entity($specification);
    }

}
