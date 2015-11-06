<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IDataStorage {

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return string
     */
    public function constructQuery(\obo\Carriers\QueryCarrier $queryCarrier);

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * return array
     */
    public function dataForQuery(\obo\Carriers\QueryCarrier $queryCarrier);

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * $return int
     */
    public function countRecordsForQuery(\obo\Carriers\QueryCarrier $queryCarrier);

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function insertEntity(\obo\Entity $entity);

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function updateEntity(\obo\Entity $entity);

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function removeEntity(\obo\Entity $entity);


    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @param string $repositoryName
     * @param \obo\Entity $owner
     * @param string $targetEntity
     * @return int
     */
    public function countEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity);

    /**
     * @param \obo\Carriers\QueryCarrier $specification
     * @param string $repositoryName
     * @param \obo\Entity $owner
     * @param string $targetEntity
     * @return array
     */
    public function dataForEntitiesInRelationship(\obo\Carriers\QueryCarrier $specification, $repositoryName, \obo\Entity $owner, $targetEntity);

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function createRelationshipBetweenEntities($repositoryName, array $entities);

    /**
     * @param string $repositoryName
     * @param array $entities
     * @return void
     */
    public function removeRelationshipBetweenEntities($repositoryName, array $entities);
}
