<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Property;

class AccessLevel extends \obo\Annotation\Base\Property {
    const ACCESS_LEVEL_PUBLIC = "public";
    const ACCESS_LEVEL_NAMESPACE = "namespace";
    const ACCESS_LEVEL_PROTECTED = "protected";
    const ACCESS_LEVEL_PRIVATE = "private";

    public static $accessLevels = [self::ACCESS_LEVEL_PUBLIC, self::ACCESS_LEVEL_NAMESPACE, self::ACCESS_LEVEL_PROTECTED, self::ACCESS_LEVEL_PRIVATE];

    /**
     * @return string
     */
    public static function name() {
        return "accessLevel";
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
     * @throws \obo\Exceptions\BadDataTypeException
     */
    public function process(array $values) {
        parent::process($values);
        if (!\in_array($values[0], self::$accessLevels)) throw new \obo\Exceptions\BadAnnotationException("Select one of the following options [" . \implode (", ", self::$accessLevels) . "]. Selected access level '" . $values[0] . "' does not exist");
        $this->propertyInformation->accessLevel = $values[0];
    }

    /**
     * @return void
     */
    public function registerEvents() {

        \obo\obo::$eventManager->registerEvent(new \obo\Services\Events\Event([
            "onClassWithName" => $this->entityInformation->className,
            "name" => "beforeRead" . \ucfirst($this->propertyInformation->name),
            "actionAnonymousFunction" => function($arguments) {

                $backtrace = \debug_backtrace();

                if (isset($backtrace[5]["class"])) {
                    $sourceClassName = $backtrace[5]["class"];

                    switch ($this->propertyInformation->accessLevel){
                        case self::ACCESS_LEVEL_NAMESPACE :
                            $sorceNamespace = substr($sourceClassName, 0, \strlen(strrchr($sourceClassName, "\\")) * -1);
                            if (\strpos($sorceNamespace, $this->entityInformation->namespace) !== 0  AND \strpos($this->entityInformation->namespace, $sorceNamespace) !== 0) $this->throwAccessException($sourceClassName);
                            break;

                        case self::ACCESS_LEVEL_PROTECTED :
                            if (\is_subclass_of($sourceClassName, "\obo\Entity")) {
                                if (!\is_subclass_of($sourceClassName, $this->entityInformation->className) AND !\is_subclass_of($this->entityInformation->className, $sourceClassName)) $this->throwAccessException($sourceClassName);
                            } elseif ((\is_subclass_of($sourceClassName, "\obo\EntityProperties"))) {
                                if (!\is_subclass_of($sourceClassName, $this->entityInformation->propertiesClassName) AND !\is_subclass_of($this->entityInformation->propertiesClassName, $sourceClassName)) $this->throwAccessException($sourceClassName);
                            }
                            break;

                        case self::ACCESS_LEVEL_PRIVATE :
                            if (\is_subclass_of($sourceClassName, "\obo\Entity")) {
                                if ($sourceClassName !== $this->entityInformation->className) $this->throwAccessException($sourceClassName);
                            } elseif (\is_subclass_of($sourceClassName, "\obo\EntityProperties")) {
                                if ($sourceClassName !== $this->entityInformation->propertiesClassName) $this->throwAccessException($sourceClassName);
                            }
                            break;
                    }

                } else {
                   $this->throwAccessException("closure function");
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
        throw new \obo\Exceptions\PropertyAccessException("Can't access property '{$this->propertyInformation->name}' of entity '{$this->entityInformation->className}' from '{$source}'. Property has accessLevel set to '{$this->propertyInformation->accessLevel}'");
    }
}
