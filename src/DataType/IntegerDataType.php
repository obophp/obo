<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class IntegerDataType extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public static function name() {
        return "integer";
    }

    /**
     * @return string
     */
    public static function dataTypeClass() {
        return \obo\Interfaces\IDataType::DATA_TYPE_CLASS_INTEGER;
    }

    /**
     * @param mixed $value
     * @param bool $throwException
     * @return bool
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if (\is_int($value) OR $value === null) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write  value " . (\is_scalar($value) ? "'" . print_r($value, true) . "'" : "") . " of '" . \gettype($value) . "' datatype into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of 'integer' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @return integer
     */
    public static function convertValue($value) {
        return ($value === null OR $value === "") ? null : (int) $value;
    }

    /**
     * @param mixed $value
     * @return integer
     */
    public static function sanitizeValue($value) {
        if (((\is_numeric($value) AND \is_int($value * 1))) OR $value === null OR $value === "") return self::convertValue($value);
        return $value;
    }

}
