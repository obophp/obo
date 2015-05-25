<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
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
        return ["numberOfParameters" => 1];
    }

    /**
     * @param array $values
     * @return void
     */
    public function process(array $values) {
        parent::process($values);
        $managerName = $this->entityInformation->managerName;
        $this->entityInformation->repositoryName = $this->repositoryName = $values[0];
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

}
