<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\IdentityMapper;

class IdentityMapper extends \obo\Object {

    /**
     * @var \obo\Entity[]
     */
    private $entities = [];

    /**
     * @param \obo\Entity $entity
     * @return string
     */
    public function identificationKeyForEntity(\obo\Entity $entity) {
        return $entity->primaryPropertyValue() ? $entity->className() . $entity->primaryPropertyValue() : null;
    }

    /**
     * @param \obo\Entity $entity
     * @return \obo\Entity
     */
    public function mappedEntity(\obo\Entity $entity) {
        if ($entity->entityIdentificationKey() === null) return null;
        if (isset($this->entities[$entity->entityIdentificationKey()])) {
            return $this->entities[$entity->entityIdentificationKey()]["entity"];
        } else {
            $this->entities[$entity->entityIdentificationKey()] = ["entity" => $entity];
            return $entity;
        }
    }

    /**
     * @param \obo\Entity $entity
     * @return bool
     */
    public function isMappedEntity(\obo\Entity $entity) {
        return isset($this->entities[$entity->entityIdentificationKey()]);
    }

    /**
     * @param string $entityIdentificationKey
     * @return \obo\Entity
     */
    public function entityWithIdentificationKey($entityIdentificationKey) {
        return isset($this->entities[$entityIdentificationKey]) ? $this->entities[$entityIdentificationKey]["entity"] : null;
    }


    public function __destruct() {
        foreach ($this->entities as $entity) {
            \obo\obo::$eventManager->notifyEventForEntity("beforeDeath", $entity["entity"]);
        }
    }

}
