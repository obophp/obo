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
        return [self::PARAMETERS_NUMBER_DEFINITION => self::ONE_OR_MORE_PARAMETERS];
    }

    /**
     * @param array $values
     * @return void
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function process(array  $values) {
        parent::process($values);

        if (count($values) > 1) {
            $dataTypeName = \array_shift($values);
            $dataTypeOptions = $values;
        } else {
            $dataTypeName = $values[0];
            $dataTypeOptions = [];
        }

        $this->propertyInformation->dataType = \obo\obo::$entitiesExplorer->createDataType($dataTypeName, $this->propertyInformation, $dataTypeOptions);
    }

}
