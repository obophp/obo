<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType\Base;

abstract class DataType extends \obo\Object {

    /**
     * @var \obo\Carriers\PropertyInformationCarrier
     */
    protected $propertyInformation = null;

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     */
    public function __construct(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        $this->propertyInformation = $propertyInformation;
    }

    /**
     * @return string
     */
    public abstract function name();

    /**
     * @param mixed $value
     * @param bolean $throwException
     * @throws \obo\Exceptions\BadDataTypeException
     * @return boolean
     */
    public abstract function validate($value, $throwException = true);

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function convertValue($value) {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeValue($value) {
        return $value;
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }
}
