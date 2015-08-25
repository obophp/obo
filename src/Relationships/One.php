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
            return ($this->autoCreate AND !$owner->isDeleted()) ? $entityManagerName::entityFromArray([]) : null;
        }
    }

    /**
     * @param \obo\Entity $owner
     * @param string $foreignKey
     * @return \obo\Entity|null
     */
    public function entityForOwnerForeignKey(\obo\Entity $owner, $foreignKey) {
        if ($owner->primaryPropertyValue() === null) return null;
        $this->owner = $owner;

        $entityClassNameToBeConnected = $this->entityClassNameToBeConnected;
        $entityManagerName = $entityClassNameToBeConnected::entityInformation()->managerName;

        $query = $entityManagerName::querySpecification();

        if (\strpos($foreignKey, ",") === false) {
            $query->where("{{$foreignKey}} = %s", $owner->primaryPropertyValue());
        } else {
            $foreignKey = \explode(",", $foreignKey);
            $query->where("{{$foreignKey[0]}} = %s AND {{$foreignKey[1]}} = %s", $owner->primaryPropertyValue(), $owner->entityInformation()->className);
        }

        if ($entity = $entityManagerName::findEntity($query, false)) {
            return $entity;
        } else {
            return ($this->autoCreate AND !$owner->isDeleted()) ? $entityManagerName::entityFromArray([$foreignKey[0] => $owner]) : null;
        }
    }
}
