<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class ColumnName extends \obo\Annotation\Base\Property {

    /**
     * @var string
     */
    protected $columnName = "";

    /**
     * @return string
     */
    public static function name() {
        return "columnName";
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
     */
    public function process(array $values) {
        parent::process($values);
        $this->propertyInformation->columnName = $this->columnName = $values[0];
    }

}
