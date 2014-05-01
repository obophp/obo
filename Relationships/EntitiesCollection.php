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
    private $relationShip = null;
    /**  @var \obo\Entity */
    private $owner = null;

    /**
     * @param \obo\Entity $owner
     * @param \obo\Relationships\Many $relationship
     */
    public function __construct(\obo\Entity $owner, \obo\Relationships\Many $relationship) {
        $this->relationShip = $relationship;
        $this->owner = $owner;
    }

    /**
     * @param \obo\Entity $entity
     * @param boolean $createRelationshipInRepository
     * @param bolean $notifyEvents
     * @return \obo\Entity
     */
    public function add(\obo\Entity $entity, $createRelationshipInRepository = true, $notifyEvents = true) {
        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner);

        if($createRelationshipInRepository AND !\is_null($this->relationShip->connectViaRepositoryWithName)) {
            $ownerPrimaryPopertyName = $this->owner->entityInformation()->primaryPropertyName;
            $entityClassNameTobeConnected = $this->relationShip->entityClassNameToBeConnected;
            $entityPrimaryPropertyName = $entityClassNameTobeConnected::entityInformation()->primaryPropertyName;

            $specification = array(
                $this->owner->entityInformation()->repositoryName => $this->owner->valueForPropertyWithName($ownerPrimaryPopertyName),
                $entity->entityInformation()->repositoryName => $entity->valueForPropertyWithName($entityPrimaryPropertyName),
            );

            \obo\EntityManager::repositoryMapper()->addRecordToRelationshipRepository($this->relationShip->connectViaRepositoryWithName, $specification);
        }

        if ($createRelationshipInRepository AND !\is_null($this->relationShip->connectViaPropertyWithName)) {
            $entity->setValueForPropertyWithName($this->owner, $this->relationShip->connectViaPropertyWithName);
            if (!\is_null($this->relationShip->ownerNameInProperty)) $entity->setValueForPropertyWithName($this->owner->className(), $this->relationShip->ownerNameInProperty);
            $entity->save();
        }

        $this->setValueForVariableWithName($entity, $entity->id);
        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner);
        return $entity;
    }

    /**
     * @param array | \Iterator $data
     * @param bolean $notifyEvents
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
     * @param bolean $removeEntity
     * @param bolean $notifyEvents
     * @return void
     */
    public function remove(\obo\Entity $entity, $removeEntity = false, $notifyEvents = true) {
        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner);
        $entityPrimaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        if(!\is_null($this->relationShip->connectViaRepositoryWithName)) {
            $ownerPrimaryPopertyName = $this->owner->entityInformation()->primaryPropertyName;
            $specification = array(
                $this->owner->entityInformation()->repositoryName => $this->owner->valueForPropertyWithName($ownerPrimaryPopertyName),
                $entity->entityInformation()->repositoryName => $entity->valueForPropertyWithName($entityPrimaryPropertyName),
            );
            \obo\EntityManager::repositoryMapper()->removeRecordFromRelationshipRepository($this->relationShip->connectViaRepositoryWithName, $specification);
        }
        $primaryPropertyValue = $entity->$entityPrimaryPropertyName;
        if (isset($this->$primaryPropertyValue)) unset ($this->$primaryPropertyValue);
        if ($removeEntity) $entity->delete();
        if ($notifyEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner);
    }

    /**
     * @return void
     */
    public function save() {
        foreach($this->asArray() as $entity) $entity->save();
    }

    /**
     * @param boolean $removeEntity
     * @return void
     */
    public function delete($removeEntity = false) {
        foreach($this->asArray() as $entity) {
            $this->remove($entity, $removeEntity);
        }
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
}