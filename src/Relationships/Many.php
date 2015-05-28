<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

class Many extends \obo\Relationships\Relationship {

    /**
     * @var string
     */
    public $connectViaPropertyWithName = null;

    /**
     * @var string
     */
    public $ownerNameInProperty = null;

    /**
     * @var string
     */
    public $connectViaRepositoryWithName = null;

    /**
     * @var string
     */
    public $sortVia = null;

    /**
     * @param \obo\Entity $owner
     * @param mixed $propertyValue
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
        return $ownedEntityManagerName::findEntities($this->constructSpecification(\obo\Carriers\QueryCarrier::instance()->addSpecification($specification)));
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return int
     */
    public function countEntities (\obo\Carriers\QuerySpecification $specification = null) {
        if (\is_null($specification)) $specification = new \obo\Carriers\QuerySpecification();
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        return $ownedEntityManagerName::countRecords($this->constructSpecification(\obo\Carriers\QueryCarrier::instance()->addSpecification($specification)));
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return \obo\Carriers\QueryCarrier
     */
    public function constructSpecification(\obo\Carriers\QueryCarrier $specification = null) {
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownerPrimaryPropertyName = $this->owner->entityInformation()->primaryPropertyName;
        $ownedPropertyNameForSoftDelete = $ownedEntityClassName::entityInformation()->propertyNameForSoftDelete;

        $query = \is_null($specification) ? \obo\Carriers\QueryCarrier::instance() : $specification;

        if (!\is_null($this->connectViaPropertyWithName)){
            $query->where("AND {{$this->connectViaPropertyWithName}} = %s", $this->owner->$ownerPrimaryPropertyName);
            if (!\is_null($this->ownerNameInProperty)) $query->where("AND {{$this->ownerNameInProperty}} = %s", $this->owner->className());
        } elseif (!\is_null($this->connectViaRepositoryWithName)){
            if ($ownedPropertyNameForSoftDelete !== "") {
                $softDeleteJoinQuery = "AND [{$ownedEntityClassName::entityInformation()->repositoryName}].[{$ownedEntityClassName::informationForPropertyWithName($ownedPropertyNameForSoftDelete)->columnName}] = %b";
                $query->join("JOIN [{$this->connectViaRepositoryWithName}] ON [{$this->owner->entityInformation()->repositoryName}] = %s AND [{$ownedEntityClassName::entityInformation()->repositoryName}] = [{$ownedEntityClassName::entityInformation()->primaryPropertyName}]" . $softDeleteJoinQuery, $this->owner->$ownerPrimaryPropertyName, FALSE);
            } else {
                $query->join("JOIN [{$this->connectViaRepositoryWithName}] ON [{$this->owner->entityInformation()->repositoryName}] = %s AND [{$ownedEntityClassName::entityInformation()->repositoryName}] = [{$ownedEntityClassName::entityInformation()->primaryPropertyName}]", $this->owner->$ownerPrimaryPropertyName);
            }
        }

        if (!\is_null($this->sortVia)) $query->orderBy($this->sortVia);

        return $query;
    }

}
