<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts;

abstract class AddressProperties extends \obo\Tests\Assets\AbstractEntities\EntityProperties {

    /**
     * @obo-one(targetEntity="property:ownerEntity")
     */
    public $owner = null;

    public $ownerEntity = "";

    /**
     * @obo-dataType(string)
     */
    public $street = "";

    /**
     * @obo-dataType(string)
     */
    public $city = "";

    /**
     * @obo-dataType(string)
     */
    public $region = "";

    /**
     * @obo-dataType(string)
     */
    public $zip = "";

    /**
     * @obo-one(targetEntity = "obo\Tests\Assets\AbstractEntities\Note", connectViaProperty="owner", ownerNameInProperty = "ownerEntityName", eager = true, cascade = "save, delete")
     */
    public $note = null;

    /**
     * @return string
     */
    public function getCompleteAddress() {
        $address = "";
        $separator = ", ";

        if ($this->_owner->street) {
            $address .= $this->_owner->street . $separator;
        }

        if ($this->_owner->zip) {
            $address .= $this->_owner->zip . $separator;
        }

        if ($this->_owner->city) {
            $address .= $this->city . $separator;
        }

        if ($this->_owner->region) {
            $address .= $this->_owner->region . $separator;
        }

        return $address;
    }

}
