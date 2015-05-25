<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class QueryCarrier extends \obo\Carriers\QuerySpecification implements \obo\Interfaces\IQuerySpecification {

    /**
     * @var string
     */
    protected $defaultEntityClassName = null;

    /**
     * @var \obo\Carriers\EntityInformationCarrier
     */
    protected $defaultEntityEntityInformation = null;

    /**
     * @var array
     */
    protected $select = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $from = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $join = ["query" => "", "data" => []];

    /**
     * @return string
     */
    public function getDefaultEntityClassName() {
        return $this->defaultEntityClassName;
    }

    /**
     * @param string $defaultEntityClassName
     * @return string
     */
    public function setDefaultEntityClassName($defaultEntityClassName) {
        $this->defaultEntityClassName = $defaultEntityClassName;
        $this->defaultEntityEntityInformation = $defaultEntityClassName::entityInformation();
    }

    /**
     * @return \obo\Carriers\EntityInformationCarrier
     */
    public function getDefaultEntityEntityInformation() {
        return $this->defaultEntityEntityInformation;
    }

    /**
     * @return array
     */
    public function getSelect() {
        return $this->select;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function select($arguments) {
        $this->processArguments(func_get_args(), $this->select, " ", ",");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteSelect($arguments) {
        $this->select = ["query" => "", "data" => []];
        return $this->select(func_get_args());
    }

    /**
     * @return array
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function from($arguments) {
        $this->processArguments(func_get_args(), $this->from, " ");
        return $this;
    }

    /**
     * @return array
     */
    public function getJoin() {
        return $this->join;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function join($arguments) {
        $this->processArguments(\func_get_args(), $this->join, " ");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteJoin($arguments) {
        $this->join = ["query" => "", "data" => []];
        return $this->join(\func_get_args());
    }

    /**
     * @param \obo\Carriers\QueryCarrier $queryCarrier
     * @return \obo\Carriers\QueryCarrier
     */
    public function addQueryCarrier(\obo\Carriers\QueryCarrier $queryCarrier) {
        parent::addSpecification($queryCarrier);

        $join = $queryCarrier->getJoin();
        $this->join["query"] .= $join["query"];
        $this->join["data"] = \array_merge($this->join["data"], $join["data"]);

        return $this;
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return \obo\Carriers\QueryCarrier
     */
    public function addSpecification(\obo\Interfaces\IQuerySpecification $specification) {
        return $specification instanceof \obo\Carriers\QueryCarrier ? $this->addQueryCarrier($specification) : parent::addSpecification($specification);
    }

    /**
     * @return string
     * @throws \obo\Exceptions\Exception
     */
    public function dumpQuery() {
        if (\is_null($this->defaultEntityClassName)) throw new \obo\Exceptions\Exception("Unable to dump query because default Entity is not set");
        $defaultEntityClassName = $this->defaultEntityClassName;
        $managerClass = $defaultEntityClassName::entityInformation()->managerName;
        return $managerClass::dataStorage()->constructQuery($this);
    }
}
