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
     * @var bool
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
     */
    public function __construct($entityClassNameToBeConnected, $ownerPropertyName, array $cascade = []) {
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

        if ($this->entityClassNameToBeConnectedInPropertyWithName === null) {
            $entityClassNameToBeConnected = $this->entityClassNameToBeConnected;
        } else {
            if (!$entityClassNameToBeConnected = $owner->valueForPropertyWithName($this->entityClassNameToBeConnectedInPropertyWithName)) return null;
        }

        $entityManagerName = $entityClassNameToBeConnected::entityInformation()->managerName;

        if ($propertyValue) {
            return $entityManagerName::entityWithPrimaryPropertyValue($propertyValue, true);
        } else {
            return $this->autoCreate ? $entityManagerName::entityFromArray([]) : null;
        }
    }

    /**
     * @param \obo\Entity $owner
     * @param string $foreignPropertyName
     * @return \obo\Entity|null
     */
    public function entityForOwnerForeignProperty(\obo\Entity $owner, $foreignPropertyName) {
        if ($owner->primaryPropertyValue() === null) return null;
        $this->owner = $owner;

        if ($this->entityClassNameToBeConnectedInPropertyWithName === null) {
            $entityClassNameToBeConnected = $this->entityClassNameToBeConnected;
        } else {
            if (!$entityClassNameToBeConnected = $owner->valueForPropertyWithName($this->entityClassNameToBeConnectedInPropertyWithName)) return null;
        }

        $entityManagerName = $entityClassNameToBeConnected::entityInformation()->managerName;

        return $entityManagerName::findEntity($entityManagerName::querySpecification()->where("{{$foreignPropertyName}} = %s", $owner->primaryPropertyValue()), false);
    }
}
