<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

abstract class Object {

    /**
     * @return string
     */
    final public static function className() {
        return get_called_class();
    }

    /**
     * @return \Nette\Reflection\ClassType
     */
    public static function getReflection() {
        return new \Nette\Reflection\ClassType(get_called_class());
    }

    /**
     * @param string $message
     * @return boolean
     */
    final public static function respondToMessage($message) {
        if (self::getReflection()->hasMethod($message)) return true;
        if (self::getReflection()->getExtensionMethod($message)) return true;
        return false;
    }

}
