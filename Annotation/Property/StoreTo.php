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
    public function proccess($values) {
        parent::proccess($values);
        $this->propertyToStore = $values[0];
    }

    /**
     * @param array $arguments
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function distribute(array $arguments) {
        $propertiesInformation = $arguments["entity"]->propertiesInformation();

        if (!isset($propertiesInformation[$this->propertyToStore]))
            throw new \obo\Exceptions\PropertyNotFoundException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' does not exists");

        if (!$propertiesInformation[$this->propertyToStore]->dataType instanceof \obo\DataType\ArrayDataType)
            throw new \obo\Exceptions\BadDataTypeException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' must be Obo Array dataType");

        $rawData = ($arguments["entity"]->valueForPropertyWithName($this->propertyToStore));

        //if (!is_array($rawData)) $rawData = unserialize($rawData);

        if(isset($rawData[$this->propertyInformation->name])) {
            $arguments["entity"]->setValueForPropertyWithName($rawData[$this->propertyInformation->name], $this->propertyInformation->name, false);
            //unset($rawData[$this->propertyInformation->name]);
            //\Tracy\Debugger::barDump($rawData);
            //$arguments["entity"]->setValueForPropertyWithName($rawData, $this->propertyToStore, false);
        }
    }

    /**
     * @param array $arguments
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function merge(array $arguments) {
        $propertiesInformation = $arguments["entity"]->propertiesInformation();

        if (!isset($propertiesInformation[$this->propertyToStore]))
            throw new \obo\Exceptions\PropertyNotFoundException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' does not exists");

        if (!$propertiesInformation[$this->propertyToStore]->dataType instanceof \obo\DataType\ArrayDataType)
            throw new \obo\Exceptions\BadDataTypeException("Property '$this->propertyToStore' of entity '{$this->entityInformation->className}' must be Obo Array dataType");

        $rawData = $arguments["entity"]->valueForPropertyWithName($this->propertyToStore);
        //\Tracy\Debugger::barDump($rawData);
        $rawData[$this->propertyInformation->name] = $arguments["entity"]->valueForPropertyWithName($this->propertyInformation->name);
        $arguments["entity"]->setValueForPropertyWithName($rawData, $this->propertyToStore, false);
    }

    /**
     * @return void
     */
    public function registerEvents() {
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "afterInitialize",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["annotation"]->distribute($arguments);
            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name, "annotation" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeSave",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["annotation"]->merge($arguments);
            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name, "annotation" => $this),
        )));
    }
}
