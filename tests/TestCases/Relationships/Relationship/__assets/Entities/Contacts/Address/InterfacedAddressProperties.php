<?php

namespace obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts;

class InterfacedAddressProperties extends \obo\Tests\Assets\Entities\Contacts\AddressProperties {

    /**
     * @obo-one(targetEntity = "property:ownerEntity", interface = "obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\IAddressEntity")
     */
    public $owner = null;

    public $ownerEntity = "";

}
