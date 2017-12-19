<?php

namespace obo\Tests\TestCases\Relationships\Relationship;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class DynamicInterfaceTest extends \Tester\TestCase {

    public function testCreateInterfacedRelation() {
        $contact = \obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts\InterfacedContactManager::contact([])->save();
        $address = \obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts\InterfacedAddressManager::address([])->save();
        $address->owner = $contact;

        \Tester\Assert::same($contact, $address->owner);
    }

    public function testCreateNonInterfacedRelation() {
        \Tester\Assert::exception(function () {
            $person = \obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\PersonManager::person([])->save();
            $address = \obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts\InterfacedAddressManager::address([])->save();
            $address->owner = $person;
        }, \obo\Exceptions\PropertyAccessException::class, "Entity with class 'obo\Tests\TestCases\\Relationships\Relationship\Assets\Entities\Person' isn't allowed for relation defined in entity 'obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\Contacts\InterfacedAddress' and property 'owner' because it must implement interface 'obo\Tests\TestCases\Relationships\Relationship\Assets\Entities\IAddressEntity'");
    }

}

(new DynamicInterfaceTest())->run();
