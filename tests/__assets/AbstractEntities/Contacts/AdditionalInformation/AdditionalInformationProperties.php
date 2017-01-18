<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts;

abstract class AdditionalInformationProperties extends \obo\Tests\Assets\AbstractEntities\EntityProperties {

    /**
     * @obo-one(targetEntity="Contact")
     */
    public $contact = null;

    /**
     * @obo-dataType(string)
     * @obo-nullable
     */
    public $value = null;

    /**
     * @obo-dataType(string)
     */
    public $type = "";

}
