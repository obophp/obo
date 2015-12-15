<?php
/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;


class AutoIncrement extends \obo\Annotation\Base\Property {

    /**
     * @return string
     */
    public static function name() {
        return "autoIncrement";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return [self::PARAMETERS_NUMBER_DEFINITION => self::ZERO_OR_ONE_PARAMETER];
    }

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function validate(\obo\Services\EntitiesInformation\Explorer $explorer) {
        if (!$this->propertyInformation->dataType instanceof \obo\DataType\IntegerDataType) {
            throw new \obo\Exceptions\BadAnnotationException("Annotation '" . self::name() . "' can be used only with 'integer' dataType.");
        }
    }

    /**
     * @param array $values
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function process(array $values) {
        parent::process($values);
        if (\is_bool($values[0]) === false) throw new \obo\Exceptions\BadAnnotationException("Parameter for '" . self::name() . "' annotation must be of boolean type");
        $this->propertyInformation->autoIncrement = $values[0];
    }

}
