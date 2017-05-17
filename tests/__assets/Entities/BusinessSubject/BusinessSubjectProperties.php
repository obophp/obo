<?php

namespace obo\Tests\Assets\Entities;

class BusinessSubjectProperties extends \obo\Tests\Assets\Entities\EntityProperties {

    /**
     * @obo-dataType(integer)
     * @obo-autoIncrement
     */
    public $id = "";

    public $name = "";

    /**
     * @obo-one(targetEntity="\obo\Tests\Assets\Entities\BusinessSubject")
     */
    public $original = null;

    public $deleted = false;

}
