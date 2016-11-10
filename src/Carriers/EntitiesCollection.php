<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class EntitiesCollection extends \obo\Carriers\DataCarrier implements \obo\Interfaces\IEntitiesCollection {

    /**
     * @var string
     */
    protected $entitiesClassName = null;

    /**
     * @var \obo\Interfaces\IQuerySpecification
     */
    protected $defaultSpecification = null;

    /**
     * @var \obo\Interfaces\IQuerySpecification
     */
    protected $specification = null;

    /**
     * @var bool
     */
    protected $entitiesAreLoaded = false;

    /**
     * @param string $entitiesClassName
     * @param \obo\Interfaces\IQuerySpecification $specification
     */
    public function __construct($entitiesClassName, \obo\Interfaces\IQuerySpecification $specification) {
        parent::__construct();
        $this->entitiesClassName = $entitiesClassName;
        $this->specification = $this->defaultSpecification = $specification;
    }

    /**
     * @param array $requiredItems
     * @return array
     */
    protected function &variables(array $requiredItems = null) {
        if ($requiredItems !== null) {
            $variables = parent::variables();
            foreach ($requiredItems as $key => $requiredItem) if (isset($variables[$requiredItem])) unset($requiredItems[$key]);
        }

        if (!$this->entitiesAreLoaded AND ((\count($requiredItems) !== 0) OR $requiredItems === null)) {
            $this->entitiesAreLoaded = $requiredItems === null;
            $this->loadEntities($requiredItems);
        }

        return parent::variables();
    }

    /**
     * @return int
     */
    public function count() {
        if ($this->entitiesAreLoaded) {
            return parent::count();
        } else {
            $entityClass = $this->entitiesClassName;
            $managerClass = $entityClass::entityInformation()->managerName;
            return $managerClass::countRecords($managerClass::queryCarrier()->addSpecification($this->getSpecification()));
        }
    }

    /**
     * return only clone original specification other modifications will not affect the original specification
     * @return \obo\Interfaces\IQuerySpecification
     */
    public function getDefaultSpecification() {
        return clone $this->defaultSpecification;
    }

    /**
     * return only clone original specification other modifications will not affect the original specification
     * @return \obo\Interfaces\IQuerySpecification
     */
    public function getSpecification() {
        return clone $this->specification;
    }

    /**
     * @return string
     */
    public function getEntitiesClassName() {
        return $this->entitiesClassName;
    }

    /**
     * @return bool
     */
    public function getEntitiesAreLoaded() {
        return $this->entitiesAreLoaded;
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return void
     */
    public function addSpecification(\obo\Interfaces\IQuerySpecification $specification) {
        $this->specification->addSpecification($specification);
        $this->clear();
    }

    /**
     * @return void
     */
    public function resetToDefaultSpecification() {
        $this->specification = $this->defaultSpecification;
        $this->clear();
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return \obo\Entity[]
     */
    public function find(\obo\Interfaces\IQuerySpecification $specification) {
        $entitiesClassName = $this->entitiesClassName;
        $entitiesManagerClassName = $entitiesClassName::entityInformation()->managerName;
        return $entitiesManagerClassName::findEntities($entitiesManagerClassName::queryCarrier()->addSpecification($this->getSpecification())->addSpecification($specification));
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Entity[]
     */
    public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null) {
        $ownedEntityClassName = $this->entitiesClassName;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        $specification = $ownedEntityManagerName::queryCarrier();

        if ($filter !== null) {
            $specification->addSpecification($filter->getSpecification());
        }

        $paginator->setItemCount($this->countEntities($specification));
        $specification->addSpecification($paginator->getSpecification());

        return $this->find($specification);
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return int
     */
    public function countEntities (\obo\Interfaces\IQuerySpecification $specification = null) {
        $ownedEntityClassName = $this->entitiesClassName;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;
        return $ownedEntityManagerName::countRecords($ownedEntityManagerName::queryCarrier()->addSpecification($this->getSpecification()->addSpecification($specification)));
    }

    /**
     * @param array $entityKeys
     * @return void
     */
    public function loadEntities(array $entityKeys = null) {
        $entitiesClassName = $this->entitiesClassName;
        $entitiesManagerClassName = $entitiesClassName::entityInformation()->managerName;

        if ($entityKeys === null) {
            $specification = $this->getSpecification();
        } else {
            $specification = new \obo\Carriers\QuerySpecification();
            $specification->addSpecification($this->getSpecification());
            $specification->where("AND {{$entitiesClassName::entityInformation()->primaryPropertyName}} IN (?)", $entityKeys);
        }

        $variables = &parent::variables();

        foreach ($entitiesManagerClassName::findEntities($specification) as $entity) $variables[$entity->primaryPropertyValue()] = $entity;
    }

    /**
     * @return void
     */
    public function reloadEntities() {
        $this->clear();
        $this->loadEntities();
    }

    /**
     * @return void
     */
    public function clear() {
        parent::clear();
        $this->entitiesAreLoaded = false;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \obo\Exceptions\VariableNotFoundException
     */
    public function &variableForName($name) {
        $variables = $this->variables([$name]);
        if (isset($variables[$name]) OR \array_key_exists($name, $variables)) return $variables[$name];
        throw new \obo\Exceptions\EntityNotFoundException("Entity '" . $this->entitiesClassName . "' with primary property value '{$name}' does not exist in collection");
    }

    /**
     * @param mixed $value
     * @param string $variableName
     * @return mixed
     */
    public function setValueForVariableWithName($value, $variableName) {
        return $this->variables([$variableName])[$variableName] = $value;
    }

    /**
     * @param string $variableName
     * @return void
     */
    public function unsetValueForVariableWithName($variableName) {
        $this->variableForName($variableName);
        unset($this->variables([$variableName])[$variableName]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        $variables = $this->variables([$name]);
        return isset($variables[$name]) OR \array_key_exists($name, $variables);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->setValueForVariableWithName($value, $offset);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->variables($offset)[$offset]);
    }

    /**
     * @throws \obo\Exceptions\Exception
     */
    public function __clone() {
        throw new \obo\Exceptions\Exception('Obo entities collection is not cloneable');
    }

}
