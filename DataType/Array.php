<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Roman Pavlík, ZZromanZZ@gmail.com
 * @copyright (c) 2011 - 2013 Adam Suba
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
        if (!\is_array($value)) throw new \obo\Exceptions\BadDataTypeException("New value for property with name '{$this->propertyInformation->name}' must be an array, " . \gettype($value) . " given");
    }

    /**
     * @param mixed $arguments
     */
    public function serialize($arguments) {
        $arguments["entity"]->setValueForPropertyWithName(serialize($arguments["entity"]->valueForPropertyWithName($this->propertyInformation->name)), $this->propertyInformation->name, false);
    }

    /**
     * @param mixed $arguments
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
            "name" => "beforeChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->validate($arguments["propertyValue"]["new"]);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeSave",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->serialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "afterSave",
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->unserialize($arguments);
            },
            "actionArguments" => array("dataType" => $this),
        )));
    }
}
