<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
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
     * @return boolean
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        if (\is_bool($value) OR \is_null($value)) return true;
        if ($throwException) throw new \obo\Exceptions\BadDataTypeException("Can't write  value '" . print_r($value, true) . "' of '" . \gettype($value) . "' datatype into property '" . $this->propertyInformation->name . "' in class '" . $this->propertyInformation->entityInformation->className . "' which is of 'boolean' datatype.");
        return false;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public static function convertValue($value) {
        return \is_null($value) ? $value : \filter_var($value, \FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public static function sanitizeValue($value) {
        if (!($value !== false AND $value !== true  AND $value !== "false"  AND $value !== "true") OR \is_null($value)) return self::convertValue($value);
        return $value;
    }
}
