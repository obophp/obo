<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

abstract class Relationship extends \obo\Object {

    /**
     * @var \obo\Entity
     */
    public $owner = null;

    /**
     * @var type
     */
    public $entityClassNameToBeConnected = "";
    public $ownerPropertyName = "";
    public $cascade = array();

    /**
     * @param string $entityClassNameToBeConnected
     * @param string $ownerPropertyName
     * @param array $cascade
     * @return void
     */
    public function __construct($entityClassNameToBeConnected, $ownerPropertyName, array $cascade = array()){
        $this->entityClassNameToBeConnected = $entityClassNameToBeConnected;
        $this->ownerPropertyName = $ownerPropertyName;
        $this->cascade = new \obo\Carriers\DataCarrier($cascade);
    }

    /**
     * @param \obo\Entity $owner
     * @param mixed $propertyValue
     */
    public abstract function relationshipForOwnerAndPropertyValue(\obo\Entity $owner, $propertyValue);

}
