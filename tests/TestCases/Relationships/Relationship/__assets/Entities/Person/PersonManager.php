<?php

namespace obo\Tests\TestCases\Relationships\Relationship\Assets\Entities;

class PersonManager extends \obo\Tests\Assets\AbstractEntities\EntityManager {

    /**
     * @param mixed $specification
     * @return \obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Person
     */
    public static function person($specification) {
        return static::entity($specification);
    }

}
