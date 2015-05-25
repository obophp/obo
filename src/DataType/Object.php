<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class Object extends \obo\DataType\Base\DataType {

    public $className = null;

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param string $className
     */
    function __construct(\obo\Carriers\PropertyInformationCarrier $propertyInformation, $className = null) {
        parent::__construct($propertyInformation);
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function name() {
        return "object";
    }

    /**
     * @param mixed $value
     * @param boolean $throwException
     * @return boolean
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if ((\is_object($value) AND (\is_null($this->className) OR $value instanceof $this->className)) OR \is_null($value)) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write  value '" . (\is_object ($value)) ? \get_class($value) : print_r($value, true) . "' of '" . \gettype($value) . "' datatype into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of '" . ($this->className === null) ? "object" : $this->className . "' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function convertValue($value) {
        return \unserialize($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeValue($value) {
        if (!\is_object($value) AND @$object = \unserialize($value)) return $object;
        return $value;
    }
}
