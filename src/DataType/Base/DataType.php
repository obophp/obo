<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
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
