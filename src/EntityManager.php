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
        $querySpecification = \obo\Carriers\QueryCarrier::instance();
        return $querySpecification;
    }

    /**
     * @param \obo\Entity $entity
     * @return bool
     */
    public static function isEntityBasedInRepository(\obo\Entity $entity) {
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        if (!$entity->valueForPropertyWithName($primaryPropertyName)) return false;
        return (bool) self::countRecords(self::queryCarrier()->where("AND [{$primaryPropertyName}] = %s", $entity->$primaryPropertyName));
    }

    /**
     * @return \obo\Entity
     */
    protected static function emptyEntity() {
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

        if (isset($data[$primaryPropertyName]) AND $data[$primaryPropertyName]) {
            $entity->setValueForPropertyWithName($data[$primaryPropertyName], $primaryPropertyName);
            $entity = \obo\Services::serviceWithName(\obo\obo::IDENTITY_MAPPER)->mappedEntity($entity);
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

        if (!$entity->isInitialized() OR $overwriteOriginalData) {
            if ($overwriteOriginalData) {
                if (!$entity->isInitialized()) $entity->setInitialized();
                $entity->changeValuesPropertiesFromArray($data);
            } else {
                $entity->changeValuesPropertiesFromArray($data);
                $entity->setInitialized();
            }
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
        $specification = self::queryCarrier()->addSpecification($specification);

        $entity = self::findEntities($specification->limit(1));

        if (count($entity)) return $entity->current();

        if ($requiredEntity) {
            $query = self::dataStorage()->constructQuery($specification);
            throw new \obo\Exceptions\EntityNotFoundException("Entity '" . self::classNameManagedEntity() . "' does not exist for query '" . (\is_string($query) ? $query : \var_export($query, true)) . "'");
        }
        return null;
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

        $specification->select("DISTINCT {" . \implode("}, {", $classNameEntity::entityInformation()->persistablePropertiesNames) . "}");

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
     * @return \obo\Entity[]
     */
    protected static function entitiesFromDataStorage(\obo\Carriers\QueryCarrier $specification) {

        $classNameEntity = self::classNameManagedEntity();

        if (($propertyNameForSoftDelete = $classNameEntity::entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = %b", FALSE);
        }

        $entities = new \obo\Carriers\DataCarrier();

        foreach (self::rawDataForSpecification($specification) as $data) {
            $entity = self::entityFromRawData($data);
            $entities->setValueForVariableWithName($entity, $entity->primaryPropertyValue());
        }

        return $entities;
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

        $specification->select("{" . \implode("}, {", $entity->entityInformation()->persistablePropertiesNames) . "}")->where("{{$primaryPropertyName}} = %s", $entity->valueForPropertyWithName($primaryPropertyName));

        if (!$ignoreSoftDelete AND ($propertyNameForSoftDelete = $entity->entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = %b", FALSE);
        }

        $data = self::rawDataForSpecification($specification);

        return isset($data[0]) ? $data[0] : [];
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return int
     */
    public static function countRecords(\obo\Carriers\QueryCarrier $specification) {
        $specification = self::queryCarrier()->addSpecification($specification);
        $classNameManagedEntity = self::classNameManagedEntity();
        $primaryPropertyName = $classNameManagedEntity::informationForPropertyWithName($classNameManagedEntity::entityInformation()->primaryPropertyName)->name;
        $specification->rewriteOrderBy(null);

        if (($propertyNameForSoftDelete = $classNameManagedEntity::entityInformation()->propertyNameForSoftDelete) !== "") {
            $specification->where("AND {{$propertyNameForSoftDelete}} = %b", FALSE);
        }

        return (int) self::dataStorage()->countRecordsForQuery($specification, $primaryPropertyName);
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $forced
     * @return void
     * @throws \obo\Exceptions\EntityIsDeletedException
     * @throws \obo\Exceptions\EntityIsNotInitializedException
     * @throws \obo\Exceptions\Exception
     * @throws \obo\Exceptions\ServicesException
     */
    public static function saveEntity(\obo\Entity $entity, $forced = false, $triggerEvents = true) {
        if (!$entity->isInitialized()) throw new \obo\Exceptions\EntityIsNotInitializedException("Cannot save entity which is not initialized");
        if (!$forced AND $entity->isDeleted()) throw new \obo\Exceptions\EntityIsDeletedException("Cannot save entity which is deleted");
        if ($entity->entityInformation()->repositoryName === null) throw new \obo\Exceptions\Exception("Entity '" . $entity->className() . "' cannot be persisted. No entity storage exists");

        if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeSave", $entity);

        if (count($entity->changedProperties($entity->entityInformation()->persistablePropertiesNames, true, true))) {
            if ($entity->isBasedInRepository()) {
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeUpdate", $entity);
                $entity->setSaveInProgress();
                $entity->dataStorage()->updateEntity($entity);
                $entity->setSaveInProgress(false);
                $entity->markUnpersistedPropertiesAsPersisted();
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterUpdate", $entity);
            } else {
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeInsert", $entity);
                $entity->setSaveInProgress();
                $entity->dataStorage()->insertEntity($entity);
                $entity->setBasedInRepository(true);
                $entity->setSaveInProgress(false);
                $entity->markUnpersistedPropertiesAsPersisted();
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
                if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterInsert", $entity);
            }
        } else {
            if ($triggerEvents) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
        }
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     * @throws \obo\Exceptions\EntityIsNotInitializedException
     */
    public static function deleteEntity(\obo\Entity $entity, $triggerEvents = true) {
        if (!$entity->isInitialized()) throw new \obo\Exceptions\EntityIsNotInitializedException("Cannot delete entity which is not initialized");
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
    }
}
