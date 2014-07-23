<?php
 
/** 
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
 
namespace obo\Annotation\Entity;
 
class SoftDeletable extends \obo\Annotation\Base\Entity {
    
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
     */
    public function proccess($values) {
        parent::proccess($values);
        
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