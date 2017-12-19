<?php

namespace obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts;

class InterfacedAddressManager extends \obo\Tests\Assets\Entities\Contacts\AddressManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts\InterfacedAddress
     */
    public static function address($specification) {
        return parent::address($specification);
    }

}
