<?php

namespace obo\Tests\Assets;

class DataStorage implements \obo\Interfaces\IDataStorage {

    public static $autoIncrementIndex = 0;

    public function constructQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        $queryData = [];
        $queryData["defaultEntityName"] = $queryCarrier->getDefaultEntityEntityInformation()->name;
        $queryData["select"] = $queryCarrier->getSelect();
        $queryData["from"] = $queryCarrier->getFrom();
        $queryData["join"] = $queryCarrier->getJoin();
        $queryData["where"] = $queryCarrier->getWhere();
        $queryData["limit"] = $queryCarrier->getLimit();
        $queryData["offset"] = $queryCarrier->getOffset();
        $queryData["oderBy"] = $queryCarrier->getOrderBy();
        return $queryData;
    }

    public function countEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
        return 0;
    }

    public function countRecordsForQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        return 0;
    }

    public function createRelationshipBetweenEntities($repositoryName, array $entities) {
        return null;
    }

    public function dataForEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
        return [];
    }

    public function dataForQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        return [];
    }

    public function insertEntity(\obo\Entity $entity) {
        if ($entity->entityInformation()->informationForPropertyWithName($entity->entityInformation()->primaryPropertyName)->autoIncrement) {
            $entity->setValueForPropertyWithName(++static::$autoIncrementIndex, $entity->entityInformation()->primaryPropertyName, false);
        }
    }

    public function removeEntity(\obo\Entity $entity) {
        return null;
    }

    public function removeRelationshipBetweenEntities($repositoryName, array $entities) {
        return null;
    }

    public function repositoryAddressForEntity(\obo\Entity $entity) {
        $entityInformation = $entity->entityInformation();
        return "[" . $this->getStorageNameForEntity($entityInformation) . "].[" . $entityInformation->repositoryName . "]";
    }

    public function updateEntity(\obo\Entity $entity) {
        return null;
    }

    public static function setDefaultConstructQueryBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("constructQuery")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->constructQuery($queryCarrier);
                }
            );
    }

    public static function setDefaultCountEntitiesInRelationshipBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("countEntitiesInRelationship")->andReturnUsing(
                function ($specification, $repositoryName, $owner, $targetEntity) {
                    return (new static)->countEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
                }
            );
    }

    public static function setDefaultCountRecordsForQueryBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("countRecordsForQuery")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->countRecordsForQuery($queryCarrier);
                }
            );
    }

    public static function setDefaultCreateRelationshipBetweenEntitiesBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("createRelationshipBetweenEntities")->andReturnUsing(
                function ($repositoryName, $entities) {
                    return (new static)->createRelationshipBetweenEntities($repositoryName, $entities);
                }
            );
    }

    public static function setDefaultDataForEntitiesInRelationshipBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("dataForEntitiesInRelationship")->andReturnUsing(
                function ($specification, $repositoryName, $owner, $targetEntity) {
                    return (new static)->dataForEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
                }
            );
    }

    public function setDefaultDataForQueryBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("dataForQuery")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->dataForQuery($queryCarrier);
                }
            );
    }

    public static function setDefaultInsertEntityBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("insertEntity")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->insertEntity($queryCarrier);
                }
            );
    }

    public static function setDefaultRemoveEntityBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("removeEntity")->andReturnUsing(
                function ($entity) {
                    return (new static)->removeEntity($entity);
                }
            );
    }

    public static function setDefaultRemoveRelationshipBetweenEntitiesBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("removeRelationshipBetweenEntities")->andReturnUsing(
                function ($repositoryName, $entities) {
                    return (new static)->removeRelationshipBetweenEntities($repositoryName, $entities);
                }
            );
    }

    public static function setDefaultRepositoryAddressForEntityBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("repositoryAddressForEntity")->andReturnUsing(
                function ($entities) {
                    return (new static)->repositoryAddressForEntity($entities);
                }
            );
    }

    public static function setDefaultUpdateEntityBehavior($dataStorageMock) {
        $dataStorageMock->shouldReceive("updateEntity")->andReturnUsing(
                function ($entities) {
                    return (new static)->updateEntity($entities);
                }
            );
    }

}
