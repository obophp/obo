<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class MixedDataType extends \obo\DataType\Base\DataType {

    /**
     * @return string
     */
    public static function name() {
        return "mixed";
    }

    /**
     * @return string
     */
    public static function dataTypeClass() {
        return \obo\Interfaces\IDataType::DATA_TYPE_CLASS_MIXED;
    }

    /**
     * @param mixed $value
     * @param bool $throwException
     * @return bool
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function validate($value, $throwException = true) {
        return true;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public static function convertValue($value) {
        return $value;
    }

    /**
     * @return bool
     */
    public function storageDataCompression() {
        return false;
    }

}
