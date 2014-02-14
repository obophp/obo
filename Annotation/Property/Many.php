<?php

/**

 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class Many extends \obo\Annotation\Base\Property {

    protected $targetEntity = "";
    protected $connectViaProperty = "";
    protected $ownerNameInProperty = "";
    protected $connectViaRepository = "";
    protected $sortVia = "";
    protected $cascadeOptions = array();

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
        return array("parameters" => array("targetEntity" => true, "connectViaProperty" => false, "ownerNameInProperty" => false, "connectViaRepository" => false, "sortVia" => false, "cascade" => false));
    }

    /**
     * @param array $values
     * @throws \obo\Exceptions\BadAnnotationException
     * @return void
     */
    public function proccess($values) {
        parent::proccess($values);

        if (!\class_exists($values["targetEntity"])) throw new \obo\Exceptions\BadAnnotationException("Relationship 'many' could not be built because it relies on a parameter 'connectViaProperty' or 'connectViaRepository' ");

        $this->targetEntity = $values["targetEntity"];

        $relationship = $this->propertyInformation->relationship = new \obo\Relationships\Many($this->targetEntity, $this->propertyInformation->name);

        if (isset($values["connectViaProperty"])) {
            $relationship->connectViaPropertyWithName = $this->connectViaProperty =  $values["connectViaProperty"];
            if (isset($values["ownerNameInProperty"])) $relationship->ownerNameInProperty = $this->ownerNameInProperty = $values["ownerNameInProperty"];
        } elseif (isset($values["connectViaRepository"])) {
            $relationship->connectViaRepositoryWithName = $this->connectViaRepository =  $values["connectViaRepository"];
            if (isset($values["ownerNameInProperty"])) throw new \obo\Exceptions\BadAnnotationException("Annotation 'ownerNameInProperty' may be used only with 'connectViaProperty' annotation");
        } else {
            throw new \obo\Exceptions\BadAnnotationException("Relationship 'many' could not be built because it relies on a parameter 'connectViaProperty' or 'connectViaRepository'");
        }

        if (isset($values["cascade"])) $this->cascadeOptions = \preg_split("#, ?#", $values["cascade"]);

        if(isset($values["sortVia"])) {
            $relationship->sortVia = $this->sortVia = $values["sortVia"];
        }

        $this->propertyInformation->dataType = new \obo\DataType\Object($this->propertyInformation, "\obo\Relationships\EntitiesCollection");
    }

    public function registerEvents() {

            foreach ($this->cascadeOptions as $cascadeOption) {
                    if ($cascadeOption == "save") {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                            "onClassWithName" => $this->entityInformation->className,
                            "name" => "beforeSave",
                            "actionAnonymousFunction" => function($arguments) {$arguments["entity"]->valueForPropertyWithName($arguments["propertyName"])->save();},
                            "actionArguments" => array("propertyName" => $this->propertyInformation->name),
                        )));
                    } elseif ($cascadeOption == "delete") {
                        \obo\Services::serviceWithName(\obo\obo::EVENT_MANAGER)->registerEvent(new \obo\Services\Events\Event(array(
                            "onClassWithName" => $this->entityInformation->className,
                            "name" => "beforeDelete",
                            "actionAnonymousFunction" => function($arguments) {$arguments["entity"]->valueForPropertyWithName($arguments["propertyName"])->delete($arguments["removeEntity"]);},
                            "actionArguments" => array("propertyName" => $this->propertyInformation->name, "removeEntity" => (bool) $this->connectViaProperty),
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

                        if (!$currentPropertyValue instanceof $propertyInformation->relationship->entityClassNameToBeConnected AND !$currentPropertyValue instanceof \obo\Relationships\EntitiesCollection) {
                            $arguments["entity"]->setValueForPropertyWithName($propertyInformation->relationship->relationshipForOwnerAndPropertyValue($arguments["entity"], $currentPropertyValue), $arguments["propertyName"]);
                        }

                    },
                "actionArguments" => array("propertyName" => $this->propertyInformation->name),
            )));

    }
}