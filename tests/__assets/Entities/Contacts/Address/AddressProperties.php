<?php

namespace obo\Tests\Assets\Entities\Contacts;

class AddressProperties extends \obo\Tests\Assets\AbstractEntities\Contacts\AddressProperties {

    /**
     * @obo-one(targetEntity="property:ownerEntity")
     */
    public $owner = null;

    public $ownerEntity = "";

    /**
     * @obo-one(targetEntity = "obo\Tests\Assets\Entities\Note", connectViaProperty="owner", ownerNameInProperty = "ownerEntityName", eager = true, cascade = "save, delete")
     */
    public $note = null;

}
