<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class EntityInformationCarrier extends \obo\Object {

    /**
     * @var string
     */
    public $className = "";

    /**
     * @var boolean
     */
    public $isAbstract = null;

    /**
     * @var string
     */
    public $file = "";

    /**
     * @var string
     */
    public $managerName = "";

    /**
     * @var string
     */
    public $propertiesClassName = "";

    /**
     * @var boolean
     */
    public $isPropertiesAbstract = null;

    /**
     * @var string
     */
    public $propertiesFile = "";

    /**
     * @var string
     */
    public $repositoryName = "";

    /**
     * @var string
     */
    public $primaryPropertyName = "";

    /**
     * @var array
     */
    public $propertiesInformation = [];

    /**
     * @var array
     */
    public $annotations = [];

    /**
     * @var array
     */
    public $propertiesNames = [];

    /**
     * @var array
     */
    public $persistablePropertiesNames = [];

    /**
     * @var string
     */
    public $propertyNameForSoftDelete = "";

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return void
     */
    public function addPropertyInformation(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        $propertyInformation->entityInformation = $this;
        $this->propertiesInformation[$propertyInformation->name] = $propertyInformation;

        $this->propertiesNames[$propertyInformation->name] = $propertyInformation->name;
        if ($propertyInformation->persistable === true) $this->persistablePropertiesNames[$propertyInformation->name] = $propertyInformation->name;
    }

    /**
     * @param string $propertyName
     * @return boolean
     */
    public function existInformationForPropertyWithName($propertyName) {
        return isset($this->propertiesInformation[$propertyName]);
    }

    /**
     * @param string $propertyName
     * @return \obo\Carriers\PropertyInformationCarrier
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function informationForPropertyWithName($propertyName) {
        if (!$this->existInformationForPropertyWithName($propertyName)) throw new \obo\Exceptions\PropertyNotFoundException("Property with name '{$propertyName}' does not exist in entity '{$this->className}'");
        return $this->propertiesInformation[$propertyName];
    }
}
