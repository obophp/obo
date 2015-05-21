<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation;

class CoreAnnotations extends \obo\Object {

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $entitiesExplorer
     * @return void
     */
    public static function register(\obo\Services\EntitiesInformation\Explorer $entitiesExplorer) {
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\ColumnName");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\Persistable");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\Many");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\One");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Entity\\PrimaryProperty");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Entity\\RepositoryName");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Entity\\SoftDeletable");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Method\\Run");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\TimeStamp");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\DataType");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\StoreTo");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\Uuid");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\AutoIncrement");
        $entitiesExplorer->registerAnnotation("\\obo\\Annotation\\Property\\Nullable");
    }
}
