<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class InterfacedAddressProperties extends \obo\Tests\Assets\Entities\Contacts\AddressProperties {

    /**
     * @obo-one(targetEntity = "property:ownerEntity", interface = "\obo\Tests\Assets\IAddressEntity")
     */
    public $owner = null;

    public $ownerEntity = "";

}
