<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
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

}
