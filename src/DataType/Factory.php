<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class Factory extends \obo\Object {

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\BooleanDataType
     */
    public static function createBooleanDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\BooleanDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\IntegerDataType
     */
    public static function createIntegerDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\IntegerDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\FloatDataType
     */
    public static function createFloatDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\FloatDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\NumberDataType
     */
    public static function createNumberDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\NumberDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\StringDataType
     */
    public static function createStringDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\StringDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\DateTimeDataType
     */
    public static function createDateTimeDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\DateTimeDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\ArrayDataType
     */
    public static function createArrayDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\ArrayDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\MixedDataType
     */
    public static function createMixedDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\MixedDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param string $className
     * @return \obo\DataType\ObjectDataType
     */
    public static function createObjectDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation, $className = null) {
        return new \obo\DataType\ObjectDataType($propertyInformation, $className);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param null $className
     * @return \obo\DataType\EntityDataType
     */
    public static function createEntityDataType(\obo\Carriers\PropertyInformationCarrier $propertyInformation, $className = null) {
        return new \obo\DataType\EntityDataType($propertyInformation, $className);
    }
}
