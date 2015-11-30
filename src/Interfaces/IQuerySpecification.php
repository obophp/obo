<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IQuerySpecification {

    const PARAMETER_PLACEHOLDER = "?";
    const PARAMETER_PREFIX = ":";

    /**
     * @return array
     */
    public function getWhere();

    /**
     * @return array
     */
    public function getOrderBy();

    /**
     * @return array
     */
    public function getLimit();

    /**
     * @return array
     */
    public function getOffset();

}
