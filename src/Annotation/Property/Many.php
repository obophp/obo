<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class Many extends \obo\Annotation\Base\Property {

    /**
     * @var string
     */
    protected $targetEntity = "";

    /**
     * @var string
     */
    protected $connectViaProperty = "";

    /**
     * @var string
     */
    protected $ownerNameInProperty = "";

    /**
     * @var string
     */
    protected $connectViaRepository = "";

    /**
     * @var string
     */
    protected $sortVia = "";

    /**
     * @var array
     */
    protected $cascadeOptions = [];

    /**
     * @return string
     */
    public static function name() {
        return "many";
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
                "connectViaRepository" => false,
                "sortVia" => false,
                "cascade" => false
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

        if (isset($values["cascade"])) $this->cascadeOptions = \preg_split("#, ?#", $values["cascade"]);

        if (isset($values["sortVia"])) $this->sortVia = $values["sortVia"];

        if (isset($values["connectViaProperty"])) {
            $this->connectViaProperty = $values["connectViaProperty"];
            if (isset($values["ownerNameInProperty"])) $this->ownerNameInProperty = $values["ownerNameInProperty"];
        } elseif (isset($values["connectViaRepository"])) {
            $this->connectViaRepository = $values["connectViaRepository"];
            if (!\array_search("delete", $this->cascadeOptions)) $this->cascadeOptions[] = "delete";
        }

        $this->propertyInformation->dataType = \obo\obo::$entitiesExplorer->createDataType(\obo\DataType\ObjectDataType::name(), $this->propertyInformation, ["className" => "\\obo\\Relationships\\EntitiesCollection"]);
        $this->propertyInformation->columnName = "";
        $this->propertyInformation->persistable = false;
    }

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function validate(\obo\Services\EntitiesInformation\Explorer $explorer) {
        if (!isset($explorer->entitiesInformationsByEntitiesNames()[$this->targetEntity])) throw new \obo\Exceptions\BadAnnotationException("Relationship 'many', in class '{$this->entityInformation->className}' could not be built. Entity with name '{$this->targetEntity}' could not be connected because it does not exist.");
        if ($this->connectViaRepository === "" AND $this->connectViaProperty === "") throw new \obo\Exceptions\BadAnnotationException("Relationship 'many' could not be built because it relies on a parameter 'connectViaProperty' or 'connectViaRepository'");
        if ($this->connectViaRepository !== "" AND $this->ownerNameInProperty !== "") throw new \obo\Exceptions\BadAnnotationException("Annotation 'ownerNameInProperty' may be used only with 'connectViaProperty' annotation");
    }

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @return void
     */
    public function finalize(\obo\Services\EntitiesInformation\Explorer $explorer) {
        $relationship = new \obo\Relationships\Many($explorer->entitiesInformationsByEntitiesNames()[$this->targetEntity]->className, $this->propertyInformation->name);
        $relationship->sortVia = $this->sortVia;
        $relationship->cascade = $this->cascadeOptions;
        $relationship->connectViaPropertyWithName = $this->connectViaProperty;
        $relationship->connectViaRepositoryWithName = $this->connectViaRepository;
        $relationship->ownerNameInProperty = $this->ownerNameInProperty;
        $this->propertyInformation->relationship = $relationship;
    }

    /**
     * @return void
     */
    public function registerEvents() {
        foreach ($this->cascadeOptions as $cascadeOption) {
            if ($cascadeOption == "save") {
                \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => "afterSave",
                    "actionAnonymousFunction" => function($arguments) { if ($arguments["entity"]->valueForPropertyWithName($arguments["propertyName"], false, false) instanceof \obo\Relationships\EntitiesCollection) $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"], false, false)->save();
                    },
                    "actionArguments" => ["propertyName" => $this->propertyInformation->name],
                ]));
            } elseif ($cascadeOption == "delete") {
                \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
                    "onClassWithName" => $this->entityInformation->className,
                    "name" => "beforeDelete",
                    "actionAnonymousFunction" => function($arguments) { if ($arguments["entity"]->valueForPropertyWithName($arguments["propertyName"]) instanceof \obo\Relationships\EntitiesCollection) $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"])->delete($arguments["removeEntity"]);
                    },
                    "actionArguments" => ["propertyName" => $this->propertyInformation->name, "removeEntity" => (bool) $this->connectViaProperty],
                ]));
            }
        }

        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeRead" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                if ($arguments["entityAsPrimaryPropertyValue"]) return;

                    $propertyInformation = $arguments["entity"]->informationForPropertyWithName($arguments["propertyName"]);
                    $currentPropertyValue = $arguments["entity"]->valueForPropertyWithName($arguments["propertyName"]);

                if (!$currentPropertyValue instanceof $propertyInformation->relationship->entityClassNameToBeConnected AND !$currentPropertyValue instanceof \obo\Relationships\EntitiesCollection) {
                    $arguments["entity"]->setValueForPropertyWithName($propertyInformation->relationship->relationshipForOwnerAndPropertyValue($arguments["entity"], $currentPropertyValue), $arguments["propertyName"], false);
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name],
        ]));

    }

}
