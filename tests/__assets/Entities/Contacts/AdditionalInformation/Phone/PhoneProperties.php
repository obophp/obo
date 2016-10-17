<?php

namespace obo\Tests\Assets\Entities\Contacts\AdditionalInformation;

class PhoneProperties extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformationProperties {

    /**
     * @return boolean
     */
    public function getIsDefault() {
        if ($this->_owner->contact && $this->id === $this->_owner->contact->defaultPhone->id) return true;
        return false;
    }

}
