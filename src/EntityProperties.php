<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

class EntityProperties extends \obo\BaseObject {

    /**
     * @var \obo\Entity
     */
    protected $_owner = null;

    /**
     * @param \obo\Entity $owner
     */
    public function __construct(\obo\Entity $owner) {
        $this->_owner = $owner;
    }

    /**
     * @param mixed $name
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function &__get($name) {
        throw new \obo\Exceptions\PropertyNotFoundException("Property with name '{$name}' can't be read, does not exist in entity '" . $this->_owner->className(). "'");
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function __set($name, $value) {
        throw new \obo\Exceptions\PropertyNotFoundException("Can't write to the property with name '{$name}' in entity '" . $this->_owner->className(). "', it is read-only");
    }

}
