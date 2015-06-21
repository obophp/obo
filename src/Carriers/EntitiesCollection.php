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
     * @var booleam
     */
    protected $entitiesAreLoaded = false;

    /**
     * @param string $entitiesClassName
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return void
     */
    public function __construct($entitiesClassName, \obo\Interfaces\IQuerySpecification $specification) {
        parent::__construct();
        $this->entitiesClassName = $entitiesClassName;
        $this->specification = $this->defaultSpecification = $specification;
    }

    /**
     * @return array
     */
    protected function &variables() {
        if (!$this->entitiesAreLoaded) {
            $this->entitiesAreLoaded = true;
            $this->loadEntities();
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
            return $managerClass::countRecords(\obo\Carriers\QueryCarrier::instance()->addSpecification($this->getSpecification()));
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
     * @return boolean
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
        return $entitiesManagerClassName::findEntities(\obo\Carriers\QueryCarrier::instance()->addSpecification($this->getSpecification())->addSpecification($specification));
    }

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Entity[]
     */
    public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null) {

        $specification = new \obo\Carriers\QueryCarrier();

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
        return $ownedEntityManagerName::countRecords(\obo\Carriers\QueryCarrier::instance()->addSpecification($this->getSpecification()->addSpecification($specification)));
    }

    /**
     * @return void
     */
    public function loadEntities() {
        $entitiesClassName = $this->entitiesClassName;
        $entitiesManagerClassName = $entitiesClassName::entityInformation()->managerName;
        foreach ($entitiesManagerClassName::findEntities($this->getSpecification()) as $entity) $this->setValueForVariableWithName($entity, $entity->primaryPropertyValue());
        $this->entitiesAreLoaded  = true;
    }

    /**
     * @return void
     */
    public function reloadEntitites() {
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
}
