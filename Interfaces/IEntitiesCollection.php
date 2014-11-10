<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2014 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Interfaces;

interface IEntitiesCollection {

    /**
     * @return string
     */
    public function getEntitiesClassName();

    /**
     * @param \obo\Interfaces\IPaginator $paginator
     * @param \obo\Interfaces\IFilter $filter
     * @return array
     */
    public function getSubset(\obo\Interfaces\IPaginator $paginator, \obo\Interfaces\IFilter $filter = null);

    /**
     * @param \obo\Carriers\QuerySpecification $specification
     * @return array
     */
    public function find(\obo\Carriers\QuerySpecification $specification);

}
