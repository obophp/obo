<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class QuerySpecification extends \obo\Object implements \obo\Interfaces\IQuerySpecification {

    /**
     * @var array
     */
    protected $where = array("query" => "", "data" => array());

    /**
     * @var array
     */
    protected $orderBy = array("query" => "", "data" => array());

    /**
     * @var array
     */
    protected $limit = array("query" => "", "data" => array());

    /**
     * @var array
     */
    protected $offset = array("query" => "", "data" => array());

    /**
     * @return array
     */
    public function getWhere() {
        return $this->where;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function where($arguments) {
        $this->processArguments(func_get_args(), $this->where, " ");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteWhere($arguments) {
        $this->where = array("query" => "", "data" => array());
        return $this->where(func_get_args());
    }

    /**
     * @return array
     */
    public function getOrderBy() {
        return $this->orderBy;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function orderBy($arguments) {
        $this->processArguments(func_get_args(), $this->orderBy, " ", ",");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteOrderBy($arguments) {
        $this->orderBy = array("query" => "", "data" => array());
        return $this->orderBy(func_get_args());
    }

    /**
     * @return array
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function limit($arguments) {
        $this->limit = array("query" => "", "data" => array());
        $this->processArguments(func_get_args(), $this->limit);
        return $this;
    }

    /**
     * @return array()
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function offset($arguments) {
        $this->offset = array("query" => "", "data" => array());
        $this->processArguments(func_get_args(), $this->offset);
        return $this;
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     */
    public function addSpecification(\obo\Interfaces\IQuerySpecification $specification) {

        $where = $specification->getWhere();
        $this->where["query"] .= $where["query"];
        $this->where["data"] = \array_merge($this->where["data"], $where["data"]);

        $orderBy = $specification->getOrderBy();
        $this->orderBy["query"] .= $orderBy["query"];
        $this->orderBy["data"] = \array_merge($this->orderBy["data"], $orderBy["data"]);

        $offset = $specification->getOffset();
        $this->offset["query"] = $offset["query"];
        $this->offset["data"] = $offset["data"];

        $limit = $specification->getLimit();
        $this->limit["query"] = $limit["query"];
        $this->limit["data"] = $limit["data"];

        return $this;
    }

    /**
     * @param array $arguments
     * @param array $targetPart
     * @param string $prefix
     * @param string $sufix
     */
    protected function processArguments(array $arguments, array &$targetPart, $prefix = "", $sufix = "" ) {

        $formatArguments = array();
        $queryPosition = 0;
        $matches = array();
        if (count($arguments) == 1 AND is_array(\current($arguments))) $arguments = \current($arguments);

        foreach ($arguments as $argument) {
            if (\is_null($argument)) continue;
            $formatArguments[] = $argument;
        }

        foreach ($formatArguments as $key => $argument) {
            if ($queryPosition == $key) {
                $queryPosition = \preg_match_all("#%([a-zA-Z~][a-zA-Z0-9~]{0,5})#", $argument, $matches) + $key + 1;
                $targetPart["query"] .= $prefix . $argument .$sufix;
            } else {
                $targetPart["data"][] = $argument;
            }
        }

    }

    /**
     * @return \obo\Carriers\QuerySpecification
     */
    public static function instance() {
        return new static;
    }
}
