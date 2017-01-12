<?php

namespace obo\Tests\Assets\Entities\Contacts;

class ContactProperties extends \obo\Tests\Assets\AbstractEntities\Contacts\ContactProperties {

    /**
     * @obo-dataType(integer)
     * @obo-autoIncrement
     */
    public $id = "";

    /**
     * @obo-many(targetEntity="\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone", connectViaProperty="contact", cascade="save,delete")
     */
    public $phones = null;

    /**
     * @obo-many(targetEntity="\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email", connectViaProperty="contact", cascade="save,delete")
     */
    public $emails = null;

    /**
     * @obo-many(targetEntity="\obo\Tests\Assets\Entities\Contacts\Address", connectViaProperty="owner", ownerNameInProperty="ownerEntity" cascade="save,delete")
     */
    public $addresses = null;

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\Entities\Contacts\Address", autoCreate=true, cascade="save, delete")
     * @obo-repositoryName(TestsEntitiesContactsContact)
     */
    public $defaultAddress = null;

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Phone", autoCreate=true, cascade="save")
     */
    public $defaultPhone = null;

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email", autoCreate=true, cascade="save")
     */
    public $defaultEmail = null;

}
