<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

abstract class Relationship extends \obo\BaseObject {

    /**
     * @var \obo\Entity
     */
    public $owner = null;

    /**
     * @var string
     */
    public $entityClassNameToBeConnected = "";

    /**
     * @var string
     */
    public $ownerPropertyName = "";

    /**
     * @var array
     */
    public $cascade = null;

    /**
     * @param string $entityClassNameToBeConnected
     * @param string $ownerPropertyName
     * @param array $cascade
     */
    public function __construct($entityClassNameToBeConnected, $ownerPropertyName, array $cascade = []) {
        $this->entityClassNameToBeConnected = $entityClassNameToBeConnected;
        $this->ownerPropertyName = $ownerPropertyName;
        $this->cascade = $cascade;
    }

    /**
     * @param \obo\Entity $owner
     * @param mixed $propertyValue
     */
    public abstract function relationshipForOwnerAndPropertyValue(\obo\Entity $owner, $propertyValue);

}
