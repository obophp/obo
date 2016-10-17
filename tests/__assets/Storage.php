<?php

namespace obo\Tests\Assets;

class Storage {

    /**
     * @var \Mockery\Mock
     */
    private static $mockStorage = null;

    /**
     * @return \Mockery\Mock
    */
    public static function getMockDataStorage() {
        if (static::$mockStorage === null) {
            static::$mockStorage = \Mockery::mock("\\obo\\Interfaces\\IDataStorage");
            static::$mockStorage->shouldReceive("constructQuery")->andReturn("");
            static::$mockStorage->shouldReceive("dataForQuery")->andReturn([]);
            static::$mockStorage->shouldReceive("countRecordsForQuery")->andReturn(0);
            static::$mockStorage->shouldReceive("insertEntity")->andReturnNull();
            static::$mockStorage->shouldReceive("updateEntity")->andReturnNull();
            static::$mockStorage->shouldReceive("removeEntity")->andReturnNull();
            static::$mockStorage->shouldReceive("countEntitiesInRelationship")->andReturn(0);
            static::$mockStorage->shouldReceive("dataForEntitiesInRelationship")->andReturn([]);
            static::$mockStorage->shouldReceive("createRelationshipBetweenEntities")->andReturnNull();
            static::$mockStorage->shouldReceive("removeRelationshipBetweenEntities")->andReturnNull();
        }

        return static::$mockStorage;
    }

}
