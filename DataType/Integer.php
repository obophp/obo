<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class Integer extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public function name() {
        return "integer";
    }

    /**
     * @param mixed $value
     * @throws \obo\Exceptions\BadDataTypeException
     * @return void
     */
    public function validate($value) {
        if (!\is_numeric($value) || !\is_int($value * 1)) throw new \obo\Exceptions\BadDataTypeException("Value for property with name '{$this->propertyInformation->name}' must be of integer data type. Given value couldn't be converted.");
    }

    /**
     * @param mixed $arguments
     * @return void
     */
    public function convertValue($value) {
        return (int) $value;
    }

    /**
     * @return void
     */
    public function registerEvents() {
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeWrite" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->validate($arguments["propertyValue"]["new"]);
                $arguments["propertyValue"]["new"] = $arguments["dataType"]->convertValue($arguments["propertyValue"]["new"]);
            },
            "actionArguments" => array("dataType" => $this),
        )));
    }
}