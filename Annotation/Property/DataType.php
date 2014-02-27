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
     * @return void
     */
    public function proccess($values) {
        parent::proccess($values);

        switch ($values[0]) {
            case "boolean" :
                    $dataType = new \obo\DataType\Boolean($this->propertyInformation);
                break;
            case "number" :
                    $dataType = new \obo\DataType\Number($this->propertyInformation);
                break;
            case "string" :
                    $dataType = new \obo\DataType\String($this->propertyInformation);
                break;
            case "dateTime" :
                    $dataType = new \obo\DataType\DateTime($this->propertyInformation);
                break;
            default :
                throw new \obo\Exceptions\BadDataTypeException("'{$values[0]}' is not allowed, permitted data types are boolean, number, string, dateTime");
                break;

        }

        $this->propertyInformation->dataType = $dataType ;
    }
}