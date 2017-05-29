<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

/**
 * @testCase
 */
class EntityManagerTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;

    private static $contactData = [
        "name" => "John Doe"
    ];

    private static $addressData = [
        "street" => "My Street",
        "city" => "My City",
        "zip" => "12345"
    ];

    private static $addressWithOwnerData = [
        "owner" => "1:Contact",
        "street" => "My Street 2",
        "city" => "My City 2",
        "zip" => "1234"
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function createContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Address
     */
    protected function createAddress() {
        return Assets\Entities\Contacts\AddressManager::entityFromArray(static::$addressData);
    }

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Address
     */
    protected function createAddressWithOwner() {
        return Assets\Entities\Contacts\AddressManager::entityFromArray(static::$addressWithOwnerData);
    }

    public function testEntityFromArray() {
        $contact = $this->createContact();
        Assert::true($contact instanceof Assets\Entities\Contacts\Contact, "Entity has to be a Contact entity instance");
        $contact->save();

        $address = $this->createAddress();
        Assert::true($address instanceof Assets\Entities\Contacts\Address, "Entity has to be an Address entity instance");

        $contact->addresses->add($address);
        $contact->addresses->remove($address);

        $addressWithOwner = $this->createAddressWithOwner();

        Assert::same($contact, $addressWithOwner->owner);
    }

    public function testCollection() {
        $contact = $this->createContact();
        $address = $this->createAddress();
        $contact->save();
        $contact->addresses->add($address);
        $contact->addresses->remove($address);

        Assert::exception(
            function () use ($contact, $address) {
                $contact->addresses->remove($address);
            },
            "\\obo\\Exceptions\\EntityNotFoundException"
        );

        Assert::exception(
            function () use ($contact) {
                $contact->addresses->add($contact);
            },
            "\\obo\\Exceptions\\BadDataTypeException"
        );

    }

    public function testEntity() {
        Assert::exception(
            function () {
                \obo\Tests\Assets\Entities\Contacts\ContactManager::contact(static::DEFAULT_CONTACT_ID);
            },
            "\\obo\\Exceptions\\EntityNotFoundException"
        );

        $contact = $this->createContact();
        Assert::true($contact instanceof Assets\Entities\Contacts\Contact, "Entity has to be a Contact entity instance");
        Assert::false($contact->isBasedInRepository());
        $contact->save();
        Assert::true($contact->isBasedInRepository());
        $contact->delete();
        Assert::true($contact->isDeleted());
    }

}

$testCase = new EntityManagerTest();
$testCase->run();
