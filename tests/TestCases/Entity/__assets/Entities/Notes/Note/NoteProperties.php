<?php

namespace obo\Tests\TestCases\Entity\Assets\Entities\Notes;

class NoteProperties extends \obo\Tests\Assets\Entities\NoteProperties {

    /**
     *  @obo-one(targetEntity = "property:ownerEntityName", autoCreate=true)
     */
    public $owner = null;

    public $ownerEntityName = "";

    public $text = "";
}
