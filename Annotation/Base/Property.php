<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Base;

abstract class Property extends \obo\Annotation\Base\Definition {

    /**
     * @var \obo\Carriers\PropertyInformationCarrier
     */
    protected $propertyInformation;

    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @param \obo\Carriers\PropertyInformationCarrier $propertyInformation
     */
    public function __construct(\obo\Carriers\PropertyInformationCarrier $propertyInformation) {
        parent::__construct($propertyInformation->entityInformation);
        $this->propertyInformation = $propertyInformation;
    }

    /**
     * @return string
     */
    public static function scope() {
        return self::PROPERTY_SCOPE;
    }

    public function getPropertyInformation() {
        return $this->propertyInformation;
    }
}