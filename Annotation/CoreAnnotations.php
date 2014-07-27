<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation;

class CoreAnnotations extends \obo\Object {

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $entitiesExplorer
     * @return void
     */
    public static function register(\obo\Services\EntitiesInformation\Explorer $entitiesExplorer) {
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Property\ColumnName");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Property\Many");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Property\One");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Entity\PrimaryProperty");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Entity\RepositoryName");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Entity\SoftDeletable");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Method\Run");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Property\TimeStamp");
        $entitiesExplorer->registerAnnotation("\obo\Annotation\Property\DataType");
    }
}