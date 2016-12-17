<?php

namespace obo\Tests\Assets\Entities;

class NoteProperties extends \obo\Tests\Assets\Entities\EntityProperties {

    /**
     *  @obo-one(targetEntity = "property:ownerEntityName")
     */
    public $owner = null;

    public $ownerEntityName = "";

    public $text = "";
}
