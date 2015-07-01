<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class BooleanDataType extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public function name() {
        return "boolean";
    }

    /**
     * @param mixed $value
     * @param bool $throwException
     * @return bool
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if (\is_bool($value) OR $value === null) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write  value " . (\is_scalar($value) ? "'" . print_r($value, true) . "'" : "") . " of '" . \gettype($value) . "' datatype into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of 'boolean' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function convertValue($value) {
        return $value === null ? $value : \filter_var($value, \FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function sanitizeValue($value) {
        if (!($value !== false AND $value !== true AND $value !== "false" AND $value !== "true") OR $value === null) return self::convertValue($value);
        return $value;
    }
}
