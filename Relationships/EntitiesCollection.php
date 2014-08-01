<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Relationships;

class EntitiesCollection extends \obo\Carriers\DataCarrier {
    /** @var \obo\Relationships\Many*/
    protected $relationShip = null;
    /** @var \obo\Entity */
    protected $owner = null;

    /** @var boolean*/
    protected $savingInProgress = false;

    /** @var boolean*/
    protected $afterSavingNeedReload = false;

    /** @var boolean*/
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
     * @return boolean
     */
    public function isSavingInProgress() {
        return $this->savingInProgress;
    }

    /**
     * @return boolean
     */
    public function isDeletingInProgress() {
        return $this->deletingInProgress;
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $createRelationshipInRepository
     * @param bool $notifyEvents
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @throws \obo\Exceptions\ServicesException
     * @return \obo\Entity
     */
    public function add(\obo\Entity $entity, $createRelationshipInRepository = true, $notifyEvents = true) {

        if (!$entity instanceof $this->relationShip->entityClassNameToBeConnected) throw new \obo\Exceptions\BadDataTypeException("Can't insert entity of {$entity->getReflection()->name} class, because the collection is designed for entity of {$this->relationShip->entityClassNameToBeConnected} class. Only entity of {$this->relationShip->entityClassNameToBeConnected} class can be loaded.");

        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, array("addedEntity" => $entity));

        if($createRelationshipInRepository AND !\is_null($this->relationShip->connectViaRepositoryWithName)) {
            if (!$entity->isBasedInRepository()) {
                $entity->save();
            }

            if ($this->owner->isBasedInRepository()) {
                $this->createRelationshipInRepositoryForEntity($entity);
            } else {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                    new \obo\Services\Events\Event(array(
                        "onObject" => $this->owner,
                        "name" => "afterInsert",
                        "actionAnonymousFunction" => function () use ($entity) {
                            $this->createRelationshipInRepositoryForEntity($entity);
                        },
                        "actionArguments" => array(),
                    ))
                );
            }
        }

        if (!\is_null($this->relationShip->connectViaPropertyWithName)) {
            $entity->setValueForPropertyWithName($this->owner, $this->relationShip->connectViaPropertyWithName);
            if (!\is_null($this->relationShip->ownerNameInProperty)) $entity->setValueForPropertyWithName($this->owner->className(), $this->relationShip->ownerNameInProperty);

            if ($createRelationshipInRepository) {
                $entity->save();
            }
        }

        if (!$entityKey = $entity->primaryPropertyValue()) {
             $entityKey = "__" . ($this->count()+1);
             $this->afterSavingNeedReload = true;

             \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(
                     new \obo\Services\Events\Event(array(
                         "onObject" => $entity,
                         "name" => "afterInsert",
                         "actionAnonymousFunction" => function($arguments) {if (!$arguments["entitiesCollection"]->isSavingInProgress()) $arguments["entitiesCollection"]->changeVariableNameForValue($arguments["entity"]->primaryPropertyValue(), $arguments["entity"]);},
                         "actionArguments" => array("entitiesCollection" => $this),
                     )));
        }

        $this->setValueForVariableWithName($entity, $entityKey);

        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, array("addedEntity" => $entity));
        return $entity;
    }

    /**
     * @param array | \Iterator $data
     * @param boolean $notifyEvents
     * @return \obo\Entity
     */
    public function addNew($data = array(), $notifyEvents = true) {
        $entityClassNameTobeConnected = $this->relationShip->entityClassNameToBeConnected;
        $entityManager = $entityClassNameTobeConnected::entityInformation()->managerName;
        $newEntity = $entityManager::entity($data);
        $newEntity->save();
        return $this->add($newEntity, true, $notifyEvents);
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $removeEntity
     * @param bool $notifyEvents
     * @throws \obo\Exceptions\EntityNotFoundException
     * @throws \obo\Exceptions\ServicesException
     * @return void
     */
    public function remove(\obo\Entity $entity, $removeEntity = false, $notifyEvents = true) {
        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, array("removedEntity" => $entity));

        if(!\is_null($this->relationShip->connectViaRepositoryWithName)) {
            \obo\EntityManager::repositoryMapper()->removeRecordFromRelationshipRepository($this->relationShip->connectViaRepositoryWithName,
            array(
                $this->owner->entityInformation()->repositoryName => $this->owner->primaryPropertyValue(),
                $entity->entityInformation()->repositoryName => $entity->primaryPropertyValue(),
            ));
        }

        $primaryPropertyValue = $entity->primaryPropertyValue();

        if (isset($this->$primaryPropertyValue)) {
            $key = $primaryPropertyValue;
        } elseif($key = \array_search($entity, $this->asArray())) {
            $key = $key;
        } else {
            throw new \obo\Exceptions\EntityNotFoundException("The entity, you want to delete does not exist in the collection");
        }

        $this->unsetValueForVaraibleWithName($key);

        if ($removeEntity) $entity->delete();
        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, array("removedEntity" => $entity));
    }

    /**
     * @return void
     */
    public function loadEntities() {
        foreach ($this->relationShip->entitiesForOwners($this->owner) as $entity) $this->add($entity, false, false);
    }

    /**
     * @return void
     */
    public function reloadEntitites() {
        $this->clear();
        $this->loadEntities();
    }

    /**
     * @return void
     */
    public function save() {
        $this->savingInProgress = true;
        foreach($this->asArray() as $entity) if (!$entity->isDeleted()) $entity->save();
        if ($this->afterSavingNeedReload) $this->reloadEntitites();
        $this->savingInProgress = false;
    }

    /**
     * @param boolean $removeEntity
     * @return void
     */
    public function delete($removeEntity = false) {
        $this->deletingInProgress = true;
        foreach($this->asArray() as $entity) {
            $this->remove($entity, $removeEntity);
        }
        $this->deletingInProgress = false;
    }

    /**
     * @return array
     */
    public function dump() {
        $dump = array();
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
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    protected function createRelationshipInRepositoryForEntity(\obo\Entity $entity) {
        $ownerPrimaryPropertyName = $this->owner->entityInformation()->primaryPropertyName;
        $entityClassNameTobeConnected = $this->relationShip->entityClassNameToBeConnected;
        $entityPrimaryPropertyName = $entityClassNameTobeConnected::entityInformation()->primaryPropertyName;

        $specification = array(
            $this->owner->entityInformation()->repositoryName => $this->owner->valueForPropertyWithName($ownerPrimaryPropertyName),
            $entity->entityInformation()->repositoryName => $entity->valueForPropertyWithName($entityPrimaryPropertyName),
        );

        \obo\EntityManager::repositoryMapper()->addRecordToRelationshipRepository($this->relationShip->connectViaRepositoryWithName, $specification);
    }
}