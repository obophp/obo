<?php

namespace obo\Tests\Assets\Entities\Contacts;

class AdditionalInformationProperties extends \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformationProperties {

    /**
     * @obo-one(targetEntity="Contact")
     */
    public $contact = null;

}
