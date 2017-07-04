<?php

namespace obo\Tests\Assets\AbstractEntities;

abstract class NoteManager extends \obo\Tests\Assets\AbstractEntities\EntityManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Entity\Assets\Entities\Notes\Note
     */
    public static function note($specification) {
        return static::entity($specification);
    }

}
