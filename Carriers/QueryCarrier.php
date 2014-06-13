<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class QueryCarrier extends \obo\Object {
    private $defaultEntityClassName = null;
    private $select = array("query" => "", "data" => array());
    private $from = array("query" => "", "data" => array());
    private $join = array("query" => "", "data" => array());
    private $where = array("query" => "", "data" => array());
    private $groupBy = array("query" => "", "data" => array());
    private $orderBy = array("query" => "", "data" => array());
    private $limit = array("query" => "", "data" => array());
    private $offset = array("query" => "", "data" => array());

    /**
     * @param string $defaultEntityClassName
     * @return void
     */

    public function setDefaultEntityClassName($defaultEntityClassName) {
        $this->defaultEntityClassName = $defaultEntityClassName;
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
     * @return \obo\Carriers\QueryCarrier
     */
    public function from($arguments) {
        $this->processArguments(func_get_args(), $this->from, " ");
        return $this;
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
     * @return \obo\Carriers\QueryCarrier
     */
    public function groupBy($arguments) {
        $this->processArguments(func_get_args(), $this->groupBy, " ", ",");
        return $this;
    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public function rewriteGroupBy($arguments) {
        $this->groupBy = array("query" => "", "data" => array());
        return $this->groupBy(func_get_args());
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
     * @return \obo\Carriers\QueryCarrier
     */
    public function limit($arguments) {
        $this->limit = array("query" => "", "data" => array());
        $this->processArguments(func_get_args(), $this->limit);
        return $this;
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
     * @return void
     */
    public function dumpQuery() {
       \obo\Services::serviceWithName(\obo\obo::REPOSITORY_MAPPER)->dumpQuery($this->constructQuery());
    }

    /**
     * @return array
     */
    public function constructQuery() {
        $query = "";
        $data = array();
        $clone = clone $this;

        if (!is_null($clone->defaultEntityClassName)) {
            $joins = array();

            $this->convert($clone->select, $joins);
            $this->convert($clone->where, $joins);
            $this->convert($clone->groupBy, $joins);
            $this->convert($clone->orderBy, $joins);
            $this->convert($clone->join, $joins);
            $clone->join($joins);
        }

        $query.= "SELECT " . rtrim($clone->select["query"],",");
        $data = \array_merge($data, $clone->select["data"]);

        if ($clone->from["query"]=="") {
            $defaultEntityClassName = $this->defaultEntityClassName;
            $query.= " FROM [".$defaultEntityClassName::entityInformation()->repositoryName."]";
        } else {
            $query.= " FROM " . rtrim($clone->from["query"],",");
            $data = \array_merge($data, $clone->from["data"]);
        }

        $query.= rtrim($clone->join["query"], ",");
        $data = \array_merge($data, $clone->join["data"]);

        if ($clone->where["query"] !="") {
            $query.= " WHERE " . \preg_replace("#^ *(AND|OR) *#i", "", $clone->where["query"]);
            $data = \array_merge($data, $clone->where["data"]);
        }

        if ($clone->groupBy["query"] !="") {
            $query.= " GROUP BY " . rtrim($clone->groupBy["query"], ",");
            $data = \array_merge($data, $clone->groupBy["data"]);
        }

        if ($clone->orderBy["query"] !="") {
            $query.= " ORDER BY " . rtrim($clone->orderBy["query"], ",");
            $data = \array_merge($data, $clone->orderBy["data"]);
        }

        if ($clone->limit["query"] !="") {
            $query.= " LIMIT " . $clone->limit["query"];
            $data = \array_merge($data, $clone->limit["data"]);
        }

        if ($clone->offset["query"] !="") {
            $query.= " OFFSET " . $clone->offset["query"];
            $data = \array_merge($data, $clone->offset["data"]);
        }

        return \array_merge(array($query), $data);
    }

    /**
     * @param array $arguments
     * @param array $targetPart
     * @param string $prefix
     * @param string $sufix
     */
    private function processArguments(array $arguments, array &$targetPart, $prefix = "", $sufix = "" ) {

        $formatArguments = array();
        $queryPosition = 0;
        $matches = array();
        if (count($arguments) == 1 AND is_array(\current($arguments))) $arguments = \current($arguments);

        foreach ($arguments as $argument) {
            if (\is_null($argument)) continue;
            if (\is_array($argument)) {
                $formatArguments += $argument;
            } else {
                $formatArguments[] = $argument;
            }
        }

        foreach ($formatArguments as $key => $argument) {
            if ($queryPosition == $key) {
                $queryPosition = \preg_match_all("#%([a-zA-Z~][a-zA-Z0-9~]{0,5})#", $argument, $matches) + $key +1;
                $targetPart["query"] .= $prefix . $argument .$sufix;
            } else {
                $targetPart["data"][] = $argument;
            }
        }

    }

    /**
     * @param array $part
     * @param array $joins
     * @return void
     */
    private function convert(array &$part, array &$joins) {
        \preg_match_all("#(\{(.*?)\}\.?)+#", $part["query"], $blocks);
        foreach ($blocks[0] as $block) {
            $defaultEntityClassName = $this->defaultEntityClassName;
            $joinKey = null;
            $tableAlias = $defaultEntityClassName::entityInformation()->repositoryName;
            $items = \explode("}.{", trim($block, "{}"));

            if (count($items)>1) {
                foreach ($items as $item) {
                    $defaultPropertyInformation = $defaultEntityClassName::informationForPropertyWithName($item);
                    if (\is_null(($defaultPropertyInformation->relationship))) break;

                    if (isset($defaultPropertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName)
                            AND $defaultPropertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName)
                        throw new \obo\Exceptions\AutoJoinException("Functionality autojoin can not be used in non-static relationship ONE for property with name '{$defaultPropertyInformation->name}'");

                    $defaultEntityInformation = $defaultEntityClassName::entityInformation();
                    $entityClassNameToBeConnected = $defaultPropertyInformation->relationship->entityClassNameToBeConnected;
                    $joinKey = "{$defaultEntityClassName}->{$entityClassNameToBeConnected}";
                    $entityToBeConnectInformation = $entityClassNameToBeConnected::entityInformation();

                    if ($defaultPropertyInformation->relationship instanceof \obo\Relationships\One) {
                        $join = "LEFT JOIN [{$entityToBeConnectInformation->repositoryName}] as [{$joinKey}]
                                ON [{$tableAlias}].[".$defaultEntityInformation->propertiesInformation[$defaultPropertyInformation->relationship->ownerPropertyName]->columnName."]

                                = [{$joinKey}].[".$entityClassNameToBeConnected::informationForPropertyWithName($entityToBeConnectInformation->primaryPropertyName)->columnName."]";

                    } elseif ($defaultPropertyInformation->relationship instanceof \obo\Relationships\Many) {
                        if (\is_null($defaultPropertyInformation->relationship->connectViaRepositoryWithName)) {
                            $join = "LEFT JOIN [{$entityToBeConnectInformation->repositoryName}] as [{$joinKey}]
                                    ON [{$joinKey}].[".$entityToBeConnectInformation->propertiesInformation[$defaultPropertyInformation->relationship->connectViaPropertyWithName]->columnName."]

                                    = [{$tableAlias}].[".$defaultEntityClassName::informationForPropertyWithName($defaultEntityInformation->primaryPropertyName)->columnName."]";

                            if (!\is_null($defaultPropertyInformation->relationship->ownerNameInProperty)) {
                                $join .= " AND [{$joinKey}].[{$defaultPropertyInformation->relationship->ownerNameInProperty}] = '{$defaultPropertyInformation->entityInformation->className}'";
                            }

                        } elseif (\is_null($defaultPropertyInformation->relationship->connectViaPropertyWithName)) {
                            $join = "
                                    LEFT JOIN [{$defaultPropertyInformation->relationship->connectViaRepositoryWithName}]
                                    ON [{$defaultPropertyInformation->relationship->connectViaRepositoryWithName}].[{$defaultEntityInformation->repositoryName}]
                                    = [{$tableAlias}].[".$defaultEntityClassName::informationForPropertyWithName($defaultEntityInformation->primaryPropertyName)->columnName."]

                                    LEFT JOIN [{$entityToBeConnectInformation->repositoryName}] as [{$joinKey}]
                                    ON [{$defaultPropertyInformation->relationship->connectViaRepositoryWithName}].[{$entityToBeConnectInformation->repositoryName}]
                                    = [{$joinKey}].[".$entityClassNameToBeConnected::informationForPropertyWithName($entityToBeConnectInformation->primaryPropertyName)->columnName."]";

                        }
                    }

                    $defaultEntityClassName = $entityClassNameToBeConnected;
                    $tableAlias = $joinKey;
                    $joins[$joinKey] = $join;
                }
            } else {
                $defaultEntityInformation = $defaultEntityClassName::entityInformation();
                $defaultPropertyInformation = $defaultEntityClassName::informationForPropertyWithName($items[0]);
            }

            $part["query"] = \preg_replace("#(\{(.*?)\}\.?)+#", "[{$tableAlias}].[{$defaultPropertyInformation->columnName}]", $part["query"], 1);
        }

    }

    /**
     * @return \obo\Carriers\QueryCarrier
     */
    public static function instance() {
        return new QueryCarrier();
    }

}