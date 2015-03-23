<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class ArrayDataType extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public function name() {
        return "array";
    }

    /**
     * @param mixed $value
     * @throws \obo\Exceptions\BadDataTypeException
     * @return void
     */
    public function validate($value) {
        parent::validate($value);
        if (!\is_array($value)) throw new \obo\Exceptions\BadDataTypeException("Value for property with name '{$this->propertyInformation->name}' must be of array data type.'");
    }

    /**
     * @param mixed $arguments
     * @return void
     */
    public function serialize($arguments) {
        $arguments["entity"]->setValueForPropertyWithName(serialize($arguments["entity"]->valueForPropertyWithName($this->propertyInformation->name)), $this->propertyInformation->name, false);
    }

    /**
     * @param mixed $arguments
     * @return void
     */
    public function unserialize($arguments) {
        $value = $arguments["entity"]->valueForPropertyWithName($this->propertyInformation->name);
        if ($value === "" || !is_string($value)) $value = serialize(array());
        $arguments["entity"]->setValueForPropertyWithName(unserialize($value), $this->propertyInformation->name, false);
    }

    /**
     * @return void
     */
    public function registerEvents() {
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeInitialize",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->unserialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeWrite" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->validate($arguments["propertyValue"]["new"]);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeInsert",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->serialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeUpdate",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->serialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "afterInsert",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->unserialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "afterUpdate",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->unserialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));
    }
}
