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
     * @var array
     */
    protected $parameters = [];

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
     * @param array $parameters
     * @param bool $forced
     * @throws \obo\Exceptions\Exception
     */
    public function bindParameters(array $parameters, $forced = false) {
        if ($forced === true OR \count(\array_intersect_key($this->parameters, $parameters)) === 0) {
            foreach ($parameters as $parameterName => $parameterValue) $this->parameters[$parameterName] = $parameterValue;
        } else {
            throw new \obo\Exceptions\Exception("Parameters [" . \implode(", ", \array_keys (\array_intersect_key($this->parameters, $parameters))) . "] are already bound to query specification, to overwrite parameters, set 'forced=true' as a second parameter of the bindParameters method.");
        }
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

        if (\count($arguments) == 1 AND \is_array(\current($arguments))) $arguments = \current($arguments);
        $formatedArguments = [];

        foreach ($arguments as $argument) {
            if ($argument === null) continue;
            $formatedArguments[] = $argument;
        }

        for (\reset($formatedArguments); \key($formatedArguments) !== null; \next($formatedArguments)) {
            $argument = \current($formatedArguments);
            $matches = [];
            $matched = \preg_match_all("#(%([a-zA-Z~][a-zA-Z0-9~]{0,5}))(\s+|$|\))|(" . \preg_quote(\obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER) . ")|(" . \preg_quote(\obo\Interfaces\IQuerySpecification::PARAMETER_PREFIX) . "[a-zA-Z~_]([a-zA-Z0-9~_])+)#", $argument, $matches);

            $targetPart["query"] .= $prefix . \preg_replace("#(" . \preg_quote(\obo\Interfaces\IQuerySpecification::PARAMETER_PREFIX) . "[a-zA-Z~_]([a-zA-Z0-9~_])+)#", \obo\Interfaces\IQuerySpecification::PARAMETER_PLACEHOLDER, $argument) . $suffix;

            if ($matched) {
                foreach($matches[0] as $match) {
                    if (\strpos($match, \obo\Interfaces\IQuerySpecification::PARAMETER_PREFIX) === 0) {
                        $parameterName = ltrim($match, \obo\Interfaces\IQuerySpecification::PARAMETER_PREFIX);
                        if (!(isset($this->parameters[$parameterName]) OR \array_key_exists($parameterName, $this->parameters))) throw new \obo\Exceptions\Exception("Can not use parameter '{$parameterName}'. Parametr is not bound.");
                        $targetPart["data"][] = &$this->parameters[ltrim($match, \obo\Interfaces\IQuerySpecification::PARAMETER_PREFIX)];
                    } else {
                        $targetPart["data"][] = \next($formatedArguments);
                    }
                }
            }

            \next($formatedArguments);
        }

    }

    /**
     * @return \obo\Carriers\QuerySpecification
     */
    public static function instance() {
        return new static;
    }
}
