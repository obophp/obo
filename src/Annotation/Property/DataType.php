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
        return ["numberOfParameters" => 1];
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
                $dataType = $this->createDataTypeBoolean();
                break;
            case "number":
                $dataType = $this->createDataTypeNumber();
                break;
            case "string":
                $dataType = $this->createDataTypeString();
                break;
            case "dateTime":
                $dataType = $this->createDataTypeDateTime();
                break;
            case "array":
                $dataType = $this->createDataTypeArray();
                break;
            case "integer":
                $dataType = $this->createDataTypeInteger();
                break;
            case "float":
                $dataType = $this->createDataTypeFloat();
                break;
            case "object":
                $dataType = $this->createDataTypeObject();
                break;
            case "entity":
                $dataType = $this->createDataTypeEntity();
                break;
            case "mixed":
                $dataType = $this->createDataTypeMixed();
                break;
            default :
                throw new \obo\Exceptions\BadDataTypeException("Data type '{$values[0]}' is not allowed.");
        }

        $this->propertyInformation->dataType = $dataType ;
    }

    /**
     * @return \obo\DataType\Boolean
     */
    protected function createDataTypeBoolean() {
        return \obo\DataType\Factory::createDataTypeBoolean($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Integer
     */
    protected function createDataTypeInteger() {
        return \obo\DataType\Factory::createDataTypeInteger($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Float
     */
    protected function createDataTypeFloat() {
        return \obo\DataType\Factory::createDataTypeFloat($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Number
     */
    protected function createDataTypeNumber() {
        return \obo\DataType\Factory::createDataTypeNumber($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\String
     */
    protected function createDataTypeString() {
        return \obo\DataType\Factory::createDataTypeString($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\DateTime
     */
    protected function createDataTypeDateTime() {
        return \obo\DataType\Factory::createDataTypeDateTime($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\ArrayDataType
     */
    protected function createDataTypeArray() {
        return \obo\DataType\Factory::createDataTypeArray($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Object
     */
    protected function createDataTypeObject() {
        return \obo\DataType\Factory::createDataTypeObject($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Entity
     */
    protected function createDataTypeEntity() {
        return \obo\DataType\Factory::createDataTypeEntity($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Mixed
     */
    protected function createDataTypeMixed() {
        return \obo\DataType\Factory::createDataTypeMixed($this->propertyInformation);
    }
}
