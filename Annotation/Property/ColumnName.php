<?php

/**

 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class ColumnName extends \obo\Annotation\Base\Property {

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
        return array("numberOfParameters" => 1);
    }

    /**
     * @param array $values
     * @return void
     */
    public function proccess($values) {
        parent::proccess($values);
        $this->propertyInformation->columnName = $this->columnName = $values[0];
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }
}