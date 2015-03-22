<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class One extends \obo\Annotation\Base\Property {

    /**
     * @var string
     */
    protected $targetEntity = null;

    /**
     * @var string
     */
    protected $targetEntityInProperty = false;

    /**
     * @var array
     */
    protected $cascadeOptions = array();

    /**
     * @var boolean
     */
    protected $autoCreate = false;

    /**
     * @return string
     */
    public static function name() {
        return "one";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return array("parameters" => array("targetEntity" => true, "cascade" => false, "autoCreate" => false));
    }

    /**
     * @param array $values
     * @throws \obo\Exceptions\BadAnnotationException
     * @return void
     */
    public function process($values) {
        parent::process($values);

        $this->targetEntity = $values["targetEntity"];

        if (\strpos($this->targetEntity, "property:") === 0) {
            $this->targetEntityInProperty = \substr($this->targetEntity, 9);
        }

        if (!$this->targetEntityInProperty AND !\class_exists($this->targetEntity)) throw new \obo\Exceptions\BadAnnotationException("Relationship 'one' could not be built. Entity '{$this->targetEntity}' could not be connected because it does not exist.");

        if (isset($values["cascade"])) $this->cascadeOptions = \preg_split("#, ?#", $values["cascade"]);

        if (isset($values["autoCreate"])) {
            if (!\is_bool($values["autoCreate"])) throw new \obo\Exceptions\BadAnnotationException("Parameter 'autoCreate' for relationship 'one' must be boolean");
            $this->autoCreate = $values["autoCreate"];
        }

        $this->propertyInformation->relationship = new \obo\Relationships\One($this->targetEntity, $this->propertyInformation->name);
        $this->propertyInformation->relationship->autoCreate = $this->autoCreate;
    }

    /**
     * @return array
     */
    public function registerEvents() {

        foreach ($this->cascadeOptions as $cascadeOption) {
            if ($cascadeOption == "save") {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => "beforeSave",
                    "actionAnonymousFunction" => function($arguments) {
                        $connectedEntity = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"], false, false);
                        if ($connectedEntity instanceof \obo\Entity && !$connectedEntity->isDeleted()) $connectedEntity->save();
                    },
                    "actionArguments" => array("propertyName" => $this->propertyInformation->name),
                )));
            } elseif ($cascadeOption == "delete") {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => "beforeDelete",
                    "actionAnonymousFunction" => function($arguments) {
                        $connectedEntity = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"]);
                        if ($connectedEntity instanceof \obo\Entity) $connectedEntity->delete($arguments["removeEntity"]);
                    },
                    "actionArguments" => array("propertyName" => $this->propertyInformation->name, "removeEntity" => true),
                )));
            }
        }

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeRead" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                if ($arguments["entityAsPrimaryPropertyValue"]) return;

                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                $currentPropertyValue = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"]);

                if (!\is_object($currentPropertyValue)) {
                    $entityToBeConnected = $propertyInformation->relationship->relationshipForOwnerAndPropertyValue($arguments["entity"], $currentPropertyValue);
                    if (!\is_null($entityToBeConnected)) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeConnectToOwner", $entityToBeConnected, array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                    $arguments["entity"]->setValueForPropertyWithName($entityToBeConnected, $arguments["propertyName"]);
                    if (!\is_null($entityToBeConnected)) {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterConnectToOwner", $entityToBeConnected, array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Connected", $arguments["entity"], array("columnName" => $propertyInformation->columnName, "connectedEntity" => $entityToBeConnected));
                    }
                }

            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name),
        )));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {

                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDisconnectFromOwner", $arguments["propertyValue"]["old"], array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeConnectToOwner", $arguments["propertyValue"]["new"], array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                } else {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDisconnectFromOwner", $arguments["propertyValue"]["old"], array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                }

            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name),
        )));

        if ($this->targetEntityInProperty) {
            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
                "actionAnonymousFunction" => function($arguments) {

                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);

                    if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                        $arguments["entity"]->setValueForPropertyWithName($arguments["propertyValue"]["new"]->className(), $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    } else {
                        $arguments["entity"]->setValueForPropertyWithName(null, $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    }

                },
                "actionArguments" => array("propertyName" => $this->propertyInformation->name),
            )));
        }

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
            "onClassWithName" => $this->entityInformation->className,
            "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {

                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDisconnectFromOwner", $arguments["propertyValue"]["old"], array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Disconnected", $arguments["entity"], array("columnName" => $propertyInformation->columnName, "disconnectedEntity" => $arguments["propertyValue"]["old"]));
                    }
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterConnectToOwner", $arguments["propertyValue"]["new"], array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Connected", $arguments["entity"], array("columnName" => $propertyInformation->columnName, "connectedEntity" => $arguments["propertyValue"]["new"]));
                } else {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDisconnectFromOwner", $arguments["propertyValue"]["old"], array("owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName));
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Disconnected", $arguments["entity"], array("columnName" => $propertyInformation->columnName, "disconnectedEntity" => $arguments["propertyValue"]["old"]));
                    }
                }

            },
            "actionArguments" => array("propertyName" => $this->propertyInformation->name),
        )));
    }
}
