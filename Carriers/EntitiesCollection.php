<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class EntitiesCollection extends \obo\Carriers\DataCarrier implements \obo\Interfaces\IEntitiesCollection {

    protected $entitiesClassName = null;
    protected $defaultSpecification = null;
    protected $specification = null;
    protected $entitiesAreLoaded = false;

    /**
     * @param string $entitiesClassName
     * @param \obo\Carriers\QuerySpecification $specification
     */
    public function __construct($entitiesClassName, \obo\Carriers\QuerySpecification $specification) {
        parent::__construct();
        $this->entitiesClassName = $entitiesClassName;
        $this->specification = $specification;
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
     * @param \obo\Carriers\QuerySpecification $specification
     * @return void
     */
    public function addSpecification(\obo\Carriers\QuerySpecification $specification) {
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
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return \obo\Entity[]
     */
    public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null) {

        $specification = new \obo\Carriers\QuerySpecification();

        if (!\is_null($filter)) {
            $specification->addSpecification($filter->getSpecification());
        }

        $paginator->setItemCount($this->countEntities($specification));
        $specification->addSpecification($paginator->getSpecification());

        return $this->find($specification);
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return \obo\Entity[]
     */
    public function find(\obo\Carriers\QuerySpecification $specification) {
        $defaultSpecification = clone $this->specification;

        $entitiesClassName = $this->entitiesClassName;
        $entitiesManagerClassName = $entitiesClassName::entityInformation()->managerName;

        return $entitiesManagerClassName::findEntities($defaultSpecification->addSpecification($specification));
    }

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return int
     */
    public function countEntities (\obo\Carriers\QuerySpecification $specification = null) {
        $defaultSpecification = clone $this->specification;

        $ownedEntityClassName = $this->entitiesClassName;
        $ownedEntityManagerName = $ownedEntityClassName::entityInformation()->managerName;

        return $ownedEntityManagerName::countRecords(\obo\Carriers\QueryCarrier::instance()->addSpecification($defaultSpecification->addSpecification($specification)));
    }

    /**
     * @return void
     */
    public function loadEntities() {
        $entitiesClassName = $this->entitiesClassName;
        $entitiesManagerClassName = $entitiesClassName::entityInformation()->managerName;

        foreach ($entitiesManagerClassName::findEntities($this->specification) as $entity) $this->setValueForVariableWithName($entity, $entity->primaryPropertyValue());
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
