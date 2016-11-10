<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Base;

abstract class Definition extends \obo\Object {

    const ENTITY_SCOPE = "entity";
    const METHOD_SCOPE = "method";
    const PROPERTY_SCOPE = "property";

    const PARAMETERS_NUMBER_DEFINITION = "numberOfParameters";
    const PARAMETERS_DEFINITION = "parameters";

    const ZERO_OR_ONE_PARAMETER = "?";
    const ONE_OR_MORE_PARAMETERS = -1;

    /**
     * @var \obo\Carriers\EntityInformationCarrier
     */
    protected $entityInformation = null;

    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
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
     * @param array $values
     * @return void
     */
    public function process(array $values) {
        $this->checkAnnotationValueStructure($values);
    }

    /**
     * @param \obo\Services\EntitiesInformation\Explorer $explorer
     * @return void
     */
    public function validate(\obo\Services\EntitiesInformation\Explorer $explorer) {

    }

    /**
     * @return void
     */
    public function registerEvents() {

    }

    /**
     * @param array $annotationValue
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    public function checkAnnotationValueStructure($annotationValue) {
        if (isset(static::parametersDefinition()[self::PARAMETERS_NUMBER_DEFINITION])) {
            $this->checkNumberOfParametersForAnnotationValue($annotationValue);
        }

        if (isset(static::parametersDefinition()[self::PARAMETERS_DEFINITION])) {
            $this->checkParametersForAnnotationValue($annotationValue);
        }
    }

    /**
     * @param array $annotationValue
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    private function checkNumberOfParametersForAnnotationValue($annotationValue) {
        $parametersDefinition = static::parametersDefinition();

        switch (true) {
            case $parametersDefinition[self::PARAMETERS_NUMBER_DEFINITION] == self::ZERO_OR_ONE_PARAMETER:
                if (count($annotationValue) > 1) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '" . static::name() . "' expects zero or one parameter, more parameters given");
                break;

            case $parametersDefinition[self::PARAMETERS_NUMBER_DEFINITION] == self::ONE_OR_MORE_PARAMETERS:
                if (count($annotationValue) == 0) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '" . static::name() . "' expects one or more parameters, no parameters given");
                break;

            case $parametersDefinition[self::PARAMETERS_NUMBER_DEFINITION] > 0:
                if (count($annotationValue) != $parametersDefinition[self::PARAMETERS_NUMBER_DEFINITION]) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '" . static::name() . "' expects {$parametersDefinition[self::PARAMETERS_NUMBER_DEFINITION]} parameters, " . count($annotationValue) . " parameters given");
                break;

            default:
                throw new \obo\Exceptions\BadAnnotationException("Bad '" . self::PARAMETERS_NUMBER_DEFINITION . "' definition");
        }
    }

    /**
     * @param array $annotationValue
     * @return void
     * @throws \obo\Exceptions\BadAnnotationException
     */
    private function checkParametersForAnnotationValue($annotationValue) {
        $parametersDefinition = static::parametersDefinition()[self::PARAMETERS_DEFINITION];

        foreach ($annotationValue as $parameterName => $parameterRequired) {
            if (!(isset($parametersDefinition[$parameterName]) OR \array_key_exists($parameterName, $parametersDefinition))) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '" . static::name() . "' does not accept parameter with name '{$parameterName}'");
            $parametersDefinition[$parameterName] = false;
        }

        if (\array_reduce($parametersDefinition, function($v, $w) {return $v OR $w;
        }, false)) {
            foreach ($parametersDefinition as $parameterName => $parameterRequired) {
                if ($parameterRequired) throw new \obo\Exceptions\BadAnnotationException("Annotation with name '" . static::name() . "' requires a parameter with name '{$parameterName}' which was not sent");
            }
        }
    }

}
