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
     * @var string
     */
    public $connectViaProperty = "";

    /**
     * @var string
     */
    public $ownerNameInProperty = "";

    /**
     * @param string $entityClassNameToBeConnected
     * @param string $ownerPropertyName
     * @param array $cascade
     */
    public function __construct($entityClassNameToBeConnected, $ownerPropertyName, array $cascade = []) {
        if (\strpos($entityClassNameToBeConnected, "property:") === 0) {
            $this->entityClassNameToBeConnectedInPropertyWithName = \substr($entityClassNameToBeConnected, 9);
            $entityClassNameToBeConnected = "";
        }
        parent::__construct($entityClassNameToBeConnected, $ownerPropertyName, $cascade);
    }

    /**
     * @param \obo\Entity $owner
     * @param mixed $propertyValue
     * @param boolean $autoCreate
     * @return \obo\Entity|null
     */
    public function relationshipForOwnerAndPropertyValue(\obo\Entity $owner, $propertyValue, $autoCreate = true) {
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
            return ($autoCreate AND $this->autoCreate AND !$owner->isDeleted()) ? $entityManagerName::entityFromArray([]) : null;
        }
    }

    /**
     * @param \obo\Entity $owner
     * @param array $foreignKey
     * @param boolean $autoCreate
     * @return \obo\Entity|null
     */
    public function entityForOwnerForeignKey(\obo\Entity $owner, array $foreignKey, $autoCreate = true) {
        if ($owner->primaryPropertyValue() === null) return null;
        $this->owner = $owner;

        if ($this->entityClassNameToBeConnectedInPropertyWithName === null) {
            $entityClassNameToBeConnected = $this->entityClassNameToBeConnected;
        } else {
            if (!$entityClassNameToBeConnected = $owner->valueForPropertyWithName($this->entityClassNameToBeConnectedInPropertyWithName)) return null;
        }

        $entityManagerName = $entityClassNameToBeConnected::entityInformation()->managerName;

        $specification = $entityManagerName::querySpecification();

        if (\count($foreignKey) === 1) {
            $specification->where("{{$foreignKey[0]}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $owner->primaryPropertyValue());
        } else {
            $this->ownerNameInProperty = $foreignKey[1];
            $specification->where("{{$foreignKey[0]}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER . " AND {{$foreignKey[1]}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $owner->primaryPropertyValue(), $owner->entityInformation()->className);
        }

        if ($entity = $entityManagerName::findEntity($specification, false)) {
            return $entity;
        } else {
            return ($autoCreate AND $this->autoCreate AND !$owner->isDeleted()) ? $entityManagerName::entityFromArray([$foreignKey[0] => $owner]) : null;
        }
    }

}
