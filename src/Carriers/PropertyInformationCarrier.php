<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

class PropertyInformationCarrier extends \obo\Object {

    /**
     * @var \obo\Carriers\EntityInformationCarrier
     */
    public $entityInformation = null;

    /**
     * @var \obo\Annotation\Base\Definition[]
     */
    public $annotations = array();

    /**
     * @var \obo\Relationships\Relationship
     */
    public $relationship = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $varName = null;

    /**
     * @var mixed
     */
    public $defaultValue = null;

    /**
     * @var string
     */
    public $access = "public";

    /**
     * @var \obo\DataType\Base\DataType
     */
    public $dataType = null;

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
    public $columnName = null;

    /**
     * @var boolean
     */
    public $persistable = null;

    /**
     * @var boolean
     */
    public $autoIncrement = null;

    /**
     * @var boolean
     */
    public $nullable = null;
}
