<?php

namespace obo\Tests\TestCases\Entity\Assets\Entities\Contacts;

class ContactProperties extends \obo\Tests\Assets\Entities\Contacts\ContactProperties {

    /**
     * @obo-one(targetEntity="\obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact\AdministrativeEmail", autoCreate=true)
     */
    public $administrativeEmail = null;

    /**
     * @obo-one(targetEntity = "\obo\Tests\TestCases\Entity\Assets\Entities\Notes\Note", connectViaProperty="owner", ownerNameInProperty="ownerEntityName" , autoCreate=true)
     */
    public $note = null;

}
