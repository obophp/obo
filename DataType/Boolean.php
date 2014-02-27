<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class Boolean extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public function name() {
        return "boolean";
    }

    /**
     * @param mixed $value
     * @throws \obo\Exceptions\BadDataTypeException
     * @return void
     */
    public function validate($value) {
        if (!\is_bool($value)) throw new \obo\Exceptions\BadDataTypeException("New value for property with name '{$this->propertyInformation->name}' must be boolean, " . \gettype($value) . " given");
    }

    /**
     * @return void
     */
    public function registerEvents() {
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->propertyInformation->entityInformation->className,
            "name" => "beforeChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $arguments["dataType"]->validate($arguments["propertyValue"]["new"]);
            },
            "actionArguments" => array("dataType" => $this),
        )));
    }
}
