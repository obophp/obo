<?php

namespace obo\Tests\DataTypes\Base;

class TestManager extends \obo\EntityManager {

    /**
     * @param int|array $specification
     * @return Test
     * @throws \obo\Exceptions\EntityNotFoundException
     */
    public static function test($specification) {
        return parent::entity($specification);
    }
}
