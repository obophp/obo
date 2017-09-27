<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

/**
 * @method void on($eventName, callable $callback)
 */
abstract class Entity  extends \obo\BaseObject {

    /**
     * @var \obo\Carriers\EntityInformationCarrier[]
     */
    private static $entitiesInformations = [];

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var bool
     */
    private $basedInRepository = null;

    /**
     * @var bool
     */
    private $deleted = null;

    /**
     * @var string
     */
    private $entityIdentificationKey = null;

    /**
     * @var \obo\EntityProperties
     */
    private $propertiesObject = null;

    /**
     * @var array
     */
    private $propertiesChanges = [];

    /**
     * @var bool
     */
    private $savingInProgress = false;

    /**
     * @var bool
     */
    private $deletingInProgress = false;

    /**
     * @var \obo\Interfaces\IDataStorage
     */
    private $dataStorage = null;

    /**
     * @param string $propertyName
     * @return mixed
     */
    public function &__get($propertyName) {
        return $this->valueForPropertyWithName($propertyName);
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @return mixed
     */
    public function __set($propertyName, $value) {
        return $this->setValueForPropertyWithName($value, $propertyName);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return array
     */
    public function __call($name, $arguments) {
        if ($name === "on") {
            return $this->dynamicOn($arguments[0], $arguments[1]);
        }

        throw new \obo\Exceptions\MethodNotFoundException("Can't call to the method with name '{$name}', does not exist in entity '" . $this->className() . "'");
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return array
     */
    public static function __callStatic($name, $arguments) {
        if ($name === "on") {
            return static::staticOn($arguments[0], $arguments[1]);
        }

        throw new \obo\Exceptions\MethodNotFoundException("Can't call to the method with name '{$name}', does not exist in entity '" . static::className() . "'");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return $this->hasPropertyWithName($name);
    }

    /**
     * @throws \obo\Exceptions\Exception
     */
    public function __clone() {
        throw new \obo\Exceptions\Exception('Obo entity is not cloneable');
    }

    /**
     * @throws \obo\Exceptions\Exception
     */
    public function __wakeup() {
        throw new \obo\Exceptions\Exception('Obo entity is not unserializable');
    }

    /**
     * @throws \obo\Exceptions\Exception
     */
    public function __sleep() {
        throw new \obo\Exceptions\Exception('Obo entity is not serializable');
    }

    /**
     * @return \obo\Interfaces\IDataStorage
     */
    public function dataStorage() {
        return $this->dataStorage;
    }

    /**
     * @param \obo\Interfaces\IDataStorage $dataStorage
     * @return \obo\Interfaces\IDataStorage
     * @throws \obo\Exceptions\Exception
     */
    public function setDataStorage(\obo\Interfaces\IDataStorage $dataStorage) {
        if ($this->isInitialized()) throw new \obo\Exceptions\Exception("You can't change datastorage, entity has been initialized");
        return $this->dataStorage = $dataStorage;
    }

    /**
     * @return string
     */
    public function repositoryAddress() {
        return $this->dataStorage->repositoryAddressForEntity($this);
    }

    /**
     * @return \obo\EntityProperties
     */
    private function propertiesObject() {
        if ($this->propertiesObject !== null) return $this->propertiesObject;
        $propertiesClassName = $this->entityInformation()->propertiesClassName;
        return $this->propertiesObject = new $propertiesClassName($this);;
    }

    /**
     * @return \obo\Carriers\EntityInformationCarrier
     */
    public static function entityInformation() {
        $selfClassName = self::className();
        if (!isset(self::$entitiesInformations[$selfClassName])) self::$entitiesInformations[$selfClassName] = \obo\obo::$entitiesInformation->informationForEntityWithClassName($selfClassName);
        return self::$entitiesInformations[$selfClassName];
    }

    /**
     * @param string $propertyName
     * @return \obo\Carriers\PropertyInformationCarrier
     */
    public static function informationForPropertyWithName($propertyName) {
        return self::entityInformation()->informationForPropertyWithName($propertyName);
    }

    /**
     * @return \obo\Carriers\PropertyInformationCarrier[]
     */
    public static function propertiesInformation() {
        return self::entityInformation()->propertiesInformation;
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public static function hasPropertyWithName($propertyName) {
        return self::entityInformation()->existInformationForPropertyWithName($propertyName);
    }

    /**
     * @return array
     */
    public function propertiesChanges() {
        return $this->propertiesChanges;
    }

    /**
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function markUnpersistedPropertiesAsPersisted() {
        $backTrace = \debug_backtrace(null, 2);

        if (($backTrace[1]["class"] === "obo\\EntityManager" AND $backTrace[1]["function"] === "saveEntity") OR ($backTrace[1]["class"] === "obo\\Entity" AND $backTrace[1]["function"] === "discardNonPersistedChanges")) {
            foreach ($this->propertiesChanges as $propertyName => $changeStatus) {
                $this->propertiesChanges[$propertyName]["persisted"] = true;
                $this->propertiesChanges[$propertyName]["lastPersistedValue"] = $this->propertiesChanges[$propertyName]["newValue"] instanceof \obo\Entity ? $this->propertiesChanges[$propertyName]["newValue"]->primaryPropertyValue() : $this->propertiesChanges[$propertyName]["newValue"];
            }
        } else {
            throw new \obo\Exceptions\Exception("MarkUnpersistedPropertiesAsPersisted method can be only called from the obo framework");
        }
    }

    /**
     * @return mixed
     */
    public function primaryPropertyName() {
        return $this->entityInformation()->primaryPropertyName;
    }

    /**
     * @return mixed
     */
    public function primaryPropertyValue() {
        return $this->valueForPropertyWithName($this->entityInformation()->primaryPropertyName);
    }

    /**
     * @param string $propertyName
     * @param bool $entityAsPrimaryPropertyValue
     * @param bool $triggerEvents
     * @param bool $autoCreate
     * @return mixed
     * @throws Exceptions\PropertyNotFoundException
     * @throws Exceptions\ServicesException
     */
    public function &valueForPropertyWithName($propertyName, $entityAsPrimaryPropertyValue = false, $triggerEvents = true, $autoCreate = true) {
        if (!$this->hasPropertyWithName($propertyName)) {

            if ($pos = \strpos($propertyName, "_")) {
                if (($subPropertyValue = $this->valueForPropertyWithName(\substr($propertyName, 0, $pos))) instanceof \obo\Entity) {
                    return $subPropertyValue->valueForPropertyWithName(substr($propertyName, $pos + 1), $entityAsPrimaryPropertyValue, $triggerEvents);
                } elseif ($subPropertyValue instanceof \obo\Relationships\EntitiesCollection) {
                    $propertyName = substr($propertyName, $pos + 1);

                    if (($pos = \strpos($propertyName, "_")) === 0) {
                        $propertyName = \ltrim($propertyName, "_");
                        $position = "___" . \substr($propertyName, 0, \strpos($propertyName, "_"));
                        $propertyName = \substr($propertyName, \strpos($propertyName, "_") + 1);

                        if ($subPropertyValue->__isset($position)) {
                            $entity = $subPropertyValue->variableForName($position);
                        } else {
                            $entityClassNameTobeConnected = $subPropertyValue->getEntitiesClassName();
                            $entityManager = $entityClassNameTobeConnected::entityInformation()->managerName;
                            $entity = $entityManager::entity([]);
                        }

                        return $entity->valueForPropertyWithName($propertyName);
                    } elseif ($pos) {
                        return $subPropertyValue->variableForName(\substr($propertyName, 0, $pos))->valueForPropertyWithName(substr($propertyName, $pos + 1));
                    }
                }
            }

            throw new \obo\Exceptions\PropertyNotFoundException("Property with name '{$propertyName}' can't be read, does not exist in entity '" . $this->className() . "'");
        }

        $propertyInformation = $this->informationForPropertyWithName($propertyName);

        if ($triggerEvents) {
            \obo\obo::$eventManager->notifyEventForEntity("beforeRead" . \ucfirst($propertyName), $this, ["propertyName" => $propertyName, "entityAsPrimaryPropertyValue" => $entityAsPrimaryPropertyValue, "autoCreate" => $autoCreate]);
        }

        if ($propertyInformation->getterName === "") {
            $value = $this->propertiesObject()->$propertyName;
        } else {
            $value = $this->propertiesObject()->{$propertyInformation->getterName}();
        }

        if ($triggerEvents) {
            \obo\obo::$eventManager->notifyEventForEntity("afterRead" . \ucfirst($propertyName), $this, ["propertyName" => $propertyName, "entityAsPrimaryPropertyValue" => $entityAsPrimaryPropertyValue]);
        }

        if ($entityAsPrimaryPropertyValue === true) {
            if ($propertyInformation->hasRelationshipOne() && $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName && $value) {
                $value = ($value instanceof \obo\Entity) ? $value->entityIdentificationKey() : \obo\obo::$identityMapper->entityIdentificationKeyForPrimaryPropertyValueAndClassName($value, $this->valueForPropertyWithName($propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName));
            } elseif ($value instanceof \obo\Entity) {
                $value = $value->primaryPropertyValue();
            }
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param string $propertyName
     * @param bool $triggerEvents
     * @return mixed
     * @throws \obo\Exceptions\Exception
     * @throws \obo\Exceptions\PropertyNotFoundException
     * @throws \obo\Exceptions\ServicesException
     */
    public function &setValueForPropertyWithName($value, $propertyName, $triggerEvents = true) {
        if (!$this->hasPropertyWithName($propertyName)) {
            if ($pos = \strpos($propertyName, "_")) {

                if (($subPropertyValue = $this->valueForPropertyWithName(\substr($propertyName, 0, $pos))) instanceof \obo\Entity) {
                    return $subPropertyValue->setValueForPropertyWithName($value, substr($propertyName, $pos + 1));
                } elseif ($subPropertyValue instanceof \obo\Relationships\EntitiesCollection) {
                    $propertyName = substr($propertyName, $pos + 1);
                    $pos = \strpos($propertyName, "_");
                    return $subPropertyValue->variableForName(\substr($propertyName, 0, $pos))->setValueForPropertyWithName($value, \substr($propertyName, $pos + 1));
                }
            }

            throw new \obo\Exceptions\PropertyNotFoundException("Can't write to the property with name '{$propertyName}', does not exist in entity '".$this->className()."'");
        }

        $propertyInformation = $this->informationForPropertyWithName($propertyName);

        if ($dataType = $propertyInformation->dataType) {
            $value = $dataType->sanitizeValue($value);
            if ($value === null AND !$propertyInformation->nullable) throw new \obo\Exceptions\Exception("Property '{$propertyName}' in entity '" . $this->entityInformation()->className . "' cannot be null.  Consider using obo-nullable annotation.");
            $dataType->validate($value);
        }

        if (\obo\obo::$eventManager->isRegisteredEntity($this) AND $triggerEvents) {
            $change = null;
            $oldValue = $this->valueForPropertyWithName($propertyName, false, true, false);

            \obo\obo::$eventManager->notifyEventForEntity("beforeWrite" . \ucfirst($propertyName), $this, ["propertyName" => $propertyName, "propertyValue" => ["old" => $oldValue, "new" => &$value]]);

            if ($propertyInformation->relationship !== null OR (\is_object($value) AND ($value instanceof \obo\Entity OR ($value instanceof \obo\Relationships\EntitiesCollection)))) {
                if (\is_scalar($value)) {
                    if ($propertyInformation->relationship instanceof \obo\Relationships\One) {
                        if (!$targetEntity = $this->informationForPropertyWithName($propertyName)->relationship->entityClassNameToBeConnected) {
                            $targetEntity = ($propertyWithTargetEntityName = $this->valueForPropertyWithName($this->informationForPropertyWithName($propertyName)->relationship->entityClassNameToBeConnectedInPropertyWithName)) ? \obo\obo::$entitiesInformation->entityClassNameForEntityWithName($propertyWithTargetEntityName) : null;
                        }

                        if ($targetEntity) {
                            $value = $targetEntity::informationForPropertyWithName($targetEntity::entityInformation()->primaryPropertyName)->dataType->sanitizeValue($value);
                        }

                        if ($oldValue instanceof \obo\Entity) {
                            $change = $value !== $oldValue->primaryPropertyValue();
                        }
                    }
                } elseif ($value instanceof \obo\Entity) {
                    if ($oldValue instanceof \obo\Entity) {
                        $change = $value->primaryPropertyValue() !== $oldValue->primaryPropertyValue();
                    } else {
                        $change = $value->primaryPropertyValue() !== $oldValue;
                    }
                }
            }

            if ($change === null) $change = $oldValue !== $value;

            if ($change) {
                \obo\obo::$eventManager->notifyEventForEntity("beforeChange", $this, ["propertyName" => $propertyName, "propertyValue" => ["old" => $oldValue, "new" => $value]]);
                \obo\obo::$eventManager->notifyEventForEntity("beforeChange" . \ucfirst($propertyName), $this, ["propertyName" => $propertyName, "propertyValue" => ["old" => $oldValue, "new" => $value]]);
            }
        }

        if ($propertyInformation->setterName === "") {
            $this->propertiesObject()->$propertyName = $value;
        } else {
            $this->propertiesObject()->{$propertyInformation->setterName}($value);
        }

        if (\obo\obo::$eventManager->isRegisteredEntity($this) AND $triggerEvents) {
            if ($change) {
                if (isset($this->propertiesChanges[$propertyName])) {
                    $compareValue = $value;

                    if ($compareValue instanceof \obo\Entity) {
                        $compareValue = $compareValue->valueForPropertyWithName($compareValue->entityInformation()->primaryPropertyName);
                    }

                    if ($this->propertiesChanges[$propertyName]["originalValue"] === $this->propertiesChanges[$propertyName]["lastPersistedValue"] AND $this->propertiesChanges[$propertyName]["originalValue"] === $compareValue) {
                        unset($this->propertiesChanges[$propertyName]);
                    } else {
                        $this->propertiesChanges[$propertyName]["newValue"] = $value;
                        $this->propertiesChanges[$propertyName]["persisted"] = false;
                    }
                } else {
                    $this->propertiesChanges[$propertyName] = ["originalValue" => $oldValue, "lastPersistedValue" => $oldValue, "newValue" => $value, "persisted" => false];
                }
            }

            \obo\obo::$eventManager->notifyEventForEntity("afterWrite" . \ucfirst($propertyName), $this, ["propertyName" => $propertyName, "propertyValue" => ["old" => $oldValue, "new" => $value]]);

            if ($change) {
                \obo\obo::$eventManager->notifyEventForEntity("afterChange", $this, ["propertyName" => $propertyName, "propertyValue" => ["old" => $oldValue, "new" => $value]]);
                \obo\obo::$eventManager->notifyEventForEntity("afterChange" . \ucfirst($propertyName), $this, ["propertyName" => $propertyName, "propertyValue" => ["old" => $oldValue, "new" => $value]]);
            }
        }

        return $value;
    }

    /**
     * @param array | \Iterator | null $onlyFromList
     * @param bool $entityAsPrimaryPropertyValue
     * @return array
     */
    public function propertiesAsArray($onlyFromList = null, $entityAsPrimaryPropertyValue = true) {
        $data = [];

        if ($onlyFromList !== null) {
            $propertiesNames = \array_keys((array) $onlyFromList);
        } else {
            $propertiesNames = \array_keys((array) $this->propertiesInformation());
        }

        foreach ($propertiesNames as $propertyName) {
            try {
                $data[$propertyName] = $this->valueForPropertyWithName($propertyName, $entityAsPrimaryPropertyValue);
            } catch (\obo\Exceptions\PropertyNotFoundException $exc) {

            }
        }

        return $data;
    }

    /**
     * @param array | \Iterator $data
     * @return void
     */
    public function changeValuesPropertiesFromArray($data) {
        foreach ($data as $propertyName => $value ) {
            $this->setValueForPropertyWithName($value, $propertyName);
        }
    }

    /**
     * @param array $data
     * @param bool $overwriteEntities
     */
    public function setValuesPropertiesFromArray($data, $overwriteEntities = true) {
        $entityProperties = [];
        $entitiesProperties = [];
        foreach ($data as $propertyName => $value) {
            if ($this->hasPropertyWithName($propertyName) OR \strpos($propertyName, "_") === false) {
                $entityProperties[$propertyName] = $value;
            } else {
                $parts = \explode ("_", $propertyName, 2);

                if (isset($entityProperties[$parts[0]]) AND !\is_array($entityProperties[$parts[0]])) {
                    if (!$targetEntity = $this->informationForPropertyWithName($parts[0])->relationship->entityClassNameToBeConnected) {
                        if (isset($entitiesProperties[$this->informationForPropertyWithName($parts[0])->relationship->entityClassNameToBeConnectedInPropertyWithName])) {
                            $targetEntity = $entitiesProperties[$this->informationForPropertyWithName($parts[0])->relationship->entityClassNameToBeConnectedInPropertyWithName];
                        } else {
                            $targetEntity = $this->valueForPropertyWithName($this->informationForPropertyWithName($parts[0])->relationship->entityClassNameToBeConnectedInPropertyWithName);
                        }
                    }
                    $entitiesProperties[$parts[0]][$targetEntity::entityInformation()->primaryPropertyName] = $entityProperties[$parts[0]];
                }

                $entitiesProperties[$parts[0]] = (isset($entitiesProperties[$parts[0]]) AND is_array($entitiesProperties[$parts[0]])) ? $entitiesProperties[$parts[0]] + [$parts[1] => $value] : [$parts[1] => $value];
            }
        }

        $formattedData = \array_merge($entityProperties, $entitiesProperties);

        foreach ($formattedData as $propertyName => $value) {
            if (\is_array($value)) {
                if (($relationship = $this->informationForPropertyWithName($propertyName)->relationship) instanceof \obo\Relationships\One) {

                    if (!$targetEntity = $this->informationForPropertyWithName($propertyName)->relationship->entityClassNameToBeConnected) {
                        if (isset($formattedData[$relationship->entityClassNameToBeConnectedInPropertyWithName])) {
                            $targetEntityName = $formattedData[$relationship->entityClassNameToBeConnectedInPropertyWithName];
                        } else {
                            $targetEntityName = $this->valueForPropertyWithName($relationship->entityClassNameToBeConnectedInPropertyWithName);
                        }

                        $targetEntity = \obo\obo::$entitiesInformation->entityClassNameForEntityWithName($targetEntityName);
                    }

                    $manager = $targetEntity::entityInformation()->managerName;

                    if ($connectViaProperty = $relationship->connectViaProperty) {
                        $value[$connectViaProperty] = $this;
                    }

                    if ($ownerNameInProperty = $relationship->ownerNameInProperty) {
                        $value[$ownerNameInProperty] = $this->entityInformation()->name;
                    }

                    if (isset($value[$targetEntity::entityInformation()->primaryPropertyName]) OR !($entity = $this->valueForPropertyWithName($propertyName)) instanceof \obo\Entity) {
                        $entity = $manager::entityFromArray($value, false, $overwriteEntities);
                        $this->setValueForPropertyWithName($entity, $propertyName);
                    } else {
                        $entity->setValuesPropertiesFromArray($value, $overwriteEntities);
                    }

                } elseif ($this->informationForPropertyWithName($propertyName)->relationship instanceof \obo\Relationships\Many) {
                    $newEntitiesData = [];
                    $newEntities = [];
                    $collection = $this->valueForPropertyWithName($propertyName);

                    foreach ($value as $subPropertyName => $subPropertyValue) {
                        if (\strpos($subPropertyName, "_") === 0) {
                            \preg_match("#\_(.*?)\_(.*)#", $subPropertyName, $matches);
                            $newEntitiesData[$matches[1]][$matches[2]] = $subPropertyValue;
                        } else {
                            $this->setValueForPropertyWithName($subPropertyValue, $propertyName . "_" . $subPropertyName);
                        }
                    }

                    foreach ($newEntitiesData as $entityKey => $newEntityData) {
                        $newEntities["___" . $entityKey] = $collection->getRelationship()->createEntity($newEntityData);
                    }

                    $collection->add($newEntities);
                } elseif (($propertyValue = $this->valueForPropertyWithName($propertyName)) instanceof \obo\Entity) {
                    if (isset($value[$primaryPropertyName = $propertyValue->entityInformation()->primaryPropertyName]) OR \array_key_exists($primaryPropertyName, $value)) unset($value[$primaryPropertyName]);
                    $propertyValue->setValuesPropertiesFromArray($value);
                } elseif ($this->informationForPropertyWithName($propertyName)->dataType === null OR ($datatypeClass = $this->informationForPropertyWithName($propertyName)->dataType->dataTypeClass()) === \obo\Interfaces\IDataType::DATA_TYPE_CLASS_ARRAY OR $datatypeClass === \obo\Interfaces\IDataType::DATA_TYPE_CLASS_MIXED) {
                    $this->setValueForPropertyWithName($value, $propertyName);
                } else {
                    throw new \obo\Exceptions\Exception("Can't set decomposition values, property '{$propertyName}' must contain entity or relationship");
                }
            } else {
                $this->setValueForPropertyWithName($value, $propertyName);
            }
        }
    }

    /**
     * @return boolean
     */
    public function changed($onlyPersistableProperties = false) {
        return (bool) $this->changedProperties($onlyPersistableProperties ? $this->entityInformation()->persistablePropertiesNames : null, true, true);
    }

    /**
     * @param array | \Iterator | null $onlyFromList
     * @param bool $entityAsPrimaryPropertyValue
     * @param bool $onlyNonPersistentChanges
     * @return array
     */
    public function changedProperties($onlyFromList = null, $entityAsPrimaryPropertyValue = true, $onlyNonPersistentChanges = false) {
        if ($this->isBasedInRepository()) {

            $propertiesChanges = $this->propertiesChanges;

            if ($onlyNonPersistentChanges) {
                foreach ($propertiesChanges as $propertyName => $changeStatus) {
                    if ($changeStatus["persisted"]) unset($propertiesChanges[$propertyName]);
                }
            }

            if ($onlyFromList === null) {
                return $this->propertiesAsArray($propertiesChanges, $entityAsPrimaryPropertyValue);
            } else {
                return $this->propertiesAsArray(array_flip(array_intersect(array_keys($onlyFromList), array_keys($propertiesChanges))), $entityAsPrimaryPropertyValue);
            }

        } else {
            return $this->propertiesAsArray($onlyFromList, $entityAsPrimaryPropertyValue);
        }
    }

    /**
     * @return bool
     */
    public function existDataToStore() {
        return (bool) $this->dataToStore();
    }

    /**
     * @return array
     */
    public function dataToStore() {
        $propertiesChanges = $this->propertiesChanges;
        $changedProperties = $this->changedProperties($this->entityInformation()->persistablePropertiesNames, true, true);

        foreach ($this->propertiesInformation() as $propertyInformation) {
            if ($propertyInformation->hasRelationshipOne() && $propertyInformation->relationship->entityClassNameToBeConnectedInPropertyWithName && isset($changedProperties[$propertyInformation->name])) {
                $value = $this->valueForPropertyWithName($propertyInformation->name);
                $changedProperties[$propertyInformation->name] = ($value instanceof \obo\Entity) ? $value->primaryPropertyValue() : $value;
            }

            if ($propertyInformation->persistable
                    AND $propertyInformation->hasRelationshipOne()
                    AND $propertyInformation->relationship->connectViaProperty !== ""
                    AND isset($propertiesChanges[$propertyInformation->name])
                    AND $this->valueForPropertyWithName($propertyInformation->name, true) != ((($lastPersistedValue = $propertiesChanges[$propertyInformation->name]["lastPersistedValue"]) instanceof \obo\Entity) ? $lastPersistedValue->primaryPropertyValue() : $lastPersistedValue)
                    AND !isset($changedProperties[$propertyInformation->name])
                ) {
                    $value = $this->valueForPropertyWithName($propertyInformation->name);
                    $changedProperties[$propertyInformation->name] = ($value instanceof \obo\Entity) ? $value->primaryPropertyValue() : $value;
            }
        }

        return $changedProperties;
    }

    /**
     * @return bool
     */
    public function isInitialized() {
        return $this->initialized;
    }

    /**
     * @return \obo\Entity
     */
    public function setInitialized() {
        \obo\obo::$eventManager->registerEntity($this);
        \obo\obo::$eventManager->notifyEventForEntity("beforeInitialize", $this);
        $this->initialized = true;
        $this->deleted = ($propertyNameForSoftDelete = $this->entityInformation()->propertyNameForSoftDelete) === "" ? false : (bool) $this->valueForPropertyWithName($propertyNameForSoftDelete);
        \obo\obo::$eventManager->notifyEventForEntity("afterInitialize", $this);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSavingInProgress() {
        return $this->savingInProgress;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setSavingInProgress($value = true) {
        $this->savingInProgress = (bool) $value;
    }

    /**
     * @return bool
     */
    public function isDeletingInProgress() {
        return $this->deletingInProgress;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setDeletingInProgress($value = true) {
        $this->deletingInProgress = (bool) $value;
    }

    /**
     * @return bool
     */
    public function isBasedInRepository() {
        if ($this->basedInRepository !== null) return $this->basedInRepository;
        $managerName = $this->entityInformation()->managerName;
        return $this->setBasedInRepository($managerName::isEntityBasedInRepository($this));
    }

    /**
     * @param bool $state
     * @return bool
     */
    public function setBasedInRepository($state) {
        return $this->basedInRepository = (bool) $state;
    }

    /**
     * @param bool $deleted
     * @return void
     */
    public function setDeleted($deleted = true) {
        $this->deleted = (bool) $deleted;
    }

    /**
     * @return bool
     */
    public function isDeleted() {
        return $this->deleted;
    }

    /**
     * @return bool
     */
    public function isEditable() {
        return !$this->isDeleted();
    }

    /**
     * @return string
     */
    public function entityIdentificationKey() {
        if (!$this->entityIdentificationKey) return $this->entityIdentificationKey = \obo\obo::$identityMapper->identificationKeyForEntity($this);
        return $this->entityIdentificationKey;
    }

    /**
     * @return string
     */
    public function objectIdentificationKey() {
        return spl_object_hash($this);
    }

    /**
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function discardNonPersistedChanges() {
        if ($this->isSavingInProgress()) throw new \obo\Exceptions\Exception("Can't discard changes, the entity is in the process of saving");
        foreach ($this->propertiesChanges as $propertyName => $changeStatus) $this->setValueForPropertyWithName($changeStatus["lastPersistedValue"], $propertyName);
        $this->markUnpersistedPropertiesAsPersisted();
    }

    /**
     * @return void
     * @throws \obo\Exceptions\Exception
     */
    public function discardChanges() {
        if ($this->isSavingInProgress()) throw new \obo\Exceptions\Exception("Can't discard changes, the entity is in the process of saving");
        foreach ($this->propertiesChanges as $propertyName => $changeStatus) $this->setValueForPropertyWithName($changeStatus["oldValue"], $propertyName);
    }

    /**
     * @param string $eventName
     * @param callable $callback
     */
    private function dynamicOn($eventName, callable $callback) {
        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onObject" => $this,
            "name" => $eventName,
            "actionAnonymousFunction" => $callback,
        ]));
    }

    /**
     * @param string $eventName
     * @param callable $callback
     */
    private static function staticOn($eventName, callable $callback) {
        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => static::className(),
            "name" => $eventName,
            "actionAnonymousFunction" => $callback,
        ]));
    }

    /**
     * @param string $eventName
     * @param array $arguments
     * @return type
     */
    public function notifyEvent($eventName, $arguments = []) {
        return \obo\obo::$eventManager->notifyEventForEntity($eventName, $this, $arguments);
    }

    /**
     * @return \obo\Entity
     */
    public function save() {
        $managerName = $this->entityInformation()->managerName;
        $managerName::saveEntity($this);
        return $this;
    }

    /**
     * @return void
     */
    public function delete() {
        $managerName = $this->entityInformation()->managerName;
        $managerName::deleteEntity($this);
    }

    /**
     * @return array
     */
    public function metaData() {
        $entityInformation = $this->entityInformation();
        $propertiesInformation = $this->propertiesInformation();
        $properties = [];

        foreach ($propertiesInformation as $propertyInformation) {
            $properties[$propertyInformation->name]["dataType"] = isset($propertyInformation->dataType) ? $propertyInformation->dataType->name() : null;
            $properties[$propertyInformation->name]["persistable"] = $propertyInformation->persistable;

            if ($relationship = $propertyInformation->relationship) {
                if ($relationship instanceof \obo\Relationships\One) {
                    $relationshipInformation = [
                        "type" => "one",
                        "entityClassNameToBeConnected" => $relationship->entityClassNameToBeConnected,
                    ];

                    if (\is_array($relationship->cascade) AND (bool) $relationship->cascade) {
                        $relationshipInformation["cascade"] = \implode(", ", $relationship->cascade);
                    }

                    $relationshipInformation["autoCreate"] = $relationship->autoCreate;

                    if ($relationship->entityClassNameToBeConnectedInPropertyWithName) $relationshipInformation["entityClassNameToBeConnectedInPropertyWithName"] = $relationship->entityClassNameToBeConnectedInPropertyWithName;
                    if ($relationship->connectViaProperty) $relationshipInformation["connectViaProperty"] = $relationship->connectViaProperty;
                    if ($relationship->ownerNameInProperty) $relationshipInformation["ownerNameInProperty"] = $relationship->ownerNameInProperty;
                } elseif ($relationship instanceof \obo\Relationships\Many) {
                    $relationshipInformation = [
                        "type" => "many",
                        "entityClassNameToBeConnected" => $relationship->entityClassNameToBeConnected,
                    ];

                    if (\is_array($relationship->cascade)) {
                        $relationshipInformation["cascade"] = \implode(", ", $relationship->cascade);
                    }

                    if ($relationship->connectViaPropertyWithName) $relationshipInformation["connectViaProperty"] = $relationship->connectViaPropertyWithName;
                    if ($relationship->ownerNameInProperty) $relationshipInformation["ownerNameInProperty"] = $relationship->ownerNameInProperty;
                    if ($relationship->connectViaRepositoryWithName) $relationshipInformation["connectViaRepositoryWithName"] = $relationship->connectViaRepositoryWithName;
                    if ($relationship->sortVia) $relationshipInformation["sortVia"] = $relationship->sortVia;
                }

                $properties[$propertyInformation->name]["relationship"] = $relationshipInformation;
            }

            if ($propertyInformation->persistable) {
                $properties[$propertyInformation->name]["columnName"] = $propertyInformation->columnName;
            }

            $properties[$propertyInformation->name]["ownerEntityHistory"] = $propertyInformation->ownerEntityHistory;
            $properties[$propertyInformation->name]["declaredInClasses"] = $propertyInformation->declaredInClasses;
        }

        return [
            "static" => [
                "name" => $entityInformation->name,
                "className" => $entityInformation->className,
                "parentClassName" => $entityInformation->parentClassName,
                "storageName" => $entityInformation->storageName,
                "repositoryName" => $entityInformation->repositoryName,
                "primaryPropertyName" => $entityInformation->primaryPropertyName,
                "properties" => $properties,
                "eagerConnections" => $entityInformation->eagerConnections,
            ],
            "dynamic" => [
                "objectIdentificationKey" => $this->objectIdentificationKey(),
                "entityIdentificationKey" => $this->entityIdentificationKey(),
                "primaryPropertyValue" => $this->primaryPropertyValue(),
                "initialized" => $this->isInitialized(),
                "basedInRepository" => $this->isbasedInRepository(),
            ]
        ];
    }

}
