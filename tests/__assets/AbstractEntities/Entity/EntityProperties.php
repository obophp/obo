<?php

namespace obo\Tests\Assets\AbstractEntities;

abstract class EntityProperties extends \obo\EntityProperties {

    /**
     * @obo-dataType(integer)
     * @obo-autoIncrement
     * @obo-columnName(id)
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
