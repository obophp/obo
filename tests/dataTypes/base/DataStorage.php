<?php

namespace obo\Tests\DataTypes\Base;

class DataStorage implements \obo\Interfaces\IDataStorage {

    /**
     * @param string $repositoryName
     * @return boolean
     */
    public function existsRepositoryWithName($repositoryName) {
        return $repositoryName === "test";
    }

    /**
     * @param string $repositoryName
     * @return array
     */
    public function columnsInRepositoryWithName($repositoryName) {
        return [
            "id",
            "parray",
            "pboolean",
            "pdateTime",
            "pfloat",
            "pinteger",
            "pnumber",
            "pobject",
            "pstring",
        ];
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return string
     */
    public function constructQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        // TODO: Implement constructQuery() method.
    }

    /**
     * @param \obo\Entity $entity
     * return array
     */
    public function dataForEntity(\obo\Entity $entity) {
        // TODO: Implement dataForEntity() method.
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return array
     */
    public function dataFromQuery(\obo\Carriers\QueryCarrier $queryCarrier) {
        return [
            "id" => 1,
            "parray" => [],
            "pboolean" => false,
            "pdateTime" => null,
            "pfloat" => 0,
            "pinteger" => 0,
            "pnumber" => 0,
            "pobject" => null,
            "pstring" => "",
        ];
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @param string $primaryPropertyName
     * $return int
     */
    public function countRecordsForQuery(\obo\Carriers\QueryCarrier $queryCarrier, $primaryPropertyName) {
        // TODO: Implement countRecordsForQuery() method.
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function insertEntity(\obo\Entity $entity) {
        // TODO: Implement insertEntity() method.
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function updateEntity(\obo\Entity $entity) {
        // TODO: Implement updateEntity() method.
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function removeEntity(\obo\Entity $entity) {
        // TODO: Implement removeEntity() method.
    }

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function createRelationshipBetweenEntities($repositoryName, array $entities) {
        // TODO: Implement createRelationshipBetweenEntities() method.
    }

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function removeRelationshipBetweenEntities($repositoryName, array $entities) {
        // TODO: Implement removeRelationshipBetweenEntities() method.
    }
}
