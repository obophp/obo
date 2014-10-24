<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class QueryCarrier extends \obo\Carriers\QuerySpecification implements \obo\Carriers\IQuerySpecification {

    protected $defaultEntityClassName = null;
    protected $select = array("query" => "", "data" => array());
    protected $from = array("query" => "", "data" => array());
    protected $join = array("query" => "", "data" => array());

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
        return $this->defaultEntityClassName = $defaultEntityClassName;
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
        $this->select = array("query" => "", "data" => array());
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
        $this->processArguments(func_get_args(), $this->join, " ");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteJoin($arguments) {
        $this->join = array("query" => "", "data" => array());
        return $this->join(func_get_args());
    }

    /**
     * @return sgtring
     * @throws \obo\Exceptions\Exception
     */
    public function dumpQuery() {
       if(\is_null($this->defaultEntityClassName)) throw new \obo\Exceptions\Exception("Unable to dump because it does not set default entity");
       $managerClass = $this->defaultEntityClassName->entityInformation()->managerName;
       return $managerClass::dataStorage()->constructQuery();
    }

}