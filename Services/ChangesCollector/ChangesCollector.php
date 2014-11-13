<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Roman Pavlík
 * @copyright (c) 2011 - 2014 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\ChangesCollector;

class ChangesCollector extends \obo\Object {

    /** @var array */
    protected $changes = [];


    /**
     * @param array $resources
     * @param \obo\Entity $entity
     */
    public function collectChange(array $resources, \obo\Entity $entity) {
        foreach ($resources as $resource) $this->changes[$resource][$entity->entityIdentificationKey()] = $entity->propertiesChanges();
    }

    /**
     * @return array
     */
    public function getChanges() {
        return $this->changes;
    }
}