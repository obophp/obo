<?php

namespace obo\Tests\Assets\AbstractEntities;

abstract class NoteProperties extends \obo\Tests\Assets\AbstractEntities\EntityProperties {

    /**
     *  @obo-one(targetEntity = "property:ownerEntityName")
     */
    public $owner = null;

    public $ownerEntityName = "";

    public $text = "";
}
