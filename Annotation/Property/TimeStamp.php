<?php

/**

 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class TimeStamp extends \obo\Annotation\Base\Property {

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
    public function proccess($values) {
        parent::proccess($values);
        $this->propertyInformation->dataType = new \obo\DataType\DateTime($this->propertyInformation);
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