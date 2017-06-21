<?php

namespace obo\Tests\TestCases\Entity;

use Tester\Assert;

require __DIR__ . "/../../bootstrap.php";

/**
 * @testCase
 */
class AutoCreateTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_EMAIL = "john.doe@mail.com";
    const DEFAULT_CONTACT_NAME = "John Doe";

    public function testReadOnNonPersistentOwner() {
        $contact = $this->createNonPersistentContact();
        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
    }

    public function testReadOnPersistentOwner() {
        $contact = $this->createPersistentContact();
        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
    }

    public function testWriteOnNonPersistentOwner() {
        $contact = $this->createNonPersistentContact();
        $contact->setValuesPropertiesFromArray(["administrativeEmail_value" => static::DEFAULT_CONTACT_EMAIL]);
        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
        \Tester\Assert::same(self::DEFAULT_CONTACT_EMAIL, $contact->administrativeEmail->value);
    }

    public function testWriteOnPersistentOwner() {
        $contact = $this->createPersistentContact();
        $contact->setValuesPropertiesFromArray(["administrativeEmail_value" => static::DEFAULT_CONTACT_EMAIL]);
        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
        \Tester\Assert::same(self::DEFAULT_CONTACT_EMAIL, $contact->administrativeEmail->value);
    }

    public function testReadOnNonPersistentInverseOwner() {
        $administrativeEmail = $this->createNonPersistentAdministrativeEmail();
        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
    }

    public function testReadOnPersistentInverseOwner() {
        $administrativeEmail = $this->createPersistentAdministrativeEmail();
        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
    }

    public function testWriteOnNonPersistentInverseOwner() {
        $administrativeEmail = $this->createNonPersistentAdministrativeEmail();
        $administrativeEmail->setValuesPropertiesFromArray(["contact_name" => static::DEFAULT_CONTACT_NAME]);
        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
        \Tester\Assert::same(static::DEFAULT_CONTACT_NAME, $administrativeEmail->contact->name);
    }

    public function testWriteOnPersistentInverseOwner() {
        $administrativeEmail = $this->createPersistentAdministrativeEmail();
        $administrativeEmail->setValuesPropertiesFromArray(["contact_name" => static::DEFAULT_CONTACT_NAME]);
        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
        \Tester\Assert::same(static::DEFAULT_CONTACT_NAME, $administrativeEmail->contact->name);
    }

    protected function createPersistentContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::contact([])->save();
    }

    protected function createNonPersistentContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::contact([]);
    }

    protected function createPersistentAdministrativeEmail() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact\AdministrativeEmailManager::administrativeEmail([])->save();
    }

    protected function createNonPersistentAdministrativeEmail() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact\AdministrativeEmailManager::administrativeEmail([]);
    }

}

(new AutoCreateTest())->run();
