<?php

namespace obo\Tests\Assets\Entities;

class EntityProperties extends \obo\EntityProperties {

    /**
     * @obo-dataType(integer)
     * @obo-autoIncrement
     */
    public $id = "";

    /**
     * @obo-timeStamp(beforeInsert)
     */
    public $createdAt = null;

    /**
     * @obo-timeStamp(beforeUpdate)
     */
    public $updatedAt = null;

}
