<?php

namespace obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes;

class NoteProperties extends \obo\Tests\Assets\Entities\NoteProperties {

    /**
     * @obo-storeTo(metadata)
     */
    public $language = "en";

    /**
     * @obo-dataType(array)
     */
    public $metadata = [];

}
