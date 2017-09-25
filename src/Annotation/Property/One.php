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
     * @var string
     */
    protected $interface = null;

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
                "interface" => false,
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

        $this->targetEntity = \trim($values["targetEntity"], "\\");

        if (\strpos($this->targetEntity, "property:") === 0) {
            $this->targetEntityInProperty = \substr($this->targetEntity, 9);
            if ($this->entityInformation->existInformationForPropertyWithName($this->targetEntityInProperty)) {
                \obo\obo::$entitiesExplorer->createDataType(\obo\DataType\StringDataType::name(), $this->entityInformation->informationForPropertyWithName($this->targetEntityInProperty));
            }
        }

        if (isset($values["cascade"])) $this->cascadeOptions = \preg_split("#, ?#", $values["cascade"]);

        if (isset($values["autoCreate"])) $this->autoCreate = $values["autoCreate"];

        if (isset($values["connectViaProperty"])) {
            $this->connectViaProperty = $values["connectViaProperty"];
            if (isset($values["ownerNameInProperty"])) $this->ownerNameInProperty = $values["ownerNameInProperty"];
            $this->propertyInformation->columnName = "";
            $this->propertyInformation->persistable = false;
        }

        if (isset($values["eager"])) $this->eager = $values["eager"];
        if ($this->eager) $this->entityInformation->eagerConnections[] = $this->propertyInformation->name;

        if (isset($values["interface"])) {
            $this->interface = $values["interface"];
        }
    }

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function validate(\obo\Services\EntitiesInformation\Explorer $explorer) {
        if ($this->targetEntityInProperty === null AND !isset($explorer->entitiesInformationsByEntitiesNames()[$this->targetEntity])) throw new \obo\Exceptions\BadAnnotationException("Relationship 'one', in class '{$this->entityInformation->className}', could not be built. Entity with name '{$this->targetEntity}' could not be connected because it does not exist.");
        if (!\is_bool($this->autoCreate)) throw new \obo\Exceptions\BadAnnotationException("Parameter 'autoCreate' for relationship 'one' must be boolean");
    }

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @return void
     */
    public function finalize(\obo\Services\EntitiesInformation\Explorer $explorer) {
        $this->propertyInformation->relationship = $relationship = new \obo\Relationships\One($this->targetEntityInProperty === null ? $explorer->entitiesInformationsByEntitiesNames()[$this->targetEntity]->className : $this->targetEntity, $this->propertyInformation->name, $this->cascadeOptions);
        $relationship->autoCreate = $this->autoCreate;
        $relationship->ownerNameInProperty = $this->ownerNameInProperty;
        $relationship->connectViaProperty = $this->connectViaProperty;
        $relationship->interface = $this->interface;

        $this->propertyInformation->dataType = \obo\obo::$entitiesExplorer->createDataType(\obo\DataType\EntityDataType::name(), $this->propertyInformation, $this->targetEntityInProperty === null ? ["className" => $this->targetEntity] : []);
    }

    /**
     * @return array
     */
    public function registerEvents() {
        foreach ($this->cascadeOptions as $cascadeOption) {
            if ($cascadeOption == "save") {
                \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => ($this->connectViaProperty) ? "afterSave" : "beforeSave",
                    "actionAnonymousFunction" => function($arguments) {
                        $connectedEntity = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"], false, true, false);
                        if ($connectedEntity instanceof \obo\Entity && !$connectedEntity->isDeleted()) $connectedEntity->save();
                    },
                    "actionArguments" => ["propertyName" => $this->propertyInformation->name],
                ]));
            } elseif ($cascadeOption == "delete") {
                \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
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

        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
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
                        \obo\obo::$eventManager->notifyEventForEntity("beforeConnectToOwner", $entityToBeConnected, ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        $arguments["entity"]->setValueForPropertyWithName($entityToBeConnected, $arguments["propertyName"], !$entityToBeConnected->isBasedInRepository());
                        \obo\obo::$eventManager->notifyEventForEntity("afterConnectToOwner", $entityToBeConnected, ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        \obo\obo::$eventManager->notifyEventForEntity($this->propertyInformation->name . "Connected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "connectedEntity" => $entityToBeConnected]);
                    }
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) \obo\obo::$eventManager->notifyEventForEntity("beforeDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                    \obo\obo::$eventManager->notifyEventForEntity("beforeConnectToOwner", $arguments["propertyValue"]["new"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                } else {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) \obo\obo::$eventManager->notifyEventForEntity("beforeDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

        if ($this->targetEntityInProperty) {

            \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "beforeWrite" . \ucfirst($this->propertyInformation->name),
                "actionAnonymousFunction" => function($arguments) {
                    if (\is_string($arguments["propertyValue"]["new"]) AND \count($parts = explode(":", $arguments["propertyValue"]["new"])) === 2) {
                        $entityName = ltrim($parts[1], "\\");
                        \obo\obo::$entitiesInformation->entityClassNameForEntityWithName($entityName);
                        $arguments["propertyValue"]["new"] = $parts[0];
                        $propertyInformation = $arguments["entity"]->informationForPropertyWithName($this->propertyInformation->name);
                        $interface = \ltrim($this->interface, "\\");

                        if ($this->interface !== null && !($arguments["entity"] instanceof $interface)) {
                            throw new \obo\Exceptions\PropertyAccessException("Entity with class '{$arguments["propertyValue"]["new"]->className()}' isn't allowed for relation defined in entity '{$arguments["entity"]->className()}' and property '{$propertyInformation->name}' because it must implement interface '{$interface}'");
                        }

                        $arguments["entity"]->setValueForPropertyWithName($entityName, $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    }
                },
            ]));

            \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
                "actionAnonymousFunction" => function($arguments) {
                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);

                    if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                        $interface = \ltrim($this->interface, "\\");

                        if ($this->interface !== null && !($arguments["propertyValue"]["new"] instanceof $interface)) {
                            throw new \obo\Exceptions\PropertyAccessException("Entity with class '{$arguments["propertyValue"]["new"]->className()}' isn't allowed for relation defined in entity '{$arguments["entity"]->className()}' and property '{$propertyInformation->name}' because it must implement interface '{$interface}'");
                        }

                        $arguments["entity"]->setValueForPropertyWithName($arguments["propertyValue"]["new"]->entityInformation()->name, $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    } elseif ($arguments["propertyValue"]["new"] === null) {
                        $arguments["entity"]->setValueForPropertyWithName(null, $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName);
                    }

                },
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));
        }

        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "afterChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                if ($arguments["propertyValue"]["new"] instanceof \obo\Entity) {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) {
                        \obo\obo::$eventManager->notifyEventForEntity("afterDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        \obo\obo::$eventManager->notifyEventForEntity($this->propertyInformation->name . "Disconnected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "disconnectedEntity" => $arguments["propertyValue"]["old"]]);
                    }
                    \obo\obo::$eventManager->notifyEventForEntity("afterConnectToOwner", $arguments["propertyValue"]["new"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                    \obo\obo::$eventManager->notifyEventForEntity($this->propertyInformation->name . "Connected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "connectedEntity" => $arguments["propertyValue"]["new"]]);
                } else {
                    if ($arguments["propertyValue"]["old"] instanceof \obo\Entity) {
                        \obo\obo::$eventManager->notifyEventForEntity("afterDisconnectFromOwner", $arguments["propertyValue"]["old"], ["owner" => $arguments["entity"], "columnName" => $propertyInformation->columnName]);
                        \obo\obo::$eventManager->notifyEventForEntity($this->propertyInformation->name . "Disconnected", $arguments["entity"], ["columnName" => $propertyInformation->columnName, "disconnectedEntity" => $arguments["propertyValue"]["old"]]);
                    }
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

        if ($this->connectViaProperty !== null) {
            \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterConnectToOwner",
                "actionAnonymousFunction" => function($arguments) {
                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                    if ($arguments["owner"] instanceof $propertyInformation->relationship->entityClassNameToBeConnected
                            && $arguments["columnName"] === $propertyInformation->relationship->connectViaProperty) {
                        if ($arguments["entity"]->isEditable()) {
                            $arguments["entity"]->setValueForPropertyWithName($arguments["owner"], $propertyInformation->relationship->ownerPropertyName);
                        }
                    }
                },
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));

            \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                "onClassWithName" => $this->entityInformation->className,
                "name" => "afterDisconnectFromOwner",
                "actionAnonymousFunction" => function($arguments) {
                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                    if ($arguments["owner"] instanceof $propertyInformation->relationship->entityClassNameToBeConnected
                            && $arguments["columnName"] === $propertyInformation->relationship->connectViaProperty) {
                        if ($arguments["entity"]->isEditable()) {
                            $arguments["entity"]->setValueForPropertyWithName(null, $propertyInformation->relationship->ownerPropertyName);
                        }
                    }
                },
                "actionArguments" => ["propertyName" => $this->propertyInformation->name],
            ]));
        }
    }

}
