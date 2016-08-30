<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class ReadOnly extends \obo\Annotation\Base\Property {

    /**
     * @return string
     */
    public static function name() {
        return "readOnly";
    }

    /**
     * @return array
     */
    public static function parametersDefinition() {
        return [self::PARAMETERS_NUMBER_DEFINITION => 1];
    }

    /**
     * @param array $values
     * @return void
     */
    public function process(array $values) {
        parent::process($values);
        $this->propertyInformation->readOnly = $values[0];
    }

    /**
     * @return void
     */
    public function registerEvents() {

        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeChange" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {
                $backtrace = \debug_backtrace();

                if (isset($backtrace[5]["class"])) {
                    $sourceClassName = $backtrace[5]["class"];
                    if (\is_subclass_of($sourceClassName, "\obo\Entity")) {
                        if ($sourceClassName !== $this->entityInformation->className) $this->throwAccessException($sourceClassName);
                    } elseif (\is_subclass_of($sourceClassName, "\obo\EntityProperties")) {
                        if ($sourceClassName !== $this->entityInformation->propertiesClassName) $this->throwAccessException($sourceClassName);
                    } else {
                        $this->throwAccessException($sourceClassName);
                    }
                } else {
                     $this->throwAccessException("anonymous function");
                }

            },
            "actionArguments" => ["propertyName" => $this->propertyInformation->name, "annotation" => $this],
        ]));
    }

    /**
     * @param string $source
     * @return void
     * @throws \obo\Exceptions\PropertyAccessException
     */
    protected function throwAccessException($source) {
        throw new \obo\Exceptions\PropertyAccessException("Can't access property '{$this->propertyInformation->name}' of entity '{$this->entityInformation->className}' from '{$source}'. Property is read only");
    }
}
