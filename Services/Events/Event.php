<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Services\Events;

class Event extends \obo\Carriers\DataCarrier {

    /**
     * @var string
     */
    public $onClassWithName = null;

    /**
     * @var \obo\Entity
     */
    public $onObject = null;

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var callable
     */
    public $actionAnonymousFunction = null;

    /**
     * @var string
     */
    public $actionEntity = null;

    /**
     * @var string
     */
    public $actionMessage = null;

    /**
     * @var array
     */
    public $actionArguments = array();

    /**
     * @param array $specification
     */
    public function __construct(array $specification) {
        parent::__construct($specification);
        $this->actionArguments["event"] = $this;
    }

    /**
     * @return string
     */
    public function eventIdentificationKey() {
        if (!\is_null($this->onClassWithName)) return $this->name.$this->onClassWithName;
        if (!\is_null($this->onObject)) return $this->name.$this->onObject->objectIdentificationKey();
    }
    
}