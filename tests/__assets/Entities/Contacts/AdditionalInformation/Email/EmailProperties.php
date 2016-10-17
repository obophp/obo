<?php

namespace obo\Tests\Assets\Entities\Contacts\AdditionalInformation;

class EmailProperties extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformationProperties {

    /**
     * @return boolean
     */
    public function getIsDefault() {
        if ($this->_owner->contact && $this->id === $this->_owner->contact->defaultEmail->id) return true;
        return false;
    }

}
