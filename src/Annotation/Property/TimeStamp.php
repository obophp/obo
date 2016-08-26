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
    protected $eventsNames = [];

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
        return [self::PARAMETERS_NUMBER_DEFINITION => self::ONE_OR_MORE_PARAMETERS];
    }

    /**
     * @param array $values
     * @return void
     */
    public function process(array $values) {
        parent::process($values);
        $this->propertyInformation->dataType = \obo\obo::$entitiesExplorer->createDataType(\obo\DataType\DateTimeDataType::name(), $this->propertyInformation);
        $this->eventsNames = $values;
    }

    /**
     * @return void
     */
    public function registerEvents() {
        foreach ($this->eventsNames as $eventName) {
            \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => $eventName,
                "actionAnonymousFunction" => function($arguments) {$arguments["entity"]->setValueForPropertyWithName(new \DateTime, $arguments["propertyName"]);},
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));
        }
    }
}
