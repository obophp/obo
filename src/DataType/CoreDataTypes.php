<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType;

class CoreDataTypes extends \obo\Object {

    public static function register(\obo\Services\EntitiesInformation\Explorer $entitiesExplorer) {
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\ArrayDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\BooleanDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\DateTimeDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\EntityDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\FloatDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\IntegerDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\MixedDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\NumberDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\ObjectDataType");
        $entitiesExplorer->registerDatatype("\\obo\\DataType\\StringDataType");
    }

}
