<?php

namespace obo\Tests\TestCases\Relationship;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class DynamicInterfaceTest extends \Tester\TestCase {

    public function testCreateInterfacedRelation() {
        $contact = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\InterfacedContactManager::contact([])->save();
        $address = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\InterfacedAddressManager::address([])->save();
        $address->owner = $contact;

        \Tester\Assert::same($contact, $address->owner);
    }

    public function testCreateNonInterfacedRelation() {
        \Tester\Assert::exception(function () {
            $person = \obo\Tests\TestCases\Relationship\Assets\Entities\PersonManager::person([])->save();
            $address = \obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\InterfacedAddressManager::address([])->save();
            $address->owner = $person;
        }, \obo\Exceptions\PropertyAccessException::class, "Entity with class 'obo\Tests\TestCases\Relationship\Assets\Entities\Person' isn't allowed for relation defined in entity 'obo\Tests\TestCases\Relationship\Assets\Entities\Contacts\InterfacedAddress' and property 'owner' because it must implement interface 'obo\Tests\Assets\IAddressEntity'");
    }

}

(new DynamicInterfaceTest())->run();
