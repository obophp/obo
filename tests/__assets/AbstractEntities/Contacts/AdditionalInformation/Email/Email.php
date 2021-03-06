<?php

namespace obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation;

/**
 * @obo-repositoryName(TestsEntitiesContactsAdditionalInformationEmail)
 * @property boolean $isDefault
 */
abstract class Email extends \obo\Tests\Assets\AbstractEntities\Contacts\AdditionalInformation {

    /**
     * @return boolean
     */
    public function canDelete() {
        if ($this->isDefault) {
            return false;
        }

        return true;
    }

    /**
     * @obo-run(beforeDelete)
     * @throws \obo\Exceptions\Exception
     */
    public function checkDeletableBeforeDeleting() {
        if (!$this->canDelete()) {
            throw new \obo\Exceptions\Exception("Entity can't be deleted", 500);
        }
    }

}
