<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Annotation\Base;

abstract class Method extends \obo\Annotation\Base\Definition {

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @param \obo\Carriers\EntityInformationCarrier $entityInformation
     * @param string $methodName
     * @return void
     */
    public function __construct(\obo\Carriers\EntityInformationCarrier $entityInformation, $methodName) {
        parent::__construct($entityInformation);
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public static function scope() {
        return self::METHOD_SCOPE;
    }

}
