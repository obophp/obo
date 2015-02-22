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
     * @var string
     */
    protected $usePropertyWithName = null;

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
        return array("numberOfParameters" => "?");
    }

    /**
     * @param array $values
     * @return void
     */
    public function process($values) {
        parent::process($values);

        if (isset($values[0])) {
            if ($values[0] === false) {
                 $value = null;
            } else {
                 $value = $values[0];
            }
        } else {
            $value = "deleted";
        }

        $this->entityInformation->propertyNameForSoftDelete = $this->usePropertyWithName = $value;
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

}
