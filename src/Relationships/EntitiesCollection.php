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
     * @internal
     * @param array $requiredItems
     * @return array
     */
    protected function &variables(array $requiredItems = null) {
        if ($requiredItems !== null) {
            $variables = parent::variables();
            foreach ($requiredItems as $key => $requiredItem) if (isset($variables[$requiredItem])) unset($requiredItems[$key]);
        }

        if (!$this->entitiesAreLoaded AND ((\count($requiredItems) !== 0) OR $requiredItems === null)) {
            $this->entitiesAreLoaded = $requiredItems === null;
            $this->loadEntities($requiredItems);

        }

        return parent::variables();
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return int
     */
    public function count(\obo\Interfaces\IQuerySpecification $specification = null) {
        if ($specification !== null) {
            return $this->relationShip->countEntities($specification);
        } else {
            if (!$this->entitiesAreLoaded AND $this->owner->isBasedInRepository()) $this->relationShip->countEntities();
            return parent::count();
        }
    }

    /**
     * @param array | \Iterator $data
     * @param bool $notifyEvents
     * @return \obo\Entity
     */
    public function addNew($data = [], $notifyEvents = true) {
        if ($this->relationShip->connectViaPropertyWithName AND \key_exists($this->relationShip->connectViaPropertyWithName, $data)) throw new \obo\Exceptions\Exception("Can't insert new entity created from data which contain foreign key '{$this->relationShip->connectViaPropertyWithName}'");
        $entityClassNameTobeConnected = $this->relationShip->entityClassNameToBeConnected;
        $entityManager = $entityClassNameTobeConnected::entityInformation()->managerName;
        $this->add($entity = $entityManager::entity($data), true, $notifyEvents);
        $entity->save();
        return $entity;
    }

    /**
     * @param \obo\Entity|array|\Iterator $entities
     * @param bool $permanently
     * @param bool $notifyEvents
     * @return mixed
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @throws \obo\Exceptions\ServicesException
     */
    public function add($entities, $permanently = true, $notifyEvents = true) {
        $originalValue = $entities;

        if (!$entities instanceof \Iterator AND !\is_array($entities)) {
            $entities = [$entities];
        }

        foreach ($entities as $key => $entity) {

            if (!$entity instanceof $this->relationShip->entityClassNameToBeConnected) throw new \obo\Exceptions\BadDataTypeException("Can't insert entity of {$entity->getReflection()->name} class, because the collection is designed for entities of {$this->relationShip->entityClassNameToBeConnected} class. Only entity of {$this->relationShip->entityClassNameToBeConnected} class can be loaded.");

            if (!$entityKey = $entity->primaryPropertyValue()) {
                if (\strpos($key, "___") === 0 AND !$this->__isset($key)) {
                    $entityKey = $key;
                } else {
                    $entityKey = "__" . ($this->count() + 1);
                }

                $this->afterSavingNeedReload = true;

                \obo\obo::$eventManager->registerEvent(
                    new \obo\Services\Events\Event([
                        "onObject" => $entity,
                        "name" => "afterInsert",
                        "actionAnonymousFunction" => function($arguments) {if (!$arguments["entitiesCollection"]->isSavingInProgress() AND $arguments["entitiesCollection"]->containsValue($arguments["entity"])) $arguments["entitiesCollection"]->changeVariableNameForValue($arguments["entity"]->primaryPropertyValue(), $arguments["entity"]);
                        },
                        "actionArguments" => ["entitiesCollection" => $this],
                    ]));
            } else {
                if ($this->__isset($entityKey)) throw new \obo\Exceptions\Exception("Can't add an element with key '{$entityKey}' into a collection because it already exists.");
            }

            if ($notifyEvents) {
                \obo\obo::$eventManager->notifyEventForEntity("beforeAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["addedEntity" => $entity]);
                \obo\obo::$eventManager->notifyEventForEntity("beforeConnectToOwner", $entity, ["collection" => $this, "columnName" => $this->relationShip->ownerPropertyName]);
            }

            if ($permanently) $this->relationShip->add($entity);
            $this->setValueForVariableWithName($entity, $entityKey);

            if ($notifyEvents) {
                \obo\obo::$eventManager->notifyEventForEntity("afterAddTo" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["addedEntity" => $entity]);
                \obo\obo::$eventManager->notifyEventForEntity("afterConnectToOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            }
        }

        return $originalValue;
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
        $primaryPropertyValue = $entity->primaryPropertyValue();

        if ($this->__isset($primaryPropertyValue)) {
            $key = $primaryPropertyValue;
        } elseif (!$key = \array_search($entity, $this->asArray())) {
            throw new \obo\Exceptions\EntityNotFoundException("The entity you want to delete does not exist in the collection");
        }

        if ($notifyEvents) {
            \obo\obo::$eventManager->notifyEventForEntity("beforeRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["removedEntity" => $entity]);
            \obo\obo::$eventManager->notifyEventForEntity("beforeDisconnectFromOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
        }

        if ($deleteEntity) {
            $entity->delete();
        } else {
            $this->relationShip->remove($entity);
        }

        $this->unsetValueForVariableWithName($this->variableNameForValue($entity));

        if ($notifyEvents) {
            \obo\obo::$eventManager->notifyEventForEntity("afterRemoveFrom" . \ucfirst($this->relationShip->ownerPropertyName), $this->owner, ["removedEntity" => $entity]);
            \obo\obo::$eventManager->notifyEventForEntity("afterDisconnectFromOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            \obo\obo::$eventManager->notifyEventForEntity($this->relationShip->ownerPropertyName . "Disconnected", $this->owner, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName, "disconnectedEntity" => $entity]);
        }
    }

    /**
     * return only clone \obo\Carriers\QuerySpecification other modifications will not affect the original specification
     * @return \obo\Carriers\QuerySpecification
     */

    public function getSpecification() {
        return clone $this->relationShip->createSpecification();
    }

    /**
     * @return \obo\Relationships\Many
     */
    public function getRelationShip() {
        return $this->relationShip;
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
        $ownedEntityClassName = $this->relationShip->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        $specification = $ownedEntityManagerName::queryCarrier();

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
    public function loadEntities(array $entityKeys = null) {
        if (!$this->owner->isBasedInRepository()) return;

        $specification = new \obo\Carriers\QuerySpecification();

        $ownedEntityClassName = $this->relationShip->entityClassNameToBeConnected;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;

        if ($entityKeys !== null) {
            $entityClassName = $this->relationShip->entityClassNameToBeConnected;
            $specification->where("AND {{$entityClassName::entityInformation()->primaryPropertyName}} IN (?)", $entityKeys);
        }

        $variables = &parent::variables();

        foreach ($this->relationShip->findEntities($specification) as $entity) {
            \obo\obo::$eventManager->notifyEventForEntity("beforeConnectToOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            $variables[$entity->primaryPropertyValue()] = $entity;

            \obo\obo::$eventManager->notifyEventForEntity("afterConnectToOwner", $entity, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName]);
            \obo\obo::$eventManager->notifyEventForEntity($this->relationShip->ownerPropertyName . "Connected", $this->owner, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName, "addedEntity" => $entity]);
        }
    }

    /**
     * @return void
     */
    public function clear() {
        foreach ($entities = &parent::variables() as $key => $entity) {
            unset($entities[$key]);
            \obo\obo::$eventManager->notifyEventForEntity($this->relationShip->ownerPropertyName . "Disconnected", $this->owner, ["collection" => $this, "owner" => $this->owner, "columnName" => $this->relationShip->ownerPropertyName, "disconnectedEntity" => $entity]);
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
        $this->savingInProgress = true;
        foreach (parent::variables() as $entity) if (!$entity->isDeleted()) $entity->save();
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
     * @param string $name
     * @return mixed
     * @throws \obo\Exceptions\VariableNotFoundException
     */
    public function &variableForName($name) {
        $variables = $this->variables([$name]);
        if (isset($variables[$name]) OR \array_key_exists($name, $variables)) return $variables[$name];
        throw new \obo\Exceptions\EntityNotFoundException("Entity '" . $this->relationShip->entityClassNameToBeConnected . "' with primary property value '{$name}' does not exist in collection");
    }

    /**
     * @param mixed $value
     * @param string $variableName
     * @return mixed
     */
    public function setValueForVariableWithName($value, $variableName) {
        if (!$value instanceof $this->relationShip->entityClassNameToBeConnected) throw new \obo\Exceptions\BadDataTypeException("Can't insert " . (\is_object($value) ? "object of class '" . \get_class($value) : "value '" . print_r($value, true)) . "', because the collection is designed for entities of {$this->relationShip->entityClassNameToBeConnected} class. Only entity of {$this->relationShip->entityClassNameToBeConnected} class can be loaded.");
        return $this->variables([$variableName])[$variableName] = $value;
    }

    /**
     * @param string $variableName
     * @return void
     */
    public function unsetValueForVariableWithName($variableName) {
        $this->variableForName($variableName);
        unset($this->variables([$variableName])[$variableName]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        $variables = $this->variables([$name]);
        return isset($variables[$name]) OR \array_key_exists($name, $variables);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->setValueForVariableWithName($value, $offset);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->variables($offset)[$offset]);
    }

    /**
     * @throws \obo\Exceptions\Exception
     */
    public function __clone() {
        throw new \obo\Exceptions\Exception('Obo entities collection is not cloneable');
    }

}
