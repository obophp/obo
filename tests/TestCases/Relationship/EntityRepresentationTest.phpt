<?php

namespace obo\Tests\Relationship;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class EntityRepresentationTest extends \Tester\TestCase {

    public function testExistenceOfScalarRepresentationOfEntityInDynamicRelationshipOne() {
        $address = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\AddressManager::address([]);

        \Tester\Assert::same(null, $address->valueForPropertyWithName("owner", true));
        \Tester\Assert::same(null, $address->owner);

        $contact = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\ContactManager::contact([])->save();
        $contact->addresses->add($address);

        \Tester\Assert::notSame(null, $address->valueForPropertyWithName("owner", true));
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->valueForPropertyWithName("owner", true));
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->owner->entityIdentificationKey());
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->propertiesChanges()["owner"]["newValue"]->entityIdentificationKey());
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->changedProperties()["owner"]);
        \Tester\Assert::same($contact->primaryPropertyValue(), $address->dataToStore()["owner"]);
        \Tester\Assert::same(\obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\Contact::entityInformation()->name, $address->dataToStore()["ownerEntity"]);
    }

}

(new \obo\Tests\Relationship\EntityRepresentationTest())->run();
