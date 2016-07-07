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
    protected $targetEntityInProperty = null;

    /**
     * @var string
     */
    protected $connectViaProperty = null;

    /**
     * @var string
     */
    protected $ownerNameInProperty = null;
    /**
     * @var array
     */
    protected $cascadeOptions = [];

    /**
     * @var bool
     */
    protected $autoCreate = false;

    /**
     * @var bool
     */
    protected $eager = false;

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
        return [
            self::PARAMETERS_DEFINITION => [
                "targetEntity" => true,
                "connectViaProperty" => false,
                "ownerNameInProperty" => false,
                "cascade" => false,
                "autoCreate" => false,
                "eager" => false,
            ]
        ];
    }

    /**
     * @param array $values
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function process(array $values) {
        parent::process($values);

        $this->targetEntity = $values["targetEntity"];

        if (\strpos($this->targetEntity, "property:") === 0) {
            $this->targetEntityInProperty = \substr($this->targetEntity, 9);
            if ($this->entityInformation->existInformationForPropertyWithName($this->targetEntityInProperty)) {
                \obo\Services::serviceWithName(\obo\obo::ENTITIES_EXPLORER)->createDataType(\obo\DataType\StringDataType::name(), $this->entityInformation->informationForPropertyWithName($this->targetEntityInProperty));
            }
        }

        if (!$this->targetEntityInProperty) {
            if (!\class_exists($this->targetEntity)) throw new \obo\Exceptions\BadAnnotationException("Relationship 'one' could not be built. Entity '{$this->targetEntity}' could not be connected because it does not exist.");
            if (!\is_subclass_of($this->targetEntity, \obo\Entity::className())) throw new \obo\Exceptions\BadAnnotationException("Target entity must extend " . \obo\Entity::className());
            $targetEntity = $this->targetEntity;
            $this->targetEntity = $targetEntity::className();
        }

        if (isset($values["cascade"])) $this->cascadeOptions = \preg_split("#, ?#", $values["cascade"]);

        if (isset($values["autoCreate"])) {
            if (!\is_bool($values["autoCreate"])) throw new \obo\Exceptions\BadAnnotationException("Parameter 'autoCreate' for relationship 'one' must be boolean");
            $this->autoCreate = $values["autoCreate"];
        }

        if (isset($values["connectViaProperty"])) {
            $this->connectViaProperty = $values["connectViaProperty"];
            if (isset($values["ownerNameInProperty"])) $this->ownerNameInProperty = $values["ownerNameInProperty"];
            $this->propertyInformation->columnName = "";
            $this->propertyInformation->persistable = false;
        }


        if (isset($values["eager"])) $this->eager = $values["eager"];
        if ($this->eager) $this->entityInformation->eagerConnections[] = $this->propertyInformation->name;

        $this->propertyInformation->relationship = new \obo\Relationships\One($this->targetEntity, $this->propertyInformation->name, $this->cascadeOptions);
        $this->propertyInformation->relationship->autoCreate = $this->autoCreate;
        $this->propertyInformation->relationship->ownerNameInProperty = $this->ownerNameInProperty;
        $this->propertyInformation->relationship->connectViaProperty = $this->connectViaProperty;
        $this->propertyInformation->dataType = \obo\Services::serviceWithName(\obo\obo::ENTITIES_EXPLORER)->createDataType(\obo\DataType\EntityDataType::name(), $this->propertyInformation, $this->targetEntityInProperty === null ? ["className" => $this->targetEntity] : []);
    }

    /**
     * @return array
     */
    public function registerEvents() {

        foreach ($this->cascadeOptions as $cascadeOption) {
            if ($cascadeOption == "save") {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => ($this->connectViaProperty) ? "afterSave" : "beforeSave",
                    "actionAnonymousFunction" => function($arguments) {
                        $connectedEntity = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"], false, true, false);
                        if ($connectedEntity instanceof \obo\Entity && !$connectedEntity->isDeleted()) $connectedEntity->save();
                    },
                    "actionArguments" => ["propertyName" => $this->propertyInformation->name],
                ]));
            } elseif ($cascadeOption == "delete") {
                \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => "beforeDelete",
                    "actionAnonymousFunction" => function($arguments) {
                        if ($arguments["entity"]->valueForPropertyWithName($arguments["propertyName"], false, true, false) !== null) {
                            if (($connectedEntity = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"])) instanceof \obo\Entity) $connectedEntity->delete($arguments["removeEntity"]);
                        }
                    },
                    "actionArguments" => ["propertyName" => $this->propertyInformation->name, "removeEntity" => true],
                ]));
            }
        }

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeRead" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                if ($arguments["entityAsPrimaryPropertyValue"] AND $this->connectViaProperty === null) return;

                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                $currentPropertyValue = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"]);

                if (!$currentPropertyValue instanceof \obo\Entity) {
                    if ($this->connectViaProperty === null ) {
                        $entityToBeConnected = $propertyInformation->relationship->relationshipForOwnerAndPropertyValue($arguments["entity"], $currentPropertyValue, $arguments["autoCreate"]);
                    } else {
                        $foreignKey = [$this->connectViaProperty];
                        if ($this->ownerNameInProperty !== null) $foreignKey[] = $this->ownerNameInProperty;
                        $entityToBeConnected = $propertyInformation->relationship->entityForOwnerForeignKey($arguments["entity"], $foreignKey, $arguments["autoCreate"]);
                    }

                    if ($entityToBeConnected === null) {
                        $arguments["entity"]->setValueForPropertyWithName(null, $arguments["propertyName"]);
                    } else {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeConnectToOwner", $entityToBeConnected, ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        $arguments["entity"]->setValueForPropertyWithName($entityToBeConnected, $arguments["propertyName"], !$entityToBeConnected->isBasedInRepository());
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterConnectToOwner", $entityToBeConnected, ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Connected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "connectedEntity" => $entityToBeConnected]);
                    }
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {

                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeConnectToOwner", $arguments["propertyValue"]["new"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                } else {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("beforeDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

        if ($this->targetEntityInProperty) {

            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "beforeWrite" . \ucfirst($this->propertyInformation->name),
                "actionAnonymousFunction" => function($arguments) {
                    if (\is_string($arguments["propertyValue"]["new"]) AND \count($parts = explode(":", $arguments["propertyValue"]["new"])) === 2) {
                        if (!\class_exists($parts[1])) throw new \obo\Exceptions\Exception("Can not set property with dynamic relationship 'one', class '{$parts[1]}' does not exist");
                        $arguments["propertyValue"]["new"] = $parts[0];
                        $propertyInformation = $arguments["entity"]->informationForPropertyWithName($this->propertyInformation->name);
                        $arguments["entity"]->setValueForPropertyWithName(ltrim($parts[1], "\\"), $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    }
                },
            ]));

            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
                "actionAnonymousFunction" => function($arguments) {

                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);

                    if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                        $arguments["entity"]->setValueForPropertyWithName($arguments["propertyValue"]["new"]->className(), $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    } elseif ($arguments["propertyValue"]["new"] === null) {
                        $arguments["entity"]->setValueForPropertyWithName(null, $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    }

                },
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));
        }

        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {

                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Disconnected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "disconnectedEntity" => $arguments["propertyValue"]["old"]]);
                    }
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterConnectToOwner", $arguments["propertyValue"]["new"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                    \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Connected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "connectedEntity" => $arguments["propertyValue"]["new"]]);
                } else {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity("afterDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->notifyEventForEntity($this->propertyInformation->name . "Disconnected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "disconnectedEntity" => $arguments["propertyValue"]["old"]]);
                    }
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

        if ($this->connectViaProperty !== null) {
           \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterConnectToOwner",
                "actionAnonymousFunction" => function($arguments) {
                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                    if ($arguments["owner"] instanceof $propertyInformation->relationship->entityClassNameToBeConnected
                            && $arguments["columnName"] === $propertyInformation->relationship->connectViaProperty) {
                        $arguments["entity"]->setValueForPropertyWithName($arguments["owner"], $propertyInformation->relationship->ownerPropertyName);
                    }
                },
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));

            \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterDisconnectFromOwner",
                "actionAnonymousFunction" => function($arguments) {
                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                    if ($arguments["owner"] instanceof $propertyInformation->relationship->entityClassNameToBeConnected
                            && $arguments["columnName"] === $propertyInformation->relationship->connectViaProperty) {
                        $arguments["entity"]->setValueForPropertyWithName(null, $propertyInformation->relationship->ownerPropertyName);
                    }
                },
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));
        }
    }
}
