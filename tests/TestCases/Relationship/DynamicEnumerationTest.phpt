<?php

namespace obo\Tests\Relationship;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class DynamicEnumerationTest extends \Tester\TestCase {

    public function testAllowedValues() {
        $address = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddressManager::address([])->save();

        \Tester\Assert::null($address->owner);

        $person = \obo\Tests\TestCases\Relationship\Assets\Entities\PersonManager::person([])->save();
        $address->owner = $person;

        \Tester\Assert::same($person, $address->owner);

        $contact = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\ContactManager::contact([]);
        $contact->addresses->add($address);
        $contact->save();

        \Tester\Assert::same($contact, $address->owner);
    }

    public function testForbiddenValues() {
        \Tester\Assert::exception(function () {
            $address = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddressManager::address([])->save();
            $anotherAddress = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddressManager::address([])->save();
            $address->owner = $anotherAddress;
        }, \obo\Exceptions\PropertyAccessException::class, "Entity with type 'TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddress' isn't allowed for relation defined in entity 'obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\EnumeratedAddress' and property 'owner'. Declared entities are 'TestCases\Relationship\Assets\Entities\Contacts\Contact, TestCases\Relationship\Assets\Entities\Person'");
    }

}

(new \obo\Tests\Relationship\DynamicEnumerationTest())->run();
