<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo;

class EntityProperties extends \obo\Object {
    /**
     * @deprecated
     */
    protected $ownerProperties = null;
    protected $_owner = null;

    /**
     * @param \obo\Entity $owner
     */
    public function __construct(\obo\Entity $owner) {
        $this->ownerProperties = $owner;
        $this->_owner = $owner;
    }

    /**
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function &__get($name) {
        throw new \obo\Exceptions\PropertyNotFoundException("Property with name '{$name}' can not be read, does not exist in entity '" . $this->_owner->className(). "'");
    }

    /**
     * @throws \obo\Exceptions\PropertyNotFoundException
     */
    public function __set($name, $value) {
        throw new \obo\Exceptions\PropertyNotFoundException("Can not write to the property with name '{$name}', it is read-only");
    }
}