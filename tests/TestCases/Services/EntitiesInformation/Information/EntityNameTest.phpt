<?php

namespace obo\Tests\Services\EntitiesInformation\Information;

use Tester\Assert;

require __DIR__ . "/../../../../bootstrap.php";

/**
 * @testCase
 */
class EntityNameTest extends \Tester\TestCase {

    public function testEntityClassNameForEntityWithName() {
        \Tester\Assert::same("obo\Tests\Assets\Entities\Contacts\Contact", \obo\obo::$entitiesInformation->entityClassNameForEntityWithName("Contact"));
        \Tester\Assert::exception(function() {
           \obo\obo::$entitiesInformation->entityClassNameForEntityWithName("NonExistingEntity");
        }, \obo\Exceptions\EntityClassNotFoundException::class, "For entity with name 'NonExistingEntity' doesn't exist class which would implement it. Possible cause could be that folders with all models are not loaded");
    }

}

(new EntityNameTest())->run();
