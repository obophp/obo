<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class InterfacedAddressManager extends \obo\Tests\Assets\Entities\Contacts\AddressManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\InterfacedAddress
     */
    public static function address($specification) {
        return parent::address($specification);
    }

}
