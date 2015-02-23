<?php

/**
 * This file is part of the Obo framework for application domain logic.
 * Obo framework is based on voluntary contributions from different developers.
 *
 * @link https://github.com/obophp/obo
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IEntitiesCollection {

    /**
     * @return string
     */
    public function getEntitiesClassName();

    /**
     * return only clone original specification other modifications will not affect the original specification
     * @return \obo\Carriers\QuerySpecification
     */
    public function getSpecification();

    /**
     * @param \obo\Interfaces\IQuerySpecification $specification
     * @return array
     */
    public function find(\obo\Interfaces\IQuerySpecification $specification);

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return array
     */
    public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null);

}
