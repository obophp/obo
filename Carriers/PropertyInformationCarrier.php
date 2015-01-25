<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
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
     * @var \obo\Relationships\Relationship
     */
    public $relationship = null;

    /**
     * @var \obo\Annotation\Base\Definition[]
     */
    public $annotations = array();

}
