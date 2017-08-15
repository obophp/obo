<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class ContactProperties extends \obo\Tests\Assets\Entities\Contacts\ContactProperties {

    /**
     * @obo-many(targetEntity="TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddress", connectViaProperty="owner", ownerNameInProperty="ownerEntity")
     */
    public $addresses;

}
