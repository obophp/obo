<?php

namespace obo\Tests\Assets\Entities\Contacts;

class ContactProperties extends \obo\Tests\Assets\Entities\EntityProperties {

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
     * @obo-one(targetEntity="\obo\Tests\Assets\Entities\Contacts\Address", autoCreate=true, cascade="save", eager="true")
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

    /**
     *
     * @obo-dataType(string)
     */
    public $note;

}
