<?php

/**
 * This file is part of framework Obo Development version (http://www.obophp.org/)
 * @link http://www.obophp.org/
 * @author Adam Suba, http://www.adamsuba.cz/
 * @copyright (c) 2011 - 2013 Adam Suba
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

namespace obo\Carriers;

interface IQuerySpecification {

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
