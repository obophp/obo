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
    public $annotations = [];

    /**
     * @var \obo\Relationships\Relationship
     */
    public $relationship = null;

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var string
     */
    public $caption = "";

    /**
     * @var string
     */
    public $varName = "";

    /**
     * @var mixed
     */
    public $defaultValue = null;

    /**
     * @var string
     */
    public $accessLevel = \obo\Annotation\Property\AccessLevel::ACCESS_LEVEL_PUBLIC;

    /**
     * @var boolean
     */
    public $readOnly = false;

    /**
     * @var \obo\DataType\Base\DataType
     */
    public $dataType = null;

    /**
     * @var string
     */
    public $getterName = "";

    /**
     * @var string
     */
    public $setterName = "";

    /**
     * @var string
     */
    public $storageName = "";

    /**
     * @var string
     */
    public $repositoryName = "";

    /**
     * @var string
     */
    public $columnName = "";

    /**
     * @var bool
     */
    public $persistable = null;

    /**
     * @var bool
     */
    public $autoIncrement = null;

    /**
     * @var bool
     */
    public $nullable = null;

    /**
     * @var string
     */
    public $firstDeclaringClassName = "";

    /**
     * @var string
     */
    public $firstDeclaringClassOboName = "";

    /**
     * @var string
     */
    public $firstDeclaringClassOboNameSameAsParent = false;

    /**
     * @var string
     */
    public $lastDeclaringClassName = "";

    /**
     * @var string
     */
    public $lastDeclaringClassOboName = "";

}
