<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/, Roman Pavlík
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

abstract class EntityManager  extends \obo\Object {

    private static $classNamesManagedEntities = array();

    /**
     * @return \obo\RepositoryMappers\DibiRepositoryMapper
     */
    public static function repositoryMapper() {
        return \obo\Services::serviceWithName(\obo\obo::REPOSITORY_MAPPER);
    }

    /**
     * @return string
     */
    public static function classNameManagedEntity() {
        if (isset(self::$classNamesManagedEntities[self::className()])) return self::$classNamesManagedEntities[self::className()];
        return self::$classNamesManagedEntities[self::className()] = \preg_replace("#Manager$#", "", self::className());
    }

    /**
     * @param \obo\Entity $entity
     * @return boolean
     */
    public static function isEntityBasedInRepository(\obo\Entity $entity) {
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        if (!$entity->valueForPropertyWithName($primaryPropertyName)) return false;
        return (bool) self::countRecords(\obo\Carriers\QueryCarrier::instance()->where("AND [{$primaryPropertyName}] = %s", $entity->$primaryPropertyName));
    }

    /**
     * @return \obo\Entity
     */
    protected static function emptyEntity() {
        $entityClassName = self::classNameManagedEntity();
        $entity = new $entityClassName;
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->turnOnIgnoreNotificationForEntity($entity);
        return $entity;
    }

    /**
     * @param mixed $primaryPropertyValue
     * @param bool $ignoreSoftDelete
     * @return \obo\Entity
     * @throws \obo\Exceptions\EntityNotFoundException
     */
    public static function entityWithPrimaryPropertyValue($primaryPropertyValue, $ignoreSoftDelete = false) {
        $entity = self::emptyEntity();

        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        $entity->$primaryPropertyName = $primaryPropertyValue;

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
     * @param boolean $loadOriginalData
     * @param boolean $overwriteOriginalData
     * @param boolean $separately
     * @return \obo\Entity
     */
    public static function entityFromArray($data, $loadOriginalData = false, $overwriteOriginalData = true, $separately = false) {
        $entity = self::emptyEntity();

        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;

        if (isset($data[$primaryPropertyName]) AND $data[$primaryPropertyName] AND !$separately) {
            $entity->setValueForPropertyWithName($data[$primaryPropertyName], $primaryPropertyName);
            $entity = \obo\Services::serviceWithName(\obo\obo::IDENTITY_MAPPER)->mappedEntity($entity);
        } elseif (!$separately) {
            $event = new \obo\Services\Events\Event(array(
                    "onObject" => $entity,
                    "name" => "afterInsert",
                    "actionAnonymousFunction" => function($arguments) {
                       \obo\Services::serviceWithName(\obo\obo::IDENTITY_MAPPER)->mappedEntity($arguments["entity"]);
                       $arguments["entity"]->setBasedInRepository(true);
                    }
            ));
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent($event);
        }

        if ($entity->valueForPropertyWithName($primaryPropertyName) AND !$entity->isInitialized() AND $loadOriginalData) {
            $entity->changeValuesPropertiesFromArray($repositoryData = self::rawDataForEntity($entity));
            $entity->setBasedInRepository((boolean) $repositoryData);
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
     * @throws \obo\Exceptions\EntityNotFoundException
     * @return \obo\Entity
     */
    public static function entity($specification) {
       if (is_array($specification) OR $specification instanceof \Traversable) {
           return self::entityFromArray($specification, true);
       } elseif (!\is_null($specification)) {
           return self::entityWithPrimaryPropertyValue($specification);
       } else {
           throw new \obo\Exceptions\EntityNotFoundException("Can not initialize entity with specification 'NULL'");
       }
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return \obo\Entity
     * @throws \obo\Exceptions\EntityNotFoundException
     */
    public static function findEntity(\obo\Carriers\IQuerySpecification $specification, $requiredEntity = true) {
        if (!$specification instanceof \obo\Carriers\QueryCarrier) {
           $specification = \obo\Carriers\QueryCarrier::instance()->addSpecification($specification);
        }

        $entity = self::findEntities($specification->limit(1));

        if (count($entity)) return $entity->current();
        if ($requiredEntity) throw new \obo\Exceptions\EntityNotFoundException("Entity '" . self::classNameManagedEntity() . "' does not exist for query '" . self::repositoryMapper()->constructQuery($specification->constructQuery()) . "'");
        return null;
    }

    /**
     * @param \obo\Carriers\IQuerySpecification $specification
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Entity
     */
    public static function findEntities(\obo\Carriers\IQuerySpecification $specification, \obo\Interfaces\IPaginator $paginator = null, \obo\Interfaces\IFilter $filter = null) {

        if (!$specification instanceof \obo\Carriers\QueryCarrier) {
           $specification = \obo\Carriers\QueryCarrier::instance()->addSpecification($specification);
        }

        if (!\is_null($filter)) {
           $specification->addSpecification($filter->getSpecification());
        }

        if (!\is_null($paginator)) {
           $paginator->setItemCount(self::countRecords(clone $specification));
           $specification->addSpecification($paginator->getSpecification());
        }

        $classNameEntity = self::classNameManagedEntity();
        $repositoryName = $classNameEntity::entityInformation()->repositoryName;

        $specification->select("DISTINCT [{$repositoryName}].[".\implode("], [{$repositoryName}].[", $classNameEntity::entityInformation()->repositoryColumnsForPersistableProperties)."]");
        return self::entitiesFromRepositoryMapper($specification);
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return \obo\Carriers\EntitiesCollection
     */
    public static function findEntitiesAsCollection(\obo\Carriers\QuerySpecification $specification) {
        return new \obo\Carriers\EntitiesCollection(self::classNameManagedEntity(), $specification);
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return \obo\Entity[]
     */
    protected static function entitiesFromRepositoryMapper(\obo\Carriers\QueryCarrier $specification) {

        $classNameEntity = self::classNameManagedEntity();

        if (!is_null($propertyNameForSoftDelete = $classNameEntity::entityInformation()->propertyNameForSoftDelete)) {
            $specification->where("AND {{$propertyNameForSoftDelete}} = 0");
        }

        $entities = new \obo\Carriers\DataCarrier();

        foreach (self::rawDataForSpecification($specification) as $data) {
            $entity = self::entityFromRawData($data);
            $entities->setValueForVariableWithName($entity, $entity->valueForPropertyWithName($entity->entityInformation()->primaryPropertyName, true));
        }

        return $entities;
    }

    /**
     * @param array $data
     * @return \obo\Entity
     */
    protected static function entityFromRawData($data) {
        $classNameManagedEntity = self::classNameManagedEntity();

        if (count($data) == 1) {
            $primaryPropertyName = $classNameManagedEntity::informationForPropertyWithName($classNameManagedEntity::entityInformation()->primaryPropertyName);
            $entity = self::entityWithPrimaryPropertyValue($data->$primaryPropertyName);
        } else {
            $entity = self::entityFromArray($data, false, false);
        }

        $entity->setBasedInRepository(true);
        return $entity;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return array();
     */
    protected static function rawDataForSpecification(\obo\Carriers\QueryCarrier $specification) {
        $classNameManagedEntity = self::classNameManagedEntity();
        $specification->setDefaultEntityClassName($classNameManagedEntity);
        $rawData = array();

        foreach(self::repositoryMapper()->dataFromQuery($specification->constructQuery()) as $data) {
            $rawData[] = $classNameManagedEntity::entityInformation()->columnsNamesToPropertiesNames($data);
        }

        return $rawData;
    }

    /**
     * @param \obo\Entity $entity
     * @return array();
     */
    protected static function rawDataForEntity(\obo\Entity $entity, $ignoreSoftDelete = false) {
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;

        $specification = \obo\Carriers\QueryCarrier::instance();

        $specification->select("[".\implode("], [", $entity->entityInformation()->repositoryColumnsForPersistableProperties)."]")->where("{{$primaryPropertyName}} = %s", $entity->valueForPropertyWithName($primaryPropertyName));

        if (!$ignoreSoftDelete AND !is_null($propertyNameForSoftDelete = $entity->entityInformation()->propertyNameForSoftDelete)) {
            $specification->where("AND {{$propertyNameForSoftDelete}} = 0");
        }

        $data = self::rawDataForSpecification($specification);

        return isset($data[0]) ? $data[0] : array();
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @return int
     */
    public static function countRecords(\obo\Carriers\QueryCarrier $specification) {
        $specification->setDefaultEntityClassName($classNameManagedEntity = self::classNameManagedEntity());
        $primaryPropertyName = $classNameManagedEntity::informationForPropertyWithName($classNameManagedEntity::entityInformation()->primaryPropertyName)->name;

        $specification = clone $specification;

        if (!is_null($propertyNameForSoftDelete = $classNameManagedEntity::entityInformation()->propertyNameForSoftDelete)) {
            $specification->where("AND {{$propertyNameForSoftDelete}} = 0");
        }

        return \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->fetchSingle($specification->select("COUNT(DISTINCT {{$primaryPropertyName}})")->constructQuery());
    }

    /**
     * @param \obo\Entity $entity
     * @param bool $forced
     * @throws \obo\Exceptions\EntityIsDeletedException
     * @throws \obo\Exceptions\EntityIsNotInitializedException
     * @throws \obo\Exceptions\ServicesException
     * @return void
     */
    public static function saveEntity(\obo\Entity $entity, $forced = false) {
        if (!$entity->isInitialized()) throw new \obo\Exceptions\EntityIsNotInitializedException("Cannot save entity which is not initialized");
        if (!$forced AND $entity->isDeleted()) throw new \obo\Exceptions\EntityIsDeletedException("Cannot save entity which is deleted");

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeSave", $entity);
        if (count($entity->dataWhoNeedToStore($entity->entityInformation()->columnsNamesToPropertiesNames($entity->entityInformation()->repositoryColumns)))) {
            if ($entity->isBasedInRepository()) {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeUpdate", $entity);
                self::repositoryMapper()->updateEntityInRepository($entity);
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterUpdate", $entity);
            } else {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeInsert", $entity);
                self::repositoryMapper()->insertEntityToRepository($entity);
                $entity->setBasedInRepository(true);
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterInsert", $entity);
            }
        }
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterSave", $entity);
    }

    /**
     * @param \obo\Entity $entity
     * @throws \obo\Exceptions\EntityIsNotInitializedException
     * @return void
     */
    public static function deleteEntity(\obo\Entity $entity) {
        if (!$entity->isInitialized()) throw new \obo\Exceptions\EntityIsNotInitializedException("Cannot delete entity which is not initialized");
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDelete", $entity);

        if (\is_null($propertyNameForSoftDelete = $entity->entityInformation()->propertyNameForSoftDelete)) {
            self::repositoryMapper()->removeEntityFromRepository($entity);
        } else {
            $entity->setValueForPropertyWithName(true, $propertyNameForSoftDelete);
            self::saveEntity($entity, true);
        }

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDelete", $entity);
    }
}