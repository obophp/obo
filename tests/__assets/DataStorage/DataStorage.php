<?php

namespace obo\Tests\Assets;

class DataStorage implements \obo\Interfaces\IDataStorage {

    const EVENT_TYPE_INSERT = "insert";
    const EVENT_TYPE_UPDATE = "update";
    const EVENT_TYPE_DELETE = "delete";
    const EVENT_TYPE_CONSTRUCT_QUERY = "constructQuery";
    const EVENT_TYPE_COUNT_ENTITIES_IN_RELATIONSHIP = "countEntitiesInRelationship";
    const EVENT_TYPE_COUNT_RECORDS_FOR_QUERY = "countRecordsForQuery";
    const EVENT_TYPE_CREATE_RELATIONSHIP_BETWEEN_ENTITIES = "createRelationshipBetweenEntities";
    const EVENT_TYPE_REMOVE_RELATIONSHIP_BETWEEN_ENTITIES = "removeRelationshipBetweenEntities";
    const EVENT_TYPE_DATA_FOR_ENTITIES_IN_RELATIONSHIP = "dataForEntitiesInRelationship";
    const EVENT_TYPE_DATA_FOR_QUERY = "dataForQuery";
    const EVENT_TYPE_REPOSITORY_ADDRESS_FOR_ENTITY = "repositoryAddressForEntity";

    public static $autoIncrementIndex = 0;

    protected $eventsStack = [
            self::EVENT_TYPE_INSERT => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_UPDATE => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_DELETE => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_CONSTRUCT_QUERY => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_COUNT_ENTITIES_IN_RELATIONSHIP => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_COUNT_RECORDS_FOR_QUERY => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_CREATE_RELATIONSHIP_BETWEEN_ENTITIES => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_REMOVE_RELATIONSHIP_BETWEEN_ENTITIES => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_DATA_FOR_ENTITIES_IN_RELATIONSHIP => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_DATA_FOR_QUERY => ["all" => [], "byKeys" => []],
            self::EVENT_TYPE_REPOSITORY_ADDRESS_FOR_ENTITY => ["all" => [], "byKeys" => []],
        ];

    /**
     * @param mixed $data
     * @param string $eventType
     * @param string $key
     * @return void
     */
    protected function logEvent($data, $eventType, $key = null) {
        $event = ["type" => $eventType, "data" => $data];

        $this->eventsStack[$eventType]["all"][] = $event;
        if ($key !== null) $this->eventsStack[$eventType]["byKeys"][$key][] = $event;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getAllEventsForType($type) {
        return isset($this->eventsStack[$type]["all"]) ? $this->eventsStack[$type]["all"] : [];
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getLastEventForType($type) {
        return isset($this->eventsStack[$type]["all"]) ? \end($this->eventsStack[$type]["all"]) : null;
    }

    /**
     * @param string $type
     * @param string $key
     * @return array
     */
    public function getAllEventsForTypeAndKey($type, $key) {
        return isset($this->eventsStack[$type]["byKeys"][$key]) ? $this->eventsStack[$type]["byKeys"][$key] : [];
    }

    /**
     * @param string $type
     * @param string $key
     * @return mixed
     */
    public function getLastEventForTypeAndKey($type, $key) {
        return isset($this->eventsStack[$type]["byKeys"][$key]) ? \end($this->eventsStack[$type]["byKeys"][$key]) : null;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return array
     */
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

        $this->logEventForConstructQuery($queryCarrier);
        return $queryData;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return void
     */
    public function logEventForConstructQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        $this->logEvent(["queryCarrier" => $queryCarrier], static::EVENT_TYPE_CONSTRUCT_QUERY, $queryCarrier->getDefaultEntityClassName());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableConstructQueryLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("constructQuery")
            ->with(\Mockery::on(function (\obo\Carriers\QueryCarrier $queryCarrier) {
                $this->logEventForConstructQuery($queryCarrier);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultConstructQueryBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("constructQuery")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->constructQuery($queryCarrier);
                }
            );
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @param string $repositoryName
     * @param \obo\Entity $owner
     * @param string $targetEntity
     * @return int
     */
    public function countEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
        $this->logEventForCountEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
        return 0;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @param string $repositoryName
     * @param \obo\Entity $owner
     * @param string $targetEntity
     * @return void
     */
    public function logEventForCountEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
        $this->logEvent(["queryCarrier" => $specification, "repositoryName" => $repositoryName, "owner" => $owner, "targetEntity" => $targetEntity], static::EVENT_TYPE_COUNT_ENTITIES_IN_RELATIONSHIP, $specification->getDefaultEntityClassName());
    }

    /**
     * @param type $dataStorageMock
     * @return void
     */
    public function enableCountEntitiesInRelationshipLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("countEntitiesInRelationship")
            ->with(\Mockery::on(function ($specification, $repositoryName, $owner, $targetEntity) {
                $this->logEventForCountEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultCountEntitiesInRelationshipBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("countEntitiesInRelationship")->andReturnUsing(
                function ($specification, $repositoryName, $owner, $targetEntity) {
                    return (new static)->countEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
                }
            );
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return int
     */
    public function countRecordsForQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        $this->logEventCountRecordsForQuery($queryCarrier);
        return 0;
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return void
     */
    public function logEventCountRecordsForQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        $this->logEvent(["queryCarrier" => $queryCarrier], static::EVENT_TYPE_COUNT_RECORDS_FOR_QUERY, $queryCarrier->getDefaultEntityClassName());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableCountRecordsForQueryLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("countRecordsForQuery")
            ->with(\Mockery::on(function ($queryCarrier) {
                $this->logEventCountRecordsForQuery($queryCarrier);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultCountRecordsForQueryBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("countRecordsForQuery")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->countRecordsForQuery($queryCarrier);
                }
            );
    }

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function createRelationshipBetweenEntities($repositoryName, array $entities) {
        $this->logEventForCreateRelationshipBetweenEntities($repositoryName, $entities);
    }

    /**
     * @param type $repositoryName
     * @param array $entities
     * @return void
     */
    public function logEventForCreateRelationshipBetweenEntities($repositoryName, array $entities) {
        $this->logEvent(["repositoryName" => $repositoryName, "entities" => $entities], static::EVENT_TYPE_CREATE_RELATIONSHIP_BETWEEN_ENTITIES);
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableCreateRelationshipBetweenEntitiesLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("createRelationshipBetweenEntities")
            ->with(\Mockery::on(function ($repositoryName, $entities) {
                $this->logEventForCreateRelationshipBetweenEntities($repositoryName, $entities);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultCreateRelationshipBetweenEntitiesBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("createRelationshipBetweenEntities")->andReturnUsing(
                function ($repositoryName, $entities) {
                    return (new static)->createRelationshipBetweenEntities($repositoryName, $entities);
                }
            );
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @param string $repositoryName
     * @param \obo\Entity $owner
     * @param string $targetEntity
     * @return array
     */
    public function dataForEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
        $this->logEventForDataForEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
        return [];
    }

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @param string $repositoryName
     * @param \obo\Entity $owner
     * @param string $targetEntity
     * @return void
     */
    public function logEventForDataForEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
        $this->logEvent(["queryCarrier" => $specification, "repositoryName" => $repositoryName, "owner" => $owner, "targetEntity" => $targetEntity], static::EVENT_TYPE_DATA_FOR_ENTITIES_IN_RELATIONSHIP, $specification->getDefaultEntityClassName());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableDataForEntitiesInRelationshipLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("dataForEntitiesInRelationship")
            ->with(\Mockery::on(function (\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity) {
                $this->logEventForDataForEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultDataForEntitiesInRelationshipBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("dataForEntitiesInRelationship")->andReturnUsing(
                function ($specification, $repositoryName, $owner, $targetEntity) {
                    return (new static)->dataForEntitiesInRelationship($specification, $repositoryName, $owner, $targetEntity);
                }
            );
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return arrya
     */
    public function dataForQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        $this->logEventForDataForQuery($queryCarrier);
        return [];
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return void
     */
    public function logEventForDataForQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        $this->logEvent(["queryCarrier" => $queryCarrier], static::EVENT_TYPE_DATA_FOR_QUERY, $queryCarrier->getDefaultEntityClassName());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableDataForQueryLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("dataForQuery")
            ->with(\Mockery::on(function (\obo\Carriers\QueryCarrier $queryCarrier) {
                $this->logEventForDataForQuery($queryCarrier);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultDataForQueryBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("dataForQuery")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->dataForQuery($queryCarrier);
                }
            );
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function insertEntity(\obo\Entity $entity) {
        $this->logEventForInsertEntity($entity);
        if ($entity->entityInformation()->informationForPropertyWithName($entity->entityInformation()->primaryPropertyName)->autoIncrement) {
            $entity->setValueForPropertyWithName(++static::$autoIncrementIndex, $entity->entityInformation()->primaryPropertyName, false);
        }
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function logEventForInsertEntity(\obo\Entity $entity) {
        $this->logEvent(["dataToStore" => $entity->dataToStore(), "entity" => $entity], static::EVENT_TYPE_INSERT, $entity->objectIdentificationKey());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableInsertEntityLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("insertEntity")
            ->with(\Mockery::on(function (\obo\Entity $entity) {
                $this->logEventForInsertEntity($entity);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultInsertEntityBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("insertEntity")->andReturnUsing(
                function ($queryCarrier) {
                    return (new static)->insertEntity($queryCarrier);
                }
            );
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function removeEntity(\obo\Entity $entity) {
        $this->logEventForRemoveEntity($entity);
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function logEventForRemoveEntity(\obo\Entity $entity) {
        $this->logEvent([$entity->entityInformation()->primaryPropertyName => $entity->primaryPropertyValue(), "softDelete" => $entity->entityInformation()->propertyNameForSoftDelete, "entity" => $entity], static::EVENT_TYPE_DELETE, $entity->objectIdentificationKey());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableRemoveEntityLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("removeEntity")
            ->with(\Mockery::on(function ($entity) {
                $this->logEventForRemoveEntity($entity);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultRemoveEntityBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("removeEntity")->andReturnUsing(
                function ($entity) {
                    return (new static)->removeEntity($entity);
                }
            );
    }

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function removeRelationshipBetweenEntities($repositoryName, array $entities) {
        $this->logEventForRemoveRelationshipBetweenEntities($repositoryName, $entities);
    }

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function logEventForRemoveRelationshipBetweenEntities($repositoryName, array $entities) {
        $this->logEvent(["repositoryName" => $repositoryName, "entities" => $entities], static::EVENT_TYPE_REMOVE_RELATIONSHIP_BETWEEN_ENTITIES);
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableRemoveRelationshipBetweenEntitiesLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("removeRelationshipBetweenEntities")
            ->with(\Mockery::on(function ($repositoryName, $entities) {
                $this->logEventForRemoveRelationshipBetweenEntities($repositoryName, $entities);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultRemoveRelationshipBetweenEntitiesBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("removeRelationshipBetweenEntities")->andReturnUsing(
                function ($repositoryName, $entities) {
                    return (new static)->removeRelationshipBetweenEntities($repositoryName, $entities);
                }
            );
    }

    /**
     * @param \obo\Entity $entity
     * @return string
     */
    public function repositoryAddressForEntity(\obo\Entity $entity) {
        $this->logEventForRepositoryAddressForEntity($entity);
        $entityInformation = $entity->entityInformation();
        return "[" . $this->getStorageNameForEntity($entityInformation) . "].[" . $entityInformation->repositoryName . "]";
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function logEventForRepositoryAddressForEntity(\obo\Entity $entity) {
        $this->logEvent(["entity" => $entity], static::EVENT_TYPE_REPOSITORY_ADDRESS_FOR_ENTITY, $entity->objectIdentificationKey());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableRepositoryAddressForEntityLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("repositoryAddressForEntity")
            ->with(\Mockery::on(function (\obo\Entity $entity) {
                $this->logEventForRepositoryAddressForEntity($entity);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultRepositoryAddressForEntityBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("repositoryAddressForEntity")->andReturnUsing(
                function ($entities) {
                    return (new static)->repositoryAddressForEntity($entities);
                }
            );
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function updateEntity(\obo\Entity $entity) {
        $this->logEventForUpdateEntity($entity);
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function logEventForUpdateEntity(\obo\Entity $entity) {
        $this->logEvent(["dataToStore" => $entity->dataToStore(), "entity" => $entity], static::EVENT_TYPE_UPDATE, $entity->objectIdentificationKey());
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function enableUpdateEntityLogging(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("updateEntity")
            ->with(\Mockery::on(function (\obo\Entity $entity) {
                $this->logEventForUpdateEntity($entity);
            }));
    }

    /**
     * @param \Mockery\MockInterface $dataStorageMock
     * @return void
     */
    public function setDefaultUpdateEntityBehavior(\Mockery\MockInterface $dataStorageMock) {
        $dataStorageMock->shouldReceive("updateEntity")->andReturnUsing(
                function ($entities) {
                    return (new static)->updateEntity($entities);
                }
            );
    }

}
