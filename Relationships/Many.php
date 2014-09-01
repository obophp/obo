<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

class Many extends \obo\Relationships\Relationship {
    public $connectViaPropertyWithName = null;
    public $ownerNameInProperty = null;
    public $connectViaRepositoryWithName = null;
    public $sortVia = null;

    /**
     * @param \obo\Entity $owner
     * @param mixed $ownerPropertyValue
     * @return \obo\Relationships\EntitiesCollection
     */
    public function relationshipForOwnerAndPropertyValue(\obo\Entity $owner, $propertyValue) {
        $relationship = clone $this;
        $relationship->owner = $owner;

        return new \obo\Relationships\EntitiesCollection($owner, $relationship);
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return \obo\Entity
     */
    public function findEntities(\obo\Carriers\QuerySpecification $specification = null) {

        if (\is_null($specification)) $specification = new \obo\Carriers\QuerySpecification();

        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;

        return $ownedEntityManagerName::findEntities($this->constructQuery(\obo\Carriers\QueryCarrier::instance()->addSpecification($specification)));
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return int
     */
    public function countEntities (\obo\Carriers\QuerySpecification $specification = null) {
        if (\is_null($specification)) $specification = new \obo\Carriers\QuerySpecification();

        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        return $ownedEntityManagerName::countRecords($this->constructQuery(\obo\Carriers\QueryCarrier::instance()->addSpecification($specification)));
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return \obo\Carriers\QueryCarrier
     */
    protected function constructQuery(\obo\Carriers\QueryCarrier $specification = null) {
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownerPrimaryPropertyName = $this->owner->entityInformation()->primaryPropertyName;
        $ownerPropertyNameForSoftDelete = $ownedEntityClassName::entityInformation()->propertyNameForSoftDelete;
        $softDeleteJoinQuery = "";

        $query = \is_null($specification) ? \obo\Carriers\QueryCarrier::instance() : $specification;

        if (!\is_null($this->connectViaPropertyWithName)){
            $query->where("AND {{$this->connectViaPropertyWithName}} = %s", $this->owner->$ownerPrimaryPropertyName);
            if (!\is_null($this->ownerNameInProperty)) $query->where("AND {{$this->ownerNameInProperty}} = %s", $this->owner->className());
        } elseif (!\is_null($this->connectViaRepositoryWithName)){
            if(!\is_null($ownerPropertyNameForSoftDelete)) {
                $softDeleteJoinQuery = "AND [{$ownedEntityClassName::entityInformation()->repositoryName}].[{$ownedEntityClassName::informationForPropertyWithName($ownerPropertyNameForSoftDelete)->columnName}] = 0";
            }

            $query->join("JOIN [{$this->connectViaRepositoryWithName}] ON [{$this->owner->entityInformation()->repositoryName}] = %s AND [{$ownedEntityClassName::entityInformation()->repositoryName}] = [{$ownedEntityClassName::entityInformation()->primaryPropertyName}]" . $softDeleteJoinQuery, $this->owner->$ownerPrimaryPropertyName);
        }

        if (!\is_null($this->sortVia)) $query->orderBy($this->sortVia);

        return $query;
    }

}