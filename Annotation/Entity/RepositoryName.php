<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Entity;

class RepositoryName extends \obo\Annotation\Base\Entity {

    /**
     * @var string
     */
    protected $repositoryName = "";

    /**
     * @return string
     */
    public static function name() {
        return "repositoryName";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return array("numberOfParameters" => 1);
    }

    /**
     * @param array $values
     * @return void
     */
    public function process($values) {
        parent::process($values);
        $managerName = $this->entityInformation->managerName;
        if (!$managerName::dataStorage()->existsRepositoryWithName($values[0])) throw new \obo\Exceptions\BadAnnotationException("Repository with name '{$values[0]}' does not exist in datastorage");
        $this->entityInformation->repositoryName = $this->repositoryName = $values[0];
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

}
