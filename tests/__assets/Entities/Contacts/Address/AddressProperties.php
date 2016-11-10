<?php

namespace obo\Tests\Assets\Entities\Contacts;

class AddressProperties extends \obo\Tests\Assets\Entities\EntityProperties {

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
