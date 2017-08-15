<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class EnumeratedAddressProperties extends \obo\Tests\Assets\Entities\Contacts\AddressProperties {

    /**
     * @obo-one(targetEntity = "property:ownerEntity, TestCases\Relationship\Assets\Entities\Contacts\Contact, TestCases\Relationship\Assets\Entities\Person")
     */
    public $owner = null;

    public $ownerEntity = "";

}
