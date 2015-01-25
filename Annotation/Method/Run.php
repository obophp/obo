<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Method;

class Run extends \obo\Annotation\Base\Method {
    protected $eventsNames = array();

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
        return array("numberOfParameters" => -1);
    }

    /**
     * @param array $values
     * @return void
     */
    public function process($values) {
        parent::process($values);
        $this->eventsNames = $values;
    }

    /**
     * @return void
     */
    public function registerEvents() {
        foreach ($this->eventsNames as $eventName) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                "onClassWithName" => $this->entityInformation->className,
                "name" => $eventName,
                "actionMessage" => $this->methodName,
            )));
        }
    }

}
