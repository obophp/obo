<?php

namespace obo\Tests\Assets\Entities\Contacts;

class AdditionalInformationProperties extends \obo\Tests\Assets\Entities\EntityProperties {

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
