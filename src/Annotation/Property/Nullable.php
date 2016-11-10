<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class Nullable extends \obo\Annotation\Base\Property {

    /**
     * @return string
     */
    public static function name() {
        return "nullable";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return [self::PARAMETERS_NUMBER_DEFINITION => self::ONE_OR_MORE_PARAMETERS];
    }

    /**
     * @param array $values
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function process(array $values) {
        parent::process($values);
        if (\is_bool($values[0]) === false) throw new \obo\Exceptions\BadAnnotationException("Parameter for '". self::name() . "' annotation must be of boolean type");
        $this->propertyInformation->nullable = $values[0];
    }

}
