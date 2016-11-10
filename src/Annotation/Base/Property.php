<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Base;

abstract class Property extends \obo\Annotation\Base\Definition {

    /**
     * @var \obo\Carriers\PropertyInformationCarrier
     */
    protected $propertyInformation;

    public function __construct(\obo\Carriers\PropertyInformationCarrier $propertyInformation, \obo\Carriers\EntityInformationCarrier $entityInformation) {
        parent::__construct($entityInformation);
        $this->propertyInformation = $propertyInformation;
    }

    /**
     * @return string
     */
    public static function scope() {
        return self::PROPERTY_SCOPE;
    }

    /**
     * @return \obo\Carriers\PropertyInformationCarrier
     */
    public function getPropertyInformation() {
        return $this->propertyInformation;
    }

}
