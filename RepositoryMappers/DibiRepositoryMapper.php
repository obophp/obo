<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\RepositoryMappers;

class DibiRepositoryMapper extends \obo\Object {

    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @return boolean
     */
    public function existRepositoryForEntity(\obo\Carriers\EntityInformationCarrier $entityInformation) {
        return (boolean) \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->fetchSingle($query = "SHOW TABLES LIKE '{$entityInformation->repositoryName}';");
    }

    /**
     * @param \obo\Entity $entity
     * @return array
     */
    public function columnsInRepositoryForEntity(\obo\Carriers\EntityInformationCarrier $entityInformation) {
        $tableName = $entityInformation->repositoryName;

        $query = "SHOW COLUMNS FROM [{$tableName}];";
        $tableColumns = array();
        foreach (\obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->fetchAll($query) as $column) $tableColumns[$column->Field] = $column->Type;
        return $tableColumns;
    }

    /**
     * @param string $columnName
     * @param \obo\Entity $entity
     * @return boolean
     */
    public function existRepositoryColumnWithNameForEntity($columnName, \obo\Entity $entity) {
        if (!$this->existRepositoryForEntity($entity->entityInformation())) return false;
        $tableColumn = self::columnsInRepositoryForEntity($entity);
        return isset($tableColumn[$columnName]);
    }

    /**
     * @param \obo\Entity $entity
     * @return array
     */
    public function dataForEntity(\obo\Entity $entity) {
        $tableName = $entity->entityInformation()->repositoryName;
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        $primaryPropertyColumnName = $entity->informationForPropertyWithName($primaryPropertyName)->columnName;
        $query = "SELECT * FROM [{$tableName}] WHERE [{$tableName}].[{$primaryPropertyColumnName}] = %i LIMIT 1";
        $data = \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->fetchAll($query,$entity->$primaryPropertyName);
        return isset($data[0]) ? (array) $data[0] : array();
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function insertEntityToRepository(\obo\Entity $entity) {
        if ($entity->isBasedInRepository()) {
            $this->updateEntityInRepository($entity);
        } else {
            \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->query("INSERT INTO [{$entity->entityInformation()->repositoryName}] ", $entity->entityInformation()->propertiesNamesToColumnsNames($entity->dataWhoNeedToStore($entity->entityInformation()->columnsNamesToPropertiesNames($entity->entityInformation()->repositoryColumns))));
            $entity->setValueForPropertyWithName(\obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->getInsertId(), $entity->entityInformation()->primaryPropertyName);
        }
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function updateEntityInRepository(\obo\Entity $entity) {
        $primaryPropertyName = $entity->entityInformation()->primaryPropertyName;
        $primaryPropertyColumnName = $entity->informationForPropertyWithName($primaryPropertyName)->columnName;
        \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->query("UPDATE [{$entity->entityInformation()->repositoryName}] SET %a", $entity->entityInformation()->propertiesNamesToColumnsNames($entity->dataWhoNeedToStore($entity->entityInformation()->columnsNamesToPropertiesNames($entity->entityInformation()->repositoryColumns))), "WHERE [{$entity->entityInformation()->repositoryName}].[{$primaryPropertyColumnName}] = %i", $entity->$primaryPropertyName);
    }

    /**
     * @param \obo\Entity $entity
     * @return void
     */
    public function removeEntityFromRepository(\obo\Entity $entity) {
        $primaryPropertyColumnName = $entity->informationForPropertyWithName($entity->entityInformation()->primaryPropertyName)->columnName;
        \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->query("DELETE FROM [{$entity->entityInformation()->repositoryName}] WHERE [{$entity->entityInformation()->repositoryName}].[{$primaryPropertyColumnName}] = %i LIMIT 1", $entity->primaryPropertyValue());
    }

    /**
     * @param string $repositoryName
     * @param mixed $specification
     * @return void
     */
    public function addRecordToRelationshipRepository($repositoryName, array $specification) {
        \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->query("INSERT INTO [{$repositoryName}] ", $specification);

    }

    /**
     * @param string $repositoryName
     * @param array $specification
     * @return void
     */
    public function removeRecordFromRelationshipRepository($repositoryName, array $specification) {
        $where = array("WHERE 1=1");
        foreach ($specification as $columnName => $columnValue) {
            $where = \array_merge($where, array(" AND [{$columnName}] = %s", $columnValue));

        }
        \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->query(array_merge(array("DELETE FROM [{$repositoryName}]"), $where));
    }

    /**
     * @return array
     */
    public function dataFromQuery($arguments) {
        $result = \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->query(func_get_args());
        $data = array();
        while ($record = $result->fetch()) $data[] = $record;
        return $data;
    }

    /**
     * @return string
     */
    public function constructQuery($arguments) {
        $queryTranslator = new \DibiTranslator(\obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER));
        return $queryTranslator->translate(func_get_args());
    }

    /**
     * @return void
     */
    public function dumpQuery($arguments) {
        \obo\Services::serviceWithName(\obo\obo::REPOSITORY_LAYER)->test($arguments);
    }
}