<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class FloatDataType extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public static function name() {
        return "float";
    }

    /**
     * @return string
     */
    public static function dataTypeClass() {
        return \obo\Interfaces\IDataType::DATA_TYPE_CLASS_FLOAT;
    }

    /**
     * @param mixed $value
     * @param bool $throwException
     * @return bool
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if (\is_float($value) OR $value === null) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write  value " . (\is_scalar($value) ? "'" . print_r($value, true) . "'" : "") . " of '" . \gettype($value) . "' datatype into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of 'float' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @return float
     */
    public static function convertValue($value) {
        return ($value === null OR $value === "") ? null : (float) $value;
    }

    /**
     * @param mixed $value
     * @return float
     */
    public static function sanitizeValue($value) {
        if ((\is_numeric($value) AND \is_float($value + 0.0)) OR $value === null OR $value === "") return self::convertValue($value);
        return $value;
    }

    /**
     * @return bool
     */
    public function storageDataCompression() {
        return false;
    }

}
