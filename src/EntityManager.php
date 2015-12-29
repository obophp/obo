<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

abstract class EntityManager  extends \obo\Object {

    /**
     * @var array
     */
    private static $classNamesManagedEntities = [];

    /**
     * @var array
     */
    protected static $dataStorages = [];

    /**
     * @return string
     */
    public static function classNameManagedEntity() {
        if (isset(self::$classNamesManagedEntities[self::className()])) return self::$classNamesManagedEntities[self::className()];
        return self::$classNamesManagedEntities[self::className()] = \preg_replace("#Manager$#", "", self::className());
    }

    /**
     * @return \obo\Interfaces\IDataStorage
     */
    public static function dataStorage() {
        if (isset(self::$dataStorages[self::className()])) return self::$dataStorages[self::className()];
        return self::setDataStorage(\obo\Services::serviceWithName(\obo\obo::DEFAULT_DATA_STORAGE));
    }

    /**
     * @param \obo\Interfaces\IDataStorage $dataStorage
     * @return \obo\Interfaces\IDataStorage
     */
    public static function setDataStorage(\obo\Interfaces\IDataStorage $dataStorage) {
        return self::$dataStorages[self::className()] = $dataStorage;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public static function queryCarrier() {
        $queryCarrier = new \obo\Carriers\QueryCarrier();
        $queryCarrier->setDefaultEntityClassName(self::classNameManagedEntity());
        return $queryCarrier;
    }

    /**
     * @return \obo\Carriers\QuerySpecification
     */
    public static function querySpecification() {
        $querySpecification = new \obo\Carriers\QuerySpecification();
        return $querySpecification;
    }

    /**
     * @param \obo\Entity $entity
     * @return bool
     */
    public static function isEntityBasedInRepository(\obo\Entity $entity) {
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        if (!$entity->primaryPropertyValue()) return false;
        return (bool) self::countRecords(self::queryCarrier()->where("AND [{$primaryPropertyName}] = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $entity->primaryPropertyValue()));
    }

    /**
     * @internal
     * @return \obo\Entity
     */
    public static function emptyEntity() {
        $entityClassName = self::classNameManagedEntity();
        $entity = new $entityClassName;
        $entity->setDataStorage(self::dataStorage());
        return $entity;
    }

    /**
     * @param mixed $primaryPropertyValue
     * @param bool $ignoreSoftDelete
     * @return \obo\Entity
     * @throws \obo\Exceptions\BadDataTypeException
     * @throws \obo\Exceptions\EntityNotFoundException
     * @throws \obo\Exceptions\Exception
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @throws \obo\Exceptions\ServicesException
     */
    public static function entityWithPrimaryPropertyValue($primaryPropertyValue, $ignoreSoftDelete = false) {
        $entity = self::emptyEntity();

        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        $primaryPropertyDataType = $entity->entityInformation()->informationForPropertyWithName($primaryPropertyName)->dataType;
        if (!$primaryPropertyDataType->validate($primaryPropertyDataType->sanitizeValue($primaryPropertyValue), false)) throw new \obo\Exceptions\BadDataTypeException("Can't create entity from value " . (\is_scalar($primaryPropertyValue) ? "'" . print_r($primaryPropertyValue, true) . "'" : "") . " of '" . \gettype($primaryPropertyValue) . "' datatype. Primary property '" . $primaryPropertyName . "' in entity '" . self::classNameManagedEntity() . "' is of '" . $entity->entityInformation()->informationForPropertyWithName($primaryPropertyName)->dataType->name() . "' datatype.");
        $entity->setValueForPropertyWithName($primaryPropertyValue, $primaryPropertyName);
        $entity = \obo\Services::serviceWithName(\obo\obo::IDENTITY_MAPPER)->mappedEntity($entity);

        if (!$entity->isInitialized()) {
            $data = self::rawDataForEntity($entity, $ignoreSoftDelete);
            if (!count($data)) throw new \obo\Exceptions\EntityNotFoundException("Entity '" . self::classNameManagedEntity() . "' with primary property value '{$primaryPropertyName} = {$primaryPropertyValue}' does not exist in the repository or is deleted");
            return self::entityFromRawData($data);
        }

        return $entity;
    }

    /**
     * @param array $data
     * @param bool $loadOriginalData
     * @param bool $overwriteOriginalData
     * @return \obo\Entity
     */
    public static function entityFromArray($data, $loadOriginalData = false, $overwriteOriginalData = true) {
        $entity = self::emptyEntity();

        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        if (isset($data[$primaryPropertyName])) {
            $entity->setValueForPropertyWithName($data[$primaryPropertyName], $primaryPropertyName);
            $entity = \obo\Services::serviceWithName(\obo\obo::IDENTITY_MAPPER)->mappedEntity($entity);
            unset($data[$primaryPropertyName]);
        } else {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                "onObject" => $entity,
                "name" => "afterInsert",
                "actionAnonymousFunction" => function($arguments) {
                   \obo\Services::serviceWithName(\obo\obo::IDENTITY_MAPPER)->mappedEntity($arguments["entity"]);
                   $arguments["entity"]->setBasedInRepository(true);
                }
            ]));
        }

        if ($entity->primaryPropertyValue() AND !$entity->isInitialized() AND $loadOriginalData) {
            $entity->changeValuesPropertiesFromArray($repositoryData = self::rawDataForEntity($entity));
            $entity->setBasedInRepository((bool) $repositoryData);
        }

        if ($overwriteOriginalData) {
            if ($entity->isInitialized()) {
                $entity->changeValuesPropertiesFromArray($data);
            } else {
                $entity->setInitialized();
                $entity->setValuesPropertiesFromArray($data);
            }
        }

        if (!$entity->isInitialized()) {
            $entity->setValuesPropertiesFromArray($data);
            $entity->setInitialized();
        }

        return $entity;
    }

    /**
     * @param array|int $specification
     * @return \obo\Entity
     * @throws \obo\Exceptions\EntityNotFoundException
     */
    public static function entity($specification) {
       if (is_array($specification) OR $specification instanceof \Traversable) {
           return self::entityFromArray($specification, true);
       } elseif ($specification !== null) {
           return self::entityWithPrimaryPropertyValue($specification);
       } else {
           throw new \obo\Exceptions\EntityNotFoundException("Can't initialize entity with specification 'NULL'");
       }
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @param bool $requiredEntity
     * @return \obo\Entity
     * @throws \obo\Exceptions\EntityNotFoundException
     */
    public static function findEntity(\obo\Interfaces\IQuerySpecification $specification, $requiredEntity = true) {
        $classNameEntity = self::classNameManagedEntity();
        $specification = self::queryCarrier()->addSpecification($specification);
        $specification->select(self::constructSelect());

        if (!($entity = self::entityFromDataStorage($specification)) AND $requiredEntity) {
            throw new \obo\Exceptions\EntityNotFoundException("Entity '" . self::classNameManagedEntity() . "' does not exist for query '" . (\is_string($query = self::dataStorage()->constructQuery($specification)) ? $query : \var_export($query, true)) . "'");
        }

        return $entity;
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Entity
     */
    public static function findEntities(\obo\Interfaces\IQuerySpecification $specification, \obo\Interfaces\IPaginator $paginator = null, \obo\Interfaces\IFilter $filter = null) {
        $specification = self::queryCarrier()->addSpecification($specification);

        if ($filter !== null) {
           $specification->addSpecification($filter->getSpecification());
        }

        if ($paginator !== null) {
           $paginator->setItemCount(self::countRecords(clone $specification));
           $specification->addSpecification($paginator->getSpecification());
        }

        $classNameEntity = self::classNameManagedEntity();

        $specification->select(self::constructSelect());

        return self::entitiesFromDataStorage($specification);
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return \obo\Carriers\EntitiesCollection
     */
    public static function findEntitiesAsCollection(\obo\Interfaces\IQuerySpecification $specification) {
        return new \obo\Carriers\EntitiesCollection(self::classNameManagedEntity(), $specification);
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return \obo\Entity
     */
    protected static function entityFromDataStorage(\obo\Carriers\QueryCarrier $specification) {
        $classNameEntity = self::classNameManagedEntity();
        $specification->limit(1);

        if (($propertyNameForSoftDelete = $classNameEntity::entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, FALSE);
        }

        $rawData = self::rawDataForSpecification($specification);
        return isset($rawData[0]) ? self::entityFromRawData($rawData[0]) : null;
    }

    /**
     * @param array $data
     * @return \obo\Entity
     */
    protected static function entityFromRawData(array $data) {
        $entity = self::entityFromArray($data, false, false);
        $entity->setBasedInRepository(true);
        return $entity;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return \obo\Entity[]
     */
    protected static function entitiesFromDataStorage(\obo\Carriers\QueryCarrier $specification) {
        $classNameEntity = self::classNameManagedEntity();

        if (($propertyNameForSoftDelete = $classNameEntity::entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, FALSE);
        }

        $entities = new \obo\Carriers\DataCarrier();

        foreach (self::rawDataForSpecification($specification) as $data) {
            $entity = self::entityFromRawData($data);
            $entities->setValueForVariableWithName($entity, $entity->primaryPropertyValue());
        }

        return $entities;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return array;
     */
    protected static function rawDataForSpecification(\obo\Carriers\QueryCarrier $specification) {
        return self::dataStorage()->dataForQuery($specification);
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $ignoreSoftDelete
     * @return array;
     */
    protected static function rawDataForEntity(\obo\Entity $entity, $ignoreSoftDelete = false) {
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;

        $specification = self::queryCarrier();

        $specification->select(self::constructSelect())->where("{{$primaryPropertyName}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $entity->valueForPropertyWithName($primaryPropertyName));

        if (!$ignoreSoftDelete AND ($propertyNameForSoftDelete = $entity->entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, FALSE);
        }

        $data = self::rawDataForSpecification($specification);
        return isset($data[0]) ? $data[0] : [];
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return int
     */
    public static function countRecords(\obo\Interfaces\IQuerySpecification $specification) {
        $specification = self::queryCarrier()->addSpecification($specification);
        $classNameManagedEntity = self::classNameManagedEntity();
        $specification->rewriteOrderBy(null);

        if (($propertyNameForSoftDelete = $classNameManagedEntity::entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = " . \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, FALSE);
        }

        return (int) self::dataStorage()->countRecordsForQuery($specification);
    }

    /**
     * @return string
     */
    public static function constructSelect() {
        $classNameManagedEntity = self::classNameManagedEntity();
        $entityInformation = $classNameManagedEntity::entityInformation();

        return "{" . \implode("}, {", $entityInformation->persistablePropertiesNames) . "}" . self::loadEagerConnections();
    }

    /**
     * @param string $prefix
     * @return string
     */
    public static function loadEagerConnections($prefix = "") {
        $nextQuery = "";
        $classNameManagedEntity = self::classNameManagedEntity();
        $entityInformation = $classNameManagedEntity::entityInformation();

        foreach ($entityInformation->eagerConnections as $eagerConnection) {
            $targetEntity = $classNameManagedEntity::informationForPropertyWithName($eagerConnection)->relationship->entityClassNameToBeConnected;
            $persistableProperties = $targetEntity::entityInformation()->persistablePropertiesNames;
            $targetEntityManagerClassName = $targetEntity::entityInformation()->managerName;

            foreach ($persistableProperties as $persistableProperty) {
                $nextQuery .= ", {$prefix}{{$eagerConnection}}.{{$persistableProperty}}";
            }

            $nextQuery .= $targetEntityManagerClassName::loadEagerConnections("{$prefix}{{$eagerConnection}}.");
        }

        return $nextQuery;
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $forced
     * @param bool $triggerEvents
     * @return void
     * @throws \obo\Exceptions\EntityIsDeletedException
     * @throws \obo\Exceptions\EntityIsNotInitializedException
     * @throws \obo\Exceptions\Exception
     * @throws \obo\Exceptions\ServicesException
     */
    public static function saveEntity(\obo\Entity $entity, $forced = false, $triggerEvents = true) {
        if (!$entity->isInitialized()) throw new \obo\Exceptions\EntityIsNotInitializedException("Cannot save entity which is not initialized");
        if (!$forced AND $entity->isDeleted() AND !$entity->isDeletingInProgress()) throw new \obo\Exceptions\EntityIsDeletedException("Cannot save entity which is deleted");
        if ($entity->entityInformation()->repositoryName === null) throw new \obo\Exceptions\Exception("Entity '" . $entity->className() . "' cannot be persisted. No entity storage exists");

        $entity->setSavingInProgress();
        if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeSave", $entity);

        if (count($entity->changedProperties($entity->entityInformation()->persistablePropertiesNames, true, true))) {
            if ($entity->isBasedInRepository()) {
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeUpdate", $entity);
                $entity->dataStorage()->updateEntity($entity);
                $entity->markUnpersistedPropertiesAsPersisted();
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterUpdate", $entity);
            } else {
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeInsert", $entity);
                $entity->dataStorage()->insertEntity($entity);
                $entity->setBasedInRepository(true);
                $entity->markUnpersistedPropertiesAsPersisted();
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterInsert", $entity);
            }
        } else {
            if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
        }
        $entity->setSavingInProgress(false);
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $triggerEvents
     * @return void
     * @throws \obo\Exceptions\EntityIsNotInitializedException
     */
    public static function deleteEntity(\obo\Entity $entity, $triggerEvents = true) {
        if (!$entity->isInitialized()) throw new \obo\Exceptions\EntityIsNotInitializedException("Cannot delete entity which is not initialized");
        $entity->setDeletingInProgress();
        if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDelete", $entity);

        if (($propertyNameForSoftDelete = $entity->entityInformation()->propertyNameForSoftDelete) === "") {
            $entity->dataStorage()->removeEntity($entity);
        } else {
            $entity->discardNonPersistedChanges();
            $entity->setValueForPropertyWithName(true, $propertyNameForSoftDelete);
            self::saveEntity($entity, true, false);
        }

        $entity->setDeleted(true);
        if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDelete", $entity);
        $entity->setDeletingInProgress(false);
    }
}
