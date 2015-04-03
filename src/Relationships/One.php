<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

class One extends \obo\Relationships\Relationship {

    /**
     * @var boolean
     */
    public $autoCreate = true;

    /**
     * @var string
     */
    public $entityClassNameToBeConnectedInPropertyWithName = null;

    /**
     * @param string $entityClassNameToBeConnected
     * @param string $ownerPropertyName
     * @param array $cascade
     * @return void
     */
    public function __construct($entityClassNameToBeConnected, $ownerPropertyName, array $cascade = array()) {
        if (\strpos($entityClassNameToBeConnected, "property:") === 0) {
            $this->entityClassNameToBeConnectedInPropertyWithName = \substr($entityClassNameToBeConnected, 9);
            $entityClassNameToBeConnected = null;
        }
        parent::__construct($entityClassNameToBeConnected, $ownerPropertyName, $cascade);
    }

    /**
     * @param \obo\Entity $owner
     * @param mixed $propertyValue
     * @return \obo\Entity|null
     */
    public function relationshipForOwnerAndPropertyValue(\obo\Entity $owner, $propertyValue) {
        $this->owner = $owner;

        if (\is_null($this->entityClassNameToBeConnectedInPropertyWithName)) {
            $entityClassNameToBeConnected = $this->entityClassNameToBeConnected;
        } else {
            if (!$entityClassNameToBeConnected = $owner->valueForPropertyWithName($this->entityClassNameToBeConnectedInPropertyWithName)) return null;
        }

        $entityManagerName = $entityClassNameToBeConnected::entityInformation()->managerName;

        if ($propertyValue) {
            return $entityManagerName::entityWithPrimaryPropertyValue($propertyValue, true);
        } else {
            return $this->autoCreate ? $entityManagerName::entityFromArray(array()) : null;
        }
    }


    /**
     * @param \obo\Entity $owner
     * @param strong $foreignPropertyName
     * @return \obo\Entity|null
     */
    public function entityForOwnerForeignProperty(\obo\Entity $owner, $foreignPropertyName) {
        if (\is_null($owner->primaryPropertyValue())) return null;
        $this->owner = $owner;

        if (\is_null($this->entityClassNameToBeConnectedInPropertyWithName)) {
            $entityClassNameToBeConnected = $this->entityClassNameToBeConnected;
        } else {
            if (!$entityClassNameToBeConnected = $owner->valueForPropertyWithName($this->entityClassNameToBeConnectedInPropertyWithName)) return null;
        }

        $entityManagerName = $entityClassNameToBeConnected::entityInformation()->managerName;

        return $entityManagerName::findEntity($entityManagerName::querySpecification()->where("{{$foreignPropertyName}} = %s", $owner->primaryPropertyValue()), false);
    }
}
