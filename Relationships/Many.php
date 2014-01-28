<?php

/** 
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

class Many extends \obo\Relationships\Relationship{
    public $connectViaPropertyWithName = null;
    public $ownerNameInProperty = null;
    public $connectViaRepositoryWithName = null;
    public $sortVia = null;
        
    /**
     * @param \obo\Entity $owner
     * @param type $ownerPropertyValue
     * @return \obo\Relationships\EntitiesCollection 
     */
    public function relationshipForOwnerAndPropertyValue(\obo\Entity $owner, $ownerPropertyValue) {
        $this->owner = $owner;
        $this->ownerPropertyValue = $ownerPropertyValue;
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        $ownerPrimaryPropertyName = $owner->entityInformation()->primaryPropertyName;
        
        if (!\is_null($this->connectViaPropertyWithName)){
            $query = \obo\Carriers\QueryCarrier::instance()
                    ->where("{{$this->connectViaPropertyWithName}} = %s", $owner->$ownerPrimaryPropertyName);
            if (!\is_null($this->ownerNameInProperty)) $query->where("AND {{$this->ownerNameInProperty}} = %s", $owner->className());
        } elseif (!\is_null($this->connectViaRepositoryWithName)){
            $query = \obo\Carriers\QueryCarrier::instance()
                    ->join("JOIN [{$this->connectViaRepositoryWithName}] ON [{$owner->entityInformation()->repositoryName}] = %s AND [{$ownedEntityClassName::entityInformation()->repositoryName}] = [{$ownedEntityClassName::entityInformation()->primaryPropertyName}]", $owner->$ownerPrimaryPropertyName);  
        }
        
        if (!\is_null($this->sortVia)) $query->orderBy($this->sortVia);

        $entities = $ownedEntityManagerName::findEntities($query);  

        $entitiesCollection = new \obo\Relationships\EntitiesCollection($this); 
        foreach ($entities as $entity) $entitiesCollection->add($entity, false, false);
        return $entitiesCollection;   
    } 
}