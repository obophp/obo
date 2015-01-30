<?php

namespace obo\Tests\Entities;

class TestProperties extends \obo\EntityProperties {

    /** @obo-autoIncrement */
    public $id = 0;

    /** @obo-dataType(string) */
    public $testProperty = "";
}
