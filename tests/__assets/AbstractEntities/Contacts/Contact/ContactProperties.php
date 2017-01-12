<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts;

abstract class ContactProperties extends \obo\Tests\Assets\AbstractEntities\EntityProperties {

    /**
     * @obo-dataType(integer)
     * @obo-autoIncrement
     */
    public $id = "";

    /**
     * @obo-dataType(string)
     */
    public $name = "";

    /**
     * @obo-many(targetEntity="\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone", connectViaProperty="contact", cascade="save,delete")
     */
    public $phones = null;

    /**
     * @obo-many(targetEntity="\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email", connectViaProperty="contact", cascade="save,delete")
     */
    public $emails = null;

    /**
     * @obo-many(targetEntity="\obo\Tests\Assets\AbstractEntities\Contacts\Address", connectViaProperty="owner", ownerNameInProperty="ownerEntity" cascade="save,delete")
     */
    public $addresses = null;

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\AbstractEntities\Contacts\Address", autoCreate=true, cascade="save", eager="true")
     */
    public $defaultAddress = null;

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Phone", autoCreate=true, cascade="save")
     */
    public $defaultPhone = null;

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation\Email", autoCreate=true, cascade="save")
     */
    public $defaultEmail = null;

    /**
     *
     * @obo-dataType(string)
     */
    public $note;

}
