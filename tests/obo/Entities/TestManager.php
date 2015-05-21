<?php

namespace obo\Tests\Entities;

class TestManager extends \obo\EntityManager {

    /**
     * @param int|array $specification
     * @return \obo\Tests\Events\BeforeSave\Test
     * @throws \obo\Exceptions\EntityNotFoundException
     */
    public static function test($specification) {
        return parent::entity($specification);
    }
}
