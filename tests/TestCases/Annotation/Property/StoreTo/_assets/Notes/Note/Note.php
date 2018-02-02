<?php

namespace obo\Tests\TestCases\Annotation\Property\StoreTo\Assets\Entities\Notes;

class Note extends \obo\Tests\Assets\Entities\Note {

    /**
     * @obo-run(afterChangeLanguage)
     */
    public function touchLanguage() {
        $this->language;
    }

}
