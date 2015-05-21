<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class Number extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public function name() {
        return "number";
    }

    /**
     * @param mixed $value
     * @param boolean $throwException
     * @return boolean
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if (\is_numeric($value) OR \is_null($value)) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write  value '" . print_r($value, true) . "' of '" . \gettype($value) . "' datatype into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of 'numeric' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function convertValue($value) {
        throw new \obo\Exceptions\Exception("Datatype 'Number' can't convert any value.");
    }
}
