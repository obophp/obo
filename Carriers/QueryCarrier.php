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
     * @param array $part
     * @param array $joins
     * @return void
     */
    private function convert(array &$part, array &$joins) {
        \preg_match_all("#(\{(.*?)\}\.?)+#", $part["query"], $blocks);
        foreach ($blocks[0] as $block) {
            $defaultEntityClassName = $this->defaultEntityClassName;
            $joinKey = null;
            $ownerRepositoryName = $defaultEntityClassName::entityInformation()->repositoryName;
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

                        $join = self::oneRelationshipJoinQuery(
                                    $entityToBeConnectInformation->repositoryName,//$ownedRepositoryName
                                    $joinKey,//$joinKey
                                    $ownerRepositoryName,//$ownerRepositoryName
                                    $defaultEntityInformation->propertiesInformation[$defaultPropertyInformation->relationship->ownerPropertyName]->columnName,//$foreignKeyColumnName
                                    $entityClassNameToBeConnected::informationForPropertyWithName($entityToBeConnectInformation->primaryPropertyName)->columnName,//$ownedEntityPrimaryColumnName
                                    $entityToBeConnectInformation->propertyNameForSoftDelete ? $entityToBeConnectInformation->informationForPropertyWithName($entityToBeConnectInformation->propertyNameForSoftDelete)->columnName : null//$propertyNameForSoftDelete
                                );

                    } elseif ($defaultPropertyInformation->relationship instanceof \obo\Relationships\Many) {

                        if (\is_null($defaultPropertyInformation->relationship->connectViaRepositoryWithName)) {

                            $join = self::manyViaPropertyRelationshipJoinQuery(
                                        $entityToBeConnectInformation->repositoryName,//$ownedRepositoryName
                                        $joinKey,//$joinKey
                                        $ownerRepositoryName,//$ownerRepositoryName
                                        $entityToBeConnectInformation->propertiesInformation[$defaultPropertyInformation->relationship->connectViaPropertyWithName]->columnName,//$foreignKeyColumnName
                                        $defaultEntityClassName::informationForPropertyWithName($defaultEntityInformation->primaryPropertyName)->columnName,//$ownedEntityPrimaryColumnName
                                        $entityToBeConnectInformation->propertyNameForSoftDelete ? $entityToBeConnectInformation->informationForPropertyWithName($entityToBeConnectInformation->propertyNameForSoftDelete)->columnName : null//$propertyNameForSoftDelete
                                    );

                            if (!\is_null($defaultPropertyInformation->relationship->ownerNameInProperty)) {
                                $join .= self::manyViaPropertyRelationshipExtendsJoinQuery(
                                            $joinKey,//$joinKey
                                            $defaultPropertyInformation->relationship->ownerNameInProperty,//$ownerNameInPropertyWithName
                                            $defaultPropertyInformation->entityInformation->className//$ownerClassName
                                        );
                            }

                        } elseif (\is_null($defaultPropertyInformation->relationship->connectViaPropertyWithName)) {
                            $join = self::manyViaRepostioryRelationshipJoinQuery(
                                        $joinKey,//$joinKey
                                        $defaultPropertyInformation->relationship->connectViaRepositoryWithName,//$connectViaRepositoryWithName
                                        $ownerRepositoryName,//$ownerRepositoryName
                                        $entityToBeConnectInformation->repositoryName,//$ownedRepositoryName
                                        $defaultEntityClassName::informationForPropertyWithName($defaultEntityInformation->primaryPropertyName)->columnName,//$ownerPrimaryPropertyColumnName
                                        $entityClassNameToBeConnected::informationForPropertyWithName($entityToBeConnectInformation->primaryPropertyName)->columnName,//$ownedPrimaryPropertyColumnName
                                        $entityToBeConnectInformation->propertyNameForSoftDelete ? $entityToBeConnectInformation->informationForPropertyWithName($entityToBeConnectInformation->propertyNameForSoftDelete)->columnName : null//$propertyNameForSoftDelete
                                    );
                        }
                    }

                    $defaultEntityClassName = $entityClassNameToBeConnected;
                    $ownerRepositoryName = $joinKey;
                    $joins[$joinKey] = $join;
                }
            } else {
                $defaultEntityInformation = $defaultEntityClassName::entityInformation();
                $defaultPropertyInformation = $defaultEntityClassName::informationForPropertyWithName($items[0]);
            }

            $part["query"] = \preg_replace("#(\{(.*?)\}\.?)+#", "[{$ownerRepositoryName}].[{$defaultPropertyInformation->columnName}]", $part["query"], 1);
        }

    }

    /**
     * @param string $ownedRepositoryName
     * @param string $joinKey
     * @param string $ownerRepositoryName
     * @param string $foreignKeyColumnName
     * @param string $ownedEntityPrimaryColumnName
     * @param string $columnNameForSoftDelete
     * @return string
     */
    protected static function oneRelationshipJoinQuery($ownedRepositoryName, $joinKey, $ownerRepositoryName, $foreignKeyColumnName, $ownedEntityPrimaryColumnName, $columnNameForSoftDelete) {
        $softDeleteClausule = $columnNameForSoftDelete ? " AND [{$joinKey}].[{$columnNameForSoftDelete}] = 0" : "";
        return "LEFT JOIN [{$ownedRepositoryName}] as [{$joinKey}] ON [{$ownerRepositoryName}].[{$foreignKeyColumnName}] = [{$joinKey}].[{$ownedEntityPrimaryColumnName}]{$softDeleteClausule}";
    }

    /**
     * @param string $ownedRepositoryName
     * @param string $joinKey
     * @param string $ownerRepositoryName
     * @param string $foreignKeyColumnName
     * @param string $ownedEntityPrimaryColumnName
     * @param string $columnNameForSoftDelete
     * @return string
     */
    protected static function manyViaPropertyRelationshipJoinQuery($ownedRepositoryName, $joinKey, $ownerRepositoryName, $foreignKeyColumnName, $ownedEntityPrimaryColumnName, $columnNameForSoftDelete) {
        $softDeleteClausule = $columnNameForSoftDelete ? " AND [{$joinKey}].[{$columnNameForSoftDelete}] = 0" : "";
        return "LEFT JOIN [{$ownedRepositoryName}] as [{$joinKey}] ON [{$joinKey}].[{$foreignKeyColumnName}] = [{$ownerRepositoryName}].[{$ownedEntityPrimaryColumnName}]{$softDeleteClausule}";
    }

    /**
     * @param type $joinKey
     * @param type $ownerNameInPropertyWithName
     * @param type $ownerClassName
     * @return type
     */
    protected static function manyViaPropertyRelationshipExtendsJoinQuery($joinKey, $ownerNameInPropertyWithName, $ownerClassName) {
        return " AND [{$joinKey}].[{$ownerNameInPropertyWithName}] = '{$ownerClassName}'";
    }

    /**
     * @param string $joinKey
     * @param string $connectViaRepositoryWithName
     * @param string $ownerRepositoryName
     * @param string $ownedRepositoryName
     * @param string $ownerPrimaryPropertyColumnName
     * @param string $ownedPrimaryPropertyColumnName
     * @param string $columnNameForSoftDelete
     * @return string
     */
    protected static function manyViaRepostioryRelationshipJoinQuery($joinKey, $connectViaRepositoryWithName, $ownerRepositoryName, $ownedRepositoryName, $ownerPrimaryPropertyColumnName, $ownedPrimaryPropertyColumnName, $columnNameForSoftDelete) {
        $softDeleteClausule = $columnNameForSoftDelete ? " AND [{$joinKey}].[{$columnNameForSoftDelete}] = 0" : "";
        return "LEFT JOIN [{$connectViaRepositoryWithName}]
                ON [{$connectViaRepositoryWithName}].[{$ownerRepositoryName}]
                = [{$ownerRepositoryName}].[{$ownerPrimaryPropertyColumnName}]
                LEFT JOIN [{$ownedRepositoryName}] as [{$joinKey}]
                ON [{$connectViaRepositoryWithName}].[{$ownedRepositoryName}]
                = [{$joinKey}].[{$ownedPrimaryPropertyColumnName}]{$softDeleteClausule}";
    }

}