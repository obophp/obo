<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Roman Pavlík
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class StoreTo extends \obo\Annotation\Base\Property {

    /**
     * @var string
     */
    private $propertyToStore;

    /**
     * @return string
     */
    public static function name() {
        return "storeTo";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return array("numberOfParameters" => 1);
    }

    /**
     * @param array $values
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function process($values) {
        parent::process($values);
        $this->propertyToStore = $values[0];
    }

    /**
     * @param array $arguments
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @return void
     */
    public function fromArray(array $arguments) {
        $propertiesInformation = $arguments["entity"]->propertiesInformation();

        if (!isset($propertiesInformation[$this->propertyToStore]))
            throw new \obo\Exceptions\PropertyNotFoundException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' does not exists");

        if (!$propertiesInformation[$this->propertyToStore]->dataType instanceof \obo\DataType\ArrayDataType)
            throw new \obo\Exceptions\BadDataTypeException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' must be Obo Array dataType");

        $rawData = $arguments["entity"]->valueForPropertyWithName($this->propertyToStore);
        if (isset($rawData[$this->propertyInformation->name])) {
            $arguments["entity"]->setValueForPropertyWithName($rawData[$this->propertyInformation->name], $this->propertyInformation->name, false);
        }
    }

    /**
     * @param array $arguments
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @return void
     */
    public function toArray(array $arguments) {
        $propertiesInformation = $arguments["entity"]->propertiesInformation();

        if (!isset($propertiesInformation[$this->propertyToStore]))
            throw new \obo\Exceptions\PropertyNotFoundException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' does not exists");

        if (!$propertiesInformation[$this->propertyToStore]->dataType instanceof \obo\DataType\ArrayDataType)
            throw new \obo\Exceptions\BadDataTypeException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' must be Obo Array dataType");

        $rawData = $arguments["entity"]->valueForPropertyWithName($this->propertyToStore);
        $rawData[$this->propertyInformation->name] = $arguments["entity"]->valueForPropertyWithName($this->propertyInformation->name, true, false);
        $arguments["entity"]->setValueForPropertyWithName($rawData, $this->propertyToStore);
    }

    /**
     * @return void
     */
    public function registerEvents() {
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeRead" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $arguments["annotation"]->fromArray($arguments);
            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name, "annotation" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $arguments["annotation"]->toArray($arguments);
            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name, "annotation" => $this),
        )));
    }

}
