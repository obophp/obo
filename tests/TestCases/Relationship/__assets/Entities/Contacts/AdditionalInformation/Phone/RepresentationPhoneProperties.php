<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class RepresentationPhoneProperties extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneProperties {

    /**
     * @obo-one(targetEntity="TestCases\Relationship\Assets\Entities\Contacts\RepresentationContact")
     */
    public $contact = null;

}
