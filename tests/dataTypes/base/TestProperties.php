<?php

namespace obo\Tests\DataTypes\Base;

class TestProperties extends \obo\EntityProperties {

    public $id = null;

    /** @obo-dataType(array) */
    public $parray = null;

    /** @obo-dataType(boolean) */
    public $pboolean = null;

    /** @obo-dataType(dateTime) */
    public $pdateTime = null;

    /** @obo-dataType(float) */
    public $pfloat = null;

    /** @obo-dataType(integer) */
    public $pinteger = null;

    /** @obo-dataType(number) */
    public $pnumber = null;

    /** @obo-dataType(object) */
    public $pobject = null;

    /** @obo-dataType(string) */
    public $pstring = null;
}
