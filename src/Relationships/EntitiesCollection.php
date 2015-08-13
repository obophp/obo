<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

class EntitiesCollection extends \obo\Carriers\DataCarrier implements \obo\Interfaces\IEntitiesCollection {

    /** @var \obo\Relationships\Many */
    protected $relationShip = null;

    /** @var \obo\Entity */
    protected $owner = null;

    /** @var bool */
    protected $entitiesAreLoaded = false;

    /** @var bool */
    protected $savingInProgress = false;

    /** @var bool */
    protected $afterSavingNeedReload = false;

    /** @var bool */
    protected $deletingInProgress = false;

    /**
     * @param \obo\Entity $owner
     * @param \obo\Relationships\Many $relationship
     */
    public function __construct(\obo\Entity $owner, \obo\Relationships\Many $relationship) {
        $this->relationShip = $relationship;
        $this->owner = $owner;
    }

    /**
     * @return \obo\Entity
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @return bool
     */
    public function isSavingInProgress() {
        return $this->savingInProgress;
    }

    /**
     * @return bool
     */
    public function isDeletingInProgress() {
        return $this->deletingInProgress;
    }

    /**
     * @return string
     */
    public function getEntitiesClassName() {
        return $this->relationShip->entityClassNameToBeConnected;
    }

    /**
     * @return array
     */
    protected function &variables() {
        if (!$this->entitiesAreLoaded) {
            $this->entitiesAreLoaded = true;
            $this->loadEntities();
        }
        return parent::variables();
    }

    /**
     * @return int
     */
    public function count() {
        if ($this->entitiesAreLoaded) {
            return parent::count();
        } elseif ($this->owner->isBasedInRepository()) {
            $entityClass = $this->relationShip->entityClassNameToBeConnected;
            $managerClass = $entityClass::entityInformation()->managerName;
            return $managerClass::countRecords(\obo\Carriers\QueryCarrier::instance()->addSpecification($this->getSpecification()));
        } else {
            return 0;
        }
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $createRelationshipInRepository
     * @param bool $notifyEvents
     * @return \obo\Entity
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @throws \obo\Exceptions\ServicesException
     */
    public function add(\obo\Entity $entity, $createRelationshipInRepository = true, $notifyEvents = true) {
        if (!$entity instanceof $this->relationShip->entityClassNameToBeConnected) throw new \obo\Exceptions\BadDataTypeException("Can't insert entity of {$entity->getReflection()->name} class, because the collection is designed for entity of {$this->relationShip->entityClassNameToBeConnected} class. Only entity of {$this->relationShip->entityClassNameToBeConnected} class can be loaded.");

        if ($notifyEvents) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["addedEntity" => $entity]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeConnectToOwner", $entity, ["collection" => $this, "columnName" => $this->relationShip->ownerPropertyName]);
        }

        if ($createRelationshipInRepository AND $this->relationShip->connectViaRepositoryWithName !== null) {
            if (!$entity->isBasedInRepository()) {
                $entity->save();
            }

            if ($this->owner->isBasedInRepository()) {
                $this->createRelationshipInRepositoryForEntity($entity);
            } else {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                    new \obo\Services\Events\Event([
                        "onObject" => $this->owner,
                        "name" => "afterInsert",
                        "actionAnonymousFunction" => function () use ($entity) {
                            $this->createRelationshipInRepositoryForEntity($entity);
                        },
                        "actionArguments" => [],
                    ])
                );
            }
        }

        if ($this->relationShip->connectViaPropertyWithName !== null) {

            $entity->setValueForPropertyWithName($this->owner, $this->relationShip->connectViaPropertyWithName);
            if ($this->relationShip->ownerNameInProperty !== null) $entity->setValueForPropertyWithName($this->owner->className(), $this->relationShip->ownerNameInProperty);

            if ($createRelationshipInRepository) {

                if ($this->owner->isBasedInRepository()) {
                    $entity->save();
                } else {
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                        new \obo\Services\Events\Event([
                            "onObject" => $this->owner,
                            "name" => "afterInsert",
                            "actionAnonymousFunction" => function () use ($entity) {
                                $entity->save();
                            },
                            "actionArguments" => [],
                        ])
                    );
                }

            }

        }

        if (!$entityKey = $entity->primaryPropertyValue()) {
             $entityKey = "__" . ($this->count() + 1);
             $this->afterSavingNeedReload = true;

             \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                     new \obo\Services\Events\Event([
                         "onObject" => $entity,
                         "name" => "afterInsert",
                         "actionAnonymousFunction" => function($arguments) {if (!$arguments["entitiesCollection"]->isSavingInProgress() AND $arguments["entitiesCollection"]->containsValue($arguments["entity"])) $arguments["entitiesCollection"]->changeVariableNameForValue($arguments["entity"]->primaryPropertyValue(), $arguments["entity"]);},
                         "actionArguments" => ["entitiesCollection" => $this],
                     ]));
        }

        $this->setValueForVariableWithName($entity, $entityKey);

        if ($notifyEvents) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["addedEntity" => $entity]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterConnectToOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
        }

        return $entity;
    }

    /**
     * @param array | \Iterator $data
     * @param bool $notifyEvents
     * @return \obo\Entity
     */
    public function addNew($data = [], $notifyEvents = true) {
        $entityClassNameTobeConnected = $this->relationShip->entityClassNameToBeConnected;
        $entityManager = $entityClassNameTobeConnected::entityInformation()->managerName;
        return $this->add($entityManager::entity($data), true, $notifyEvents);
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $deleteEntity
     * @param bool $notifyEvents
     * @return void
     * @throws \obo\Exceptions\EntityNotFoundException
     * @throws \obo\Exceptions\ServicesException
     */
    public function remove(\obo\Entity $entity, $deleteEntity = false, $notifyEvents = true) {

        if ($notifyEvents) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["removedEntity" => $entity]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDisconnectFromOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
        }

        if ($this->entitiesAreLoaded) {
            $primaryPropertyValue = $entity->primaryPropertyValue();

            if ($this->__isset($primaryPropertyValue)) {
                $key = $primaryPropertyValue;
            } elseif (!$key = \array_search($entity, $this->asArray())) {
                throw new \obo\Exceptions\EntityNotFoundException("The entity you want to delete does not exist in the collection");
            }

            $this->unsetValueForVariableWithName($key);
        }

        if (!$deleteEntity) $this->removeRelationshipFromRepositoryForEntity($entity);

        if ($notifyEvents) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["removedEntity" => $entity]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDisconnectFromOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->relationShip->ownerPropertyName . "Disconnected", $this->owner, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName, "disconnectedEntity" => $entity]);
        }

        if ($deleteEntity) $entity->delete();
    }

    /**
     * return only clone \obo\Carriers\QuerySpecification other modifications will not affect the original specification
     * @return \obo\Carriers\QuerySpecification
     */

    public function getSpecification() {
        return clone $this->relationShip->constructSpecification();
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return \obo\Entity[]
     */
    public function find(\obo\Interfaces\IQuerySpecification $specification) {
        return $this->relationShip->findEntities($specification);
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Entity[]
     */
    public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null) {

        $specification = new \obo\Carriers\QuerySpecification();

        if ($filter !== null) {
            $specification->addSpecification($filter->getSpecification());
        }

        $paginator->setItemCount($this->relationShip->countEntities($specification));
        $specification->addSpecification($paginator->getSpecification());

        return $this->find($specification);
    }

    /**
     * @return void
     */
    public function loadEntities() {
        if (!$this->owner->isBasedInRepository()) return;
        foreach ($this->relationShip->findEntities() as $entity) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeConnectToOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            $this->setValueForVariableWithName($entity, $entity->primaryPropertyValue());
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterConnectToOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->relationShip->ownerPropertyName . "Connected", $this->owner, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName, "addedEntity" => $entity]);
        }
    }

    /**
     * @return void
     */
    public function clear() {
        foreach ($entities = &parent::variables() as $entity) {
            unset($entities[$entity->id]);
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->relationShip->ownerPropertyName . "Disconnected", $this->owner, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName, "disconnectedEntity" => $entity]);
        }
    }

    /**
     * @return void
     */
    public function reloadEntities() {
        $this->clear();
        $this->loadEntities();
    }

    /**
     * @return void
     */
    public function save() {
        if (!$this->entitiesAreLoaded) return;
        $this->savingInProgress = true;
        foreach ($this->asArray() as $entity) if (!$entity->isDeleted()) $entity->save();
        if ($this->afterSavingNeedReload) $this->reloadEntities();
        $this->savingInProgress = false;
    }

    /**
     * @param bool $removeEntity
     * @return void
     */
    public function delete($removeEntity = false) {
        $this->deletingInProgress = true;
        foreach ($this->asArray() as $entity) {
            $this->remove($entity, $removeEntity);
        }
        $this->deletingInProgress = false;
    }

    /**
     * @return array
     */
    public function dump() {
        $dump = [];
        $arguments = func_get_args();

        if (isset ($arguments[0])) {
            foreach ($this->asArray() as $entity) $dump[] = $entity->dump($arguments[0]);
        } else {
            foreach ($this->asArray() as $entity) $dump[] = $entity->dump();
        }

        return $dump;
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    protected function createRelationshipInRepositoryForEntity(\obo\Entity $entity) {
        $ownerManagerName = $this->owner->entityInformation()->managerName;
        $ownerManagerName::dataStorage()->createRelationshipBetweenEntities($this->relationShip->connectViaRepositoryWithName, [$this->owner, $entity]);
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    protected function removeRelationshipFromRepositoryForEntity(\obo\Entity $entity) {
        if ($this->relationShip->connectViaRepositoryWithName === null) {
            $entity->setValueForPropertyWithName(null, $this->relationShip->connectViaPropertyWithName);
            $entity->save();
        } else {
            $ownerManagerName = $this->owner->entityInformation()->managerName;
            $ownerManagerName::dataStorage()->removeRelationshipBetweenEntities($this->relationShip->connectViaRepositoryWithName, [$this->owner, $entity]);
        }
    }

}
