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
     * @return \obo\DataType\Boolean
     */
    public static function createDataTypeBoolean(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\Boolean($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\Integer
     */
    public static function createDataTypeInteger(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\Integer($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\Float
     */
    public static function createDataTypeFloat(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\Float($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\Number
     */
    public static function createDataTypeNumber(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\Number($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\String
     */
    public static function createDataTypeString(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\String($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\DateTime
     */
    public static function createDataTypeDateTime(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\DateTime($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\ArrayDataType
     */
    public static function createDataTypeArray(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\ArrayDataType($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @return \obo\DataType\Mixed
     */
    public static function createDataTypeMixed(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        return new \obo\DataType\Mixed($propertyInformation);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param string $className
     * @return \obo\DataType\Object
     */
    public static function createDataTypeObject(\obo\Carriers\PropertyInformationCarrier $propertyInformation, $className = null) {
        return new \obo\DataType\Object($propertyInformation, $className = null);
    }

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     * @param null $className
     * @return \obo\DataType\Entity
     */
    public static function createDataTypeEntity(\obo\Carriers\PropertyInformationCarrier $propertyInformation, $className = null) {
        return new \obo\DataType\Entity($propertyInformation, $className = null);
    }
}
