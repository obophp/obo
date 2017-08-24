<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class EnumeratedAddressManager extends \obo\Tests\Assets\Entities\Contacts\AddressManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddress
     */
    public static function address($specification) {
        return parent::address($specification);
    }

}
