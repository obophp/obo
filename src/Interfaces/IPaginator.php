<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IPaginator {

    /**
     * @param int $itemCount
     * @return void
     */
    public function setItemCount($itemCount);

    /**
     * @return \obo\Carriers\QuerySpecification
     */
    public function getSpecification();

}
