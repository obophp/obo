<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Base;

abstract class Definition extends \obo\Object {

    const ENTITY_SCOPE = "entity";
    const METHOD_SCOPE = "method";
    const PROPERTY_SCOPE = "property";

    /**
     * @var \obo\Carriers\EntityInformationCarrier
     */
    protected $entityInformation = null;

    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @return void
     */
    public function __construct(\obo\Carriers\EntityInformationCarrier $entityInformation) {
        $this->entityInformation = $entityInformation;
    }

    /**
     * @return string
     */
    public static function name() {

    }

    /**
     * @return string
     */
    public static function scope() {

    }

    /**
     * @return string
     */
    public static function parametersDefinition() {

    }

    /**
     * @param mixed $values
     * @return void
     */
    public function process($values) {
        $this->checkAnnotationValueStructure($values);
    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

    /**
     * @param array $annotationValue
     * @throws \obo\Exceptions\BadAnnotationException
     * @return void
     */
    public function checkAnnotationValueStructure($annotationValue) {
        $parametersDefinition = self::parametersDefinition();

        if (isset($parametersDefinition["numberOfParameters"])) {
            $this->checkNumberOfParametersForAnnotationValue($annotationValue);
        }

        if (isset($parametersDefinition["parameters"])) {
            $this->checkParametersForAnnotationValue($annotationValue);
        }
    }

    /**
     * @param array $annotationValue
     * @throws \obo\Exceptions\BadAnnotationException
     * @return void
     */
    private function checkNumberOfParametersForAnnotationValue($annotationValue) {
        $parametersDefinition = self::parametersDefinition();

        switch (true) {
            case $parametersDefinition["numberOfParameters"] == "?" :
                    if (count($annotationValue) > 1) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$this->name}' expects zero or one parameter, you send more parameters");
                break;

            case $parametersDefinition["numberOfParameters"] == 0 :
                    if (count($annotationValue)) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$this->name}' does not accept any parameters, you sent " . count($annotationValue) . " parameters");
                break;

            case $parametersDefinition["numberOfParameters"] == -1 :
                    if (count($annotationValue) == 0) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$this->name}' expects one or more parameters, you did not send any parameters");
                break;

            case $parametersDefinition["numberOfParameters"] > 0 :
                    if (count($annotationValue) != $parametersDefinition["numberOfParameters"]) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$this->name}' expects {$parametersDefinition["numberOfParameters"]} parameters, you sent " . count($annotationValue));
                break;

            default:
                throw new \obo\Exceptions\BadAnnotationException("Bad numberOfParameters definition");
        }
    }

    /**
     * @param array $annotationValue
     * @throws \obo\Exceptions\BadAnnotationException
     * @return void
     */
    private function checkParametersForAnnotationValue($annotationValue) {
        $parametersDefinition = self::parametersDefinition();
        $definitionParameters = $parametersDefinition["parameters"];

        foreach ($annotationValue as $parameterName => $parameterRequired) {
            if (!isset($definitionParameters[$parameterName])) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$this->name}' does not accept parameter with name '{$parameterName}'");
            $definitionParameters[$parameterName] = false;
        }

        if(\array_reduce($definitionParameters, function($v, $w) {return $v OR $w;}, false)) {
            foreach ($definitionParameters as $parameterName => $parameterRequired) {
                if ($parameterRequired) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '{$this->name}' requires a parameter with name '{$parameterName}' which was not sent");
            }
        }
    }

}
