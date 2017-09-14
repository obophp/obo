<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class InterfacedContactProperties extends \obo\Tests\Assets\Entities\Contacts\ContactProperties {

    /**
    * @obo-many(targetEntity="TestCases\Relationship\Assets\Entities\Contacts\InterfacedAddress", connectViaProperty="owner", ownerNameInProperty="ownerEntity")
    */
    public $addresses;

}
