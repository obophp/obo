<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation;

abstract class EmailProperties extends \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformationProperties {

    /**
     * @return boolean
     */
    public function getIsDefault() {
        if ($this->_owner->contact && $this->id === $this->_owner->contact->defaultEmail->id) return true;
        return false;
    }

}
