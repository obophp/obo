<?php

namespace obo\Tests\src\IdentityMapper;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class EntityWithIdentificationKeyTest extends \Tester\TestCase {

    public function testGetMappedEntity() {
        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact([\obo\Tests\Assets\Entities\Contacts\Contact::entityInformation()->primaryPropertyName => 1]);
        \Tester\Assert::same($contact, \obo\obo::$identityMapper->entityWithidentificationKey($contact->entityIdentificationKey()));
    }

    public function testGetNonMappedEntity() {
        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact([\obo\Tests\Assets\Entities\Contacts\Contact::entityInformation()->primaryPropertyName => 1]);
        \Tester\Assert::null(\obo\obo::$identityMapper->entityWithidentificationKey($contact->entityIdentificationKey() . "nonExisting"));
    }

}

(new EntityWithidentificationKeyTest())->run();
