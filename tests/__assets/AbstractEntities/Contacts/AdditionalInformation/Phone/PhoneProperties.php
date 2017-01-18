<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation;

abstract class PhoneProperties extends \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformationProperties {

    /**
     * @return boolean
     */
    public function getIsDefault() {
        if ($this->_owner->contact && $this->id === $this->_owner->contact->defaultPhone->id) return true;
        return false;
    }

}
