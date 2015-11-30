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
    public $connectViaPropertyWithName = "";

    /**
     * @var string
     */
    public $ownerNameInProperty = "";

    /**
     * @var string
     */
    public $connectViaRepositoryWithName = "";

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
     * @return \obo\Entity[]
     */
    public function findEntities(\obo\Carriers\QuerySpecification $specification = null) {
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        return $ownedEntityManagerName::findEntities($this->createSpecification($specification));
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return int
     */
    public function countEntities(\obo\Carriers\QuerySpecification $specification = null) {
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        return $ownedEntityManagerName::countRecords($this->createSpecification($specification));
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return \obo\Carriers\QueryCarrier
     */
    public function createSpecification(\obo\Interfaces\IQuerySpecification $specification = null) {
        $ownedEntityClassName = $this->entityClassNameToBeConnected;
        $ownedEntityManger = $ownedEntityClassName::entityInformation()->managerName;
        $ownerPrimaryPropertyName = $this->owner->entityInformation()->primaryPropertyName;
        $ownedPropertyNameForSoftDelete = $ownedEntityClassName::entityInformation()->propertyNameForSoftDelete;

        if ($specification === null) {
            $specification = $ownedEntityManger::queryCarrier();
        } else {
            $specification = $ownedEntityManger::queryCarrier()->addSpecification($specification);
        }

        if ($this->connectViaPropertyWithName !== "") {
            $specification->where("AND {{$this->connectViaPropertyWithName}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $this->owner->primaryPropertyValue());
            if ($this->ownerNameInProperty !== "") $specification->where("AND {{$this->ownerNameInProperty}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $this->owner->entityInformation()->name);
        } elseif ($this->connectViaRepositoryWithName !== "") {
            $specification->where("{*{$this->connectViaRepositoryWithName}," . $this->owner->className() . "*} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $this->owner->primaryPropertyValue());
        }

        if ($this->sortVia !== null) $specification->orderBy($this->sortVia);

        return $specification ;
    }

    public function add(\obo\Entity $entity) {
        if ($this->connectViaPropertyWithName !== "") {
            $entity->setValueForPropertyWithName($this->owner, $this->connectViaPropertyWithName);
            if ($this->ownerNameInProperty !== "") $entity->setValueForPropertyWithName($this->owner->entityInformation()->name, $this->ownerNameInProperty);

            if ($entity->isBasedInRepository() AND $this->owner->isBasedInRepository()) $entity->save();

            if (!$this->owner->isBasedInRepository()) {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                    new \obo\Services\Events\Event([
                        "onObject" => $this->owner,
                        "name" => "afterInsert",
                        "actionAnonymousFunction" => function () use ($entity) {
                            if ($entity->isBasedInRepository()) $entity->save();
                        },
                        "actionArguments" => [],
                    ])
                );
            }

        } elseif ($this->connectViaRepositoryWithName !== "") {
            if ($entity->isBasedInRepository() AND $this->owner->isBasedInRepository()) {
                $this->createRelationshipInRepositoryForEntity($entity);
            } else {
                if (!$entity->isBasedInRepository()) {
                    $owner = $this->owner;
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                        new \obo\Services\Events\Event([
                            "onObject" => $entity,
                            "name" => "afterInsert",
                            "actionAnonymousFunction" => function () use ($owner, $entity) {
                                if ($owner->isBasedInRepository()) $this->createRelationshipInRepositoryForEntity($entity);
                            },
                            "actionArguments" => [],
                        ])
                    );
                }

                if (!$this->owner->isBasedInRepository()) {
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                        new \obo\Services\Events\Event([
                            "onObject" => $this->owner,
                            "name" => "afterInsert",
                            "actionAnonymousFunction" => function () use ($entity) {
                                if ($entity->isBasedInRepository()) $this->createRelationshipInRepositoryForEntity($entity);
                            },
                            "actionArguments" => [],
                        ])
                    );
                }
            }
        } else {
            throw new \obo\Exceptions\Exception("This relationship is not well configured");
        }
    }

    public function remove(\obo\Entity $entity) {
        if ($this->connectViaPropertyWithName !== "") {
            $entity->setValueForPropertyWithName(null, $this->connectViaPropertyWithName);
            $entity->save();
        } elseif ($this->connectViaRepositoryWithName !== "") {
            $ownerManagerName = $this->owner->entityInformation()->managerName;
            $ownerManagerName::dataStorage()->removeRelationshipBetweenEntities($this->connectViaRepositoryWithName, [$this->owner, $entity]);
        } else {
            throw new \obo\Exceptions\Exception("This relationship is not well configured");
        }
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    protected function createRelationshipInRepositoryForEntity(\obo\Entity $entity) {
        $ownerManagerName = $this->owner->entityInformation()->managerName;
        $ownerManagerName::dataStorage()->createRelationshipBetweenEntities($this->connectViaRepositoryWithName, [$this->owner, $entity]);
    }

}
