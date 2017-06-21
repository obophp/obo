<?php

namespace obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact;

class AdministrativeEmailProperties extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\EmailProperties {

    /**
     * @obo-one(targetEntity="\TestCases\Entity\Assets\Entities\Contacts\Contact", connectViaProperty = "administrativeEmail", autoCreate = true)
     */
    public $contact = null;

}
