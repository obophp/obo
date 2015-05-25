<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Method;

class Run extends \obo\Annotation\Base\Method {
    protected $eventsNames = [];

    /**
     * @return string
     */
    public static function name() {
        return "run";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return ["numberOfParameters" => -1];
    }

    /**
     * @param array $values
     * @return void
     */
    public function process(array $values) {
        parent::process($values);
        $this->eventsNames = $values;
    }

    /**
     * @return void
     */
    public function registerEvents() {
        foreach ($this->eventsNames as $eventName) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => $eventName,
                "actionMessage" => $this->methodName,
            ]));
        }
    }

}
