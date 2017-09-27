<?php

namespace obo\Tests\Relationship;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class EntityRepresentationTest extends \Tester\TestCase {

    public function testExistenceOfScalarRepresentationOfEntityInDynamicRelationshipOne() {
        $address = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\RepresentationAddressManager::address([]);

        \Tester\Assert::same(null, $address->valueForPropertyWithName("owner", true));
        \Tester\Assert::same(null, $address->owner);

        $contact = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\RepresentationContactManager::contact([])->save();
        $contact->addresses->add($address);

        \Tester\Assert::notSame(null, $address->valueForPropertyWithName("owner", true));
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->valueForPropertyWithName("owner", true));
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->owner->entityIdentificationKey());
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->propertiesChanges()["owner"]["newValue"]->entityIdentificationKey());
        \Tester\Assert::same($contact->entityIdentificationKey(), $address->changedProperties()["owner"]);
        \Tester\Assert::same($contact->primaryPropertyValue(), $address->dataToStore()["owner"]);
        \Tester\Assert::same(\obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\RepresentationContact::entityInformation()->name, $address->dataToStore()["ownerEntity"]);
    }

    public function testExistenceOfScalarRepresentationOfEntityInRelationshipOne() {
        $contact = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\RepresentationContactManager::contact([])->save();
        $phone = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\RepresentationPhoneManager::phone([])->save();
        $contact->phones->add($phone);

        \Tester\Assert::same($contact->entityIdentificationKey(), $phone->valueForPropertyWithName("contact", true));
        \Tester\Assert::same($phone->entityIdentificationKey(), $contact->phones->rewind()->entityIdentificationKey());

        $phone->contact = $contact->primaryPropertyValue();
        \Tester\Assert::same($contact->entityIdentificationKey(), $phone->valueForPropertyWithName("contact", true));
    }

}

(new \obo\Tests\Relationship\EntityRepresentationTest())->run();
