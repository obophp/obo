<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IDataStorage {

    /**
     * @param string $repositoryName
     * @return boolean
     */
    public function existsRepositoryWithName($repositoryName);

    /**
     * @param string $repositoryName
     * return array
     */
    public function columnsInRepositoryWithName($repositoryName);

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return string
     */
    public function constructQuery(\obo\Carriers\QueryCarrier $queryCarrier);

    /**
     * @param \obo\Entity $entity
     * return array
     */
    public function dataForEntity(\obo\Entity $entity);

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * return array
     */
    public function dataFromQuery(\obo\Carriers\QueryCarrier $queryCarrier);

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @param name $primaryPropertyName
     * $return int
     */
    public function countRecordsForQuery(\obo\Carriers\QueryCarrier $queryCarrier, $primaryPropertyName);

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