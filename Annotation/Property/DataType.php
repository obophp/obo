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
            case "integer":
                    $dataType = $this->createDataTypeInteger();
                break;
            case "float":
                    $dataType = $this->createDataTypeFloat();
                break;
            default :
                throw new \obo\Exceptions\BadDataTypeException("Data type '{$values[0]}' is not allowed.");
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
     * @return \obo\DataType\Integer
     */
    protected function createDataTypeInteger(){
        return new \obo\DataType\Integer($this->propertyInformation);
    }

    /**
     * @return \obo\DataType\Float
     */
    protected function createDataTypeFloat(){
        return new \obo\DataType\Float($this->propertyInformation);
    }


    /**
     * @return \obo\DataType\Number\
     * @deprecated
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