<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class TimeStamp extends \obo\Annotation\Base\Property {

    /**
     * @var array
     */
    protected $eventsNames = array();

    /**
     * @return string
     */
    public static function name() {
        return "timeStamp";
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
        $this->propertyInformation->dataType = \obo\DataType\Factory::createDataTypeDateTime($this->propertyInformation);
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
                "actionAnonymousFunction" => function($arguments) {$arguments["entity"]->setValueForPropertyWithName(new \DateTime, $arguments["propertyName"]);},
                "actionArguments" => array("propertyName" => $this->propertyInformation->name),
            )));
        }
    }
}
