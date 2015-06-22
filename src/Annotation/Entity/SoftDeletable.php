<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Entity;

class SoftDeletable extends \obo\Annotation\Base\Entity {

    /**
     * @return string
     */
    public static function name() {
        return "softDeletable";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return [self::PARAMETERS_NUMBER_DEFINITION => self::ZERO_OR_ONE_PARAMETER];
    }

    /**
     * @param array $values
     * @throws \obo\Exceptions\Exception
     * @return void
     */
    public function process(array $values) {
        parent::process($values);

        $propertyNameForSoftDelete = "deleted";

        if (isset($values[0])) {
            if (is_string($values[0])) {
                $propertyNameForSoftDelete = $values[0];
            } else if (is_bool($values[0])) {
                if (!$values[0]) $propertyNameForSoftDelete = "";
            } else {
                throw new \obo\Exceptions\BadAnnotationException("Annotation '" . self::name() . "' expects single parameter of data type string or boolean");
            }
        }

        $this->entityInformation->propertyNameForSoftDelete = $propertyNameForSoftDelete;
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

}
