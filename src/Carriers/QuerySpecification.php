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
    protected $where = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $orderBy = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $limit = ["query" => "", "data" => []];

    /**
     * @var array
     */
    protected $offset = ["query" => "", "data" => []];

    /**
     * @return array
     */
    public function getWhere() {
        return $this->where;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function where() {
        $this->processArguments(func_get_args(), $this->where, " ");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteWhere() {
        $this->where = ["query" => "", "data" => []];
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
    public function orderBy() {
        $this->processArguments(func_get_args(), $this->orderBy, " ", ",");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteOrderBy() {
        $this->orderBy = ["query" => "", "data" => []];
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
    public function limit() {
        $this->limit = ["query" => "", "data" => []];
        $this->processArguments(func_get_args(), $this->limit);
        return $this;
    }

    /**
     * @return array
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function offset() {
        $this->offset = ["query" => "", "data" => []];
        $this->processArguments(func_get_args(), $this->offset);
        return $this;
    }

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return self
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
     * @param string $suffix
     */
    protected function processArguments(array $arguments, array &$targetPart, $prefix = "", $suffix = "" ) {

        $formatArguments = [];
        $queryPosition = 0;
        $matches = [];
        if (count($arguments) == 1 AND is_array(\current($arguments))) $arguments = \current($arguments);

        foreach ($arguments as $argument) {
            if ($argument === null) continue;
            $formatArguments[] = $argument;
        }

        foreach ($formatArguments as $key => $argument) {
            if ($queryPosition == $key) {
                $queryPosition = \preg_match_all("#%([a-zA-Z~][a-zA-Z0-9~]{0,5})#", $argument, $matches) + $key + 1;
                $targetPart["query"] .= $prefix . $argument .$suffix;
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
