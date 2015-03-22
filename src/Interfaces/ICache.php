<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface ICache {

    /**
     * @param string $key
     * @return mixed
     */
    public function load($key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function store($key, $value);

}
