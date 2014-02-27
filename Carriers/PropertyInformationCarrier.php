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

    public $entityInformation = null;
    public $name = "";
    public $dataType = null;
    public $directAccessToRead = true;
    public $directAccessToWrite = true;
    public $getterName = null;
    public $setterName = null;
    public $access = "public";
    public $columnName = null;
    public $relationship = null;
    public $annotations = array();
}