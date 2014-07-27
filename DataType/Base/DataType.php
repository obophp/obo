<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\DataType\Base;

abstract class DataType extends \obo\Object {

    /**
     * @var \obo\Carriers\PropertyInformationCarrier
     */
    protected $propertyInformation = null;

    /**
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     */
    public function __construct(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        $this->propertyInformation = $propertyInformation;
    }

    /**
     * @return string
     */
    public abstract function name();

    /**
     * @return void
     */
    public abstract function registerEvents();

}