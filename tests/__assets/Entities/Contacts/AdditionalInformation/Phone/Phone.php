<?php

namespace obo\Tests\Assets\Entities\Contacts\AdditionalInformation;

/**
 * @obo-repositoryName(TestsEntitiesContactsAdditionalInformationPhone)
 * @property boolean $isDefault
 */
class Phone extends \obo\Tests\Assets\Entities\Contacts\AdditionalInformation {

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
