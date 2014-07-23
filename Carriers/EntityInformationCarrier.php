<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class EntityInformationCarrier extends \obo\Carriers\DataCarrier {
    public $className = "";
    public $managerName = "";
    public $propertiesClassName = "";
    public $repositoryName = "";
    public $repositoryColumns = array();
    public $primaryPropertyName = "id";
    public $propertiesInformation = array();
    public $annotations = array();
    public $propertiesForSerialization = array();
    public $propertyNameForSoftDelete = null;

    private $inversePropertiesInformationList = array();
    /**
     * @param array $information
     * @return \obo\Carriers\PropertyInformationCarrier
     */
    public function addPropertyInformation(array $information) {
        $propertyInformation = new \obo\Carriers\PropertyInformationCarrier($information);
        $this->propertiesInformation[$propertyInformation->name] = $propertyInformation;

        $propertyInformation->entityInformation = $this;
        return $propertyInformation;
    }

    /**
     * @param string $propertyName
     * @return \obo\Carriers\PropertyInformationCarrier
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function informationForPropertyWithName($propertyName) {
        if (!$this->existInformationForPropertyWithName($propertyName)) throw new \obo\Exceptions\PropertyNotFoundException("Property with name '{$propertyName}' does not exist in entity '{$this->className}'");
        return $this->propertiesInformation[$propertyName];
    }

    /**
     * @param string $columnName
     * @return \obo\Carriers\PropertyInformationCarrier
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function informationForPropertyThatIsMappedToColumnWithName($columnName) {
        if (!$this->existInformationForPropertyThatIsMappedToColumnWithName($columnName)) throw new \obo\Exceptions\PropertyNotFoundException("Property that is mapped to column with name '{$columnName}' does not exist in entity '{$this->className}'");
        return $this->inversePropertiesInformationList[$columnName];
    }

    /**
     * @param string $propertName
     * @return boolean
     */
    public function existInformationForPropertyWithName($propertName) {
        return isset($this->propertiesInformation[$propertName]);
    }

    /**
     * @param string $columnName
     * @return boolean
     */
    public function existInformationForPropertyThatIsMappedToColumnWithName($columnName) {
        return isset($this->inversePropertiesInformationList[$columnName]);
    }

    /**
     * @return void
     */
    public function processInformation() {
        foreach($this->propertiesInformation as $propertyInformation) {
            if (\is_null($propertyInformation->columnName) AND isset($this->repositoryColumns[$propertyInformation->name])) {
                $propertyInformation->columnName = $propertyInformation->name;
            }
            $this->inversePropertiesInformationList[$propertyInformation->columnName] = $propertyInformation;
        }
    }

    /**
     * @param array $propertiesNames
     * @param booleam $convertKeys
     * @return array
     */
    public function propertiesNamesToColumnsNames($propertiesNames, $convertKeys = true) {
        if ($convertKeys) {
            $convert = array();
            foreach ($propertiesNames as $propertyName => $propertyValue) {
                $convert[$this->informationForPropertyWithName($propertyName)->columnName] = $propertyValue;
            }
            return $convert;
        }

        foreach($propertiesNames as $key => $property) {
            $propertiesNames[$key] = $this->informationForPropertyWithName($property)->columnName;
        }

        return $propertiesNames;
    }

    /**
     * @param array $columnsNames
     * @param bolean $convertKeys
     * @return array
     */
    public function columnsNamesToPropertiesNames($columnsNames, $convertKeys = true) {

        if ($convertKeys) {
            $convert = array();
            foreach ($columnsNames as $columnName => $columnValue) {
                if (!$this->existInformationForPropertyThatIsMappedToColumnWithName($columnName)) continue;
                $convert[$this->informationForPropertyThatIsMappedToColumnWithName($columnName)->name] = $columnValue;
            }

            return $convert;
        }

        foreach ($columnsNames as $key => $column) {
            if (!$this->existInformationForPropertyThatIsMappedToColumnWithName($column)) continue;
            $columnsNames[$key] = $this->informationForPropertyThatIsMappedToColumnWithName($column)->name;
        }

        return $columnsNames;
    }

}