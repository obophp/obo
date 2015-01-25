<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
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
        return array("numberOfParameters" => 1);
    }

    /**
     * @param array $values
     * @throws \obo\Exceptions\BadDataTypeException
     * @return void
     */
    public function process($values) {
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
            default :
                throw new \obo\Exceptions\BadDataTypeException("'{$values[0]}' is not allowed, permitted data types are boolean, number, string, dateTime and array");
        }

        $this->propertyInformation->dataType = $dataType ;
    }

    /**
     * @return \obo\DataType\Boolean
     */
    protected function createDataTypeBoolean(){
        return new \obo\DataType\Boolean($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Number
     */
    protected function createDataTypeNumber(){
        return new \obo\DataType\Number($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\String
     */
    protected function createDataTypeString(){
        return new \obo\DataType\String($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\DateTime
     */
    protected function createDataTypeDateTime(){
        return new \obo\DataType\DateTime($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\ArrayDataType
     */
    protected function createDataTypeArray(){
        return new \obo\DataType\ArrayDataType($this->propertyInformation);
    }

}
