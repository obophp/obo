<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class PropertyInformationCarrier extends \obo\Carriers\DataCarrier {

    /**
     * @var \obo\Carriers\EntityInformationCarrier
     */
    public $entityInformation = null;

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var \obo\DataType\Base\DataType
     */
    public $dataType = null;

    /**
     * @var boolean
     */
    public $directAccessToRead = true;

    /**
     * @var boolean
     */
    public $directAccessToWrite = true;

    /**
     * @var string
     */
    public $getterName = null;

    /**
     * @var string
     */
    public $setterName = null;

    /**
     * @var string
     */
    public $access = "public";

    /**
     * @var string
     */
    public $columnName = null;

    /**
     * @var boolean
     */
    public $autoIncrement = null;

    /**
     * @var boolean
     */
    public $nullable = null;

    /**
     * @var \obo\Relationships\Relationship
     */
    public $relationship = null;

    /**
     * @var \obo\Annotation\Base\Definition[]
     */
    public $annotations = array();

}
