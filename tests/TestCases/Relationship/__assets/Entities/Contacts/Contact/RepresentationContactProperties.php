<?php

namespace obo\Tests\TestCases\Relationship\Assets\Entities\Contacts;

class RepresentationContactProperties extends \obo\Tests\Assets\Entities\Contacts\ContactProperties {

    /**
     * @obo-many(targetEntity="TestCases\Relationship\Assets\Entities\Contacts\AdditionalInformation\RepresentationPhone", connectViaProperty="contact", cascade="save,delete")
     */
    public $phones = null;

    /**
     * @obo-one(targetEntity="TestCases\Relationship\Assets\Entities\Contacts\AdditionalInformation\RepresentationPhone", connectViaProperty="contact", autoCreate=true, cascade="save")
     */
    public $defaultPhone = null;

}
