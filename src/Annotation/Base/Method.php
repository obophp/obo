<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
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
