<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Roman Pavlík
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class Serialize extends \obo\Annotation\Base\Property {

    /**
     * @return string
     */
    public static function name() {
        return "serialize";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return array("numberOfParameters" => 0);
    }

    /**
     * @param array $values
     * @return void
     */
    public function proccess($values) {
        parent::proccess($values);
        $this->entityInformation->propertiesForSerialization[] = $this->propertyInformation->name;
    }

    /**
     * @return void
     */
    public function registerEvents() {}
}