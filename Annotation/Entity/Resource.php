<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Roman Pavlík
 * @copyright (c) 2011 - 2014 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Entity;

class Resource extends \obo\Annotation\Base\Entity {

    protected $resources = [];

    /**
     * @return string
     */
    public static function name() {
        return "resource";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return ["numberOfParameters" => "-1"];
    }

    /**
     * @param array $values
     */
    public function proccess($values) {
        parent::proccess($values);
        $this->resources = array_unique($values);
    }

    public function collectChanges($arguments) {
        /** @var \obo\Entity $entity */
        $entity = $arguments["entity"];
        /** @var \obo\Services\ChangesCollector\ChangesCollector $changesCollector */
        $changesCollector = \obo\Services::serviceWithName(\obo\obo::CHANGES_COLLECTOR);
        $changesCollector->collectChange($this->resources, $entity);
    }

    public function registerEvents() {
        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeSave",
            "actionAnonymousFunction" => function($arguments) {
                $this->collectChanges($arguments);
            },
            "actionArguments" => [],
        ]));
    }

}