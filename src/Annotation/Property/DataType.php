<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class DataType extends \obo\Annotation\Base\Property {

    /**
     * @return string
     */
    public static function name() {
        return "dataType";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return [self::PARAMETERS_NUMBER_DEFINITION => 1];
    }

    /**
     * @param array $values
     * @return void
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function process(array  $values) {
        parent::process($values);

        switch ($values[0]) {
            case "boolean":
                $dataType = $this->createBooleanDataType();
                break;
            case "number":
                $dataType = $this->createNumberDataType();
                break;
            case "string":
                $dataType = $this->createStringDataType();
                break;
            case "dateTime":
                $dataType = $this->createDataTypeDateTime();
                break;
            case "array":
                $dataType = $this->createArrayDataType();
                break;
            case "integer":
                $dataType = $this->createIntegerDataType();
                break;
            case "float":
                $dataType = $this->createFloatDataType();
                break;
            case "object":
                $dataType = $this->createObjectDataType();
                break;
            case "entity":
                $dataType = $this->createEntityDataType();
                break;
            case "mixed":
                $dataType = $this->createMixedDataType();
                break;
            default :
                throw new \obo\Exceptions\BadDataTypeException("Data type '{$values[0]}' is not allowed.");
        }

        $this->propertyInformation->dataType = $dataType ;
    }

    /**
     * @return \obo\DataType\BooleanDataType
     */
    protected function createBooleanDataType() {
        return \obo\DataType\Factory::createBooleanDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\IntegerDataType
     */
    protected function createIntegerDataType() {
        return \obo\DataType\Factory::createIntegerDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\FloatDataType
     */
    protected function createFloatDataType() {
        return \obo\DataType\Factory::createFloatDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\NumberDataType
     */
    protected function createNumberDataType() {
        return \obo\DataType\Factory::createNumberDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\StringDataType
     */
    protected function createStringDataType() {
        return \obo\DataType\Factory::createStringDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\DateTimeDataType
     */
    protected function createDataTypeDateTime() {
        return \obo\DataType\Factory::createDateTimeDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\ArrayDataType
     */
    protected function createArrayDataType() {
        return \obo\DataType\Factory::createArrayDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\ObjectDataType
     */
    protected function createObjectDataType() {
        return \obo\DataType\Factory::createObjectDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\EntityDataType
     */
    protected function createEntityDataType() {
        return \obo\DataType\Factory::createEntityDataType($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\MixedDataType
     */
    protected function createMixedDataType() {
        return \obo\DataType\Factory::createMixedDataType($this->propertyInformation);
    }
}
