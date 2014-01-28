<?php

/** 
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\IdentityMapper;

class IdentityMapper extends \obo\Object {
    private $entities = array();
    
    /**
     * @param \obo\Entity $entity
     * @return string
     */
    public function identificationKeyForEntity(\obo\Entity $entity) {
        return $entity->className() . $entity->valueForPropertyWithName($entity->entityInformation()->primaryPropertyName);
    }
    
    /**
     * @param \obo\Entity $entity
     * @return \obo\Entity 
     */
    public function mappedEntity(\obo\Entity $entity) {
        if (isset($this->entities[$entity->entityIdentificationKey()])) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->turnOffIgnoreNotificationForEntity($entity);
            return $this->entities[$entity->entityIdentificationKey()]["entity"];
        } else {
            $this->entities[$entity->entityIdentificationKey()] = array("entity" => $entity);
            return $entity;
        }
    }
        
    /**
     * @param \obo\Entity $entity
     * @return boolean 
     */
    public function isMappedEntity(\obo\Entity $entity) {
        return isset($this->entities[$entity->entityIdentificationKey()]);
    }
           
}