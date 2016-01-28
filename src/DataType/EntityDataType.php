<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class EntityDataType extends \obo\DataType\Base\DataType {

    public $className = null;

    /**
     * @return string
     */
    public static function name() {
        return "entity";
    }

    /**
     * @return string
     */
    public static function dataTypeClass() {
        return \obo\Interfaces\IDataType::DATA_TYPE_CLASS_ENTITY;
    }

    /**
     * @return array
     */
    public static function optionsStructure() {
        return ["className" => false];
    }

    /**
     * @param mixed $value
     * @param bool $throwException
     * @return bool
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if ($value === null OR \is_string($value) OR \is_integer($value) OR ($value instanceof \obo\Entity AND ($this->className === null OR $value instanceof $this->className))) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write " . (\is_object($value) ? "object of class '" . \get_class($value) : "value '" . print_r($value, true)) . "' into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of '" . (($this->className === null) ? "entity" : $this->className) . "' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @throws \obo\Exceptions\Exception
     */
    public static function convertValue($value) {
        throw new \obo\Exceptions\Exception("Datatype 'Entity' can't convert any value.");
    }
}
