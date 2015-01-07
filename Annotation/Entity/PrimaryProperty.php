<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Entity;

class PrimaryProperty extends \obo\Annotation\Base\Entity {

    /**
     * @var string
     */
    protected $primaryPropertyName = "";

    /**
     * @return string
     */
    public static function name() {
        return "primaryProperty";
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
        $this->entityInformation->primaryPropertyName = $this->primaryPropertyName = $values[0];
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

}