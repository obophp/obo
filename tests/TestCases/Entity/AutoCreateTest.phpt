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
    const DEFAULT_NOTE_TEXT = "default text";

    public function testReadAutoCreateRelationshipOnNonpersistentOwner() {
        $contact = $this->createNonpersistentContact();

        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
    }

    public function testWriteAutoCreateRelationshipOnNonpersistentOwner() {
        $contact = $this->createNonpersistentContact();

        $contact->setValuesPropertiesFromArray(["administrativeEmail_value" => static::DEFAULT_CONTACT_EMAIL]);
        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
        \Tester\Assert::same(self::DEFAULT_CONTACT_EMAIL, $contact->administrativeEmail->value);
    }

    public function testReadAutoCreateRelationshipOnPersistentOwner() {
        $contact = $this->createPersistentContact();

        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
    }

    public function testWriteAutoCreateRelationshipOnPersistentOwner() {
        $contact = $this->createPersistentContact();

        $contact->setValuesPropertiesFromArray(["administrativeEmail_value" => static::DEFAULT_CONTACT_EMAIL]);
        \Tester\Assert::true($contact->administrativeEmail instanceof \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\Email);
        \Tester\Assert::same(self::DEFAULT_CONTACT_EMAIL, $contact->administrativeEmail->value);
    }

    public function testReadAutoCreateInverseRelationshipOnNonpersistentOwner() {
        $administrativeEmail = $this->createNonpersistentAdministrativeEmail();

        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
    }

    public function testWriteAutoCreateInverseRelationshipOnNonpersistentOwner() {
        $administrativeEmail = $this->createNonpersistentAdministrativeEmail();

        $administrativeEmail->setValuesPropertiesFromArray(["contact_name" => static::DEFAULT_CONTACT_NAME]);
        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
        \Tester\Assert::same(static::DEFAULT_CONTACT_NAME, $administrativeEmail->contact->name);
    }

    public function testReadAutoCreateInverseRelationshipOnPersistentOwner() {
        $administrativeEmail = $this->createPersistentAdministrativeEmail();

        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
    }

    public function testWriteAutoCreateInverseRelationshipOnPersistentOwner() {
        $administrativeEmail = $this->createPersistentAdministrativeEmail();

        $administrativeEmail->setValuesPropertiesFromArray(["contact_name" => static::DEFAULT_CONTACT_NAME]);
        \Tester\Assert::true($administrativeEmail->contact instanceof \obo\Tests\Assets\Entities\Contacts\Contact);
        \Tester\Assert::same(static::DEFAULT_CONTACT_NAME, $administrativeEmail->contact->name);
    }

    public function testReadAutoCreateDynamicRelationshipOnNonpersistentOwner() {
        $note = $this->createNonpersistentNote();

        \Tester\Assert::true($note->owner instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact);
    }

    public function testWriteAutoCreateDynamicRelationshipOnNonpersistentOwner() {
        $note = $this->createNonpersistentNote();

        $note->setValuesPropertiesFromArray(["owner_name" => static::DEFAULT_CONTACT_NAME]);
        \Tester\Assert::true($note->owner instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact);
        \Tester\Assert::same(static::DEFAULT_CONTACT_NAME, $note->owner->name);
    }

    public function testReadAutoCreateDynamicRelationshipOnPersistentOwner() {
        $note = $this->createPersistentNote();

        \Tester\Assert::true($note->owner instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact);
    }

    public function testWriteAutoCreateDynamicRelationshipOnPersistentOwner() {
        $note = $this->createPersistentNote();

        $note->setValuesPropertiesFromArray(["owner_name" => static::DEFAULT_CONTACT_NAME]);
        \Tester\Assert::true($note->owner instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact);
        \Tester\Assert::same(static::DEFAULT_CONTACT_NAME, $note->owner->name);
    }

    public function testReadAutoCreateDynamicInverseRelationshipOnNonpersistentOwner() {
        $contact = $this->createNonpersistentContact();

        \Tester\Assert::true($contact->note instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Notes\Note);
        \Tester\Assert::same($contact->entityInformation()->name, $contact->note->ownerEntityName );
    }

    public function testWriteAutoCreateDynamicInverseRelationshipOnNonpersistentOwner() {
        $contact = $this->createNonpersistentContact();

        $contact->setValuesPropertiesFromArray(["note_text" => static::DEFAULT_NOTE_TEXT]);
        \Tester\Assert::true($contact->note instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Notes\Note);
        \Tester\Assert::same($contact->entityInformation()->name, $contact->note->ownerEntityName );
        \Tester\Assert::same(static::DEFAULT_NOTE_TEXT, $contact->note->text);
    }

    public function testReadAutoCreateDynamicInverseRelationshipOnPersistentOwner() {
        $contact = $this->createPersistentContact();

        \Tester\Assert::true($contact->note instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Notes\Note);
        \Tester\Assert::same($contact->entityInformation()->name, $contact->note->ownerEntityName );
    }

    public function testWriteAutoCreateDynamicInverseRelationshipOnPersistentOwner() {
        $contact = $this->createPersistentContact();

        $contact->setValuesPropertiesFromArray(["note_text" => static::DEFAULT_NOTE_TEXT]);
        \Tester\Assert::true($contact->note instanceof \obo\Tests\TestCases\Entity\Assets\Entities\Notes\Note);
        \Tester\Assert::same($contact->entityInformation()->name, $contact->note->ownerEntityName );
        \Tester\Assert::same(static::DEFAULT_NOTE_TEXT, $contact->note->text);
    }

    protected function createPersistentContact() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\ContactManager::contact([])->save();
    }

    protected function createNonpersistentContact() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\ContactManager::contact([]);
    }

    protected function createPersistentAdministrativeEmail() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact\AdministrativeEmailManager::administrativeEmail([])->save();
    }

    protected function createNonpersistentAdministrativeEmail() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Contacts\Contact\AdministrativeEmailManager::administrativeEmail([]);
    }

    protected function createPersistentNote() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::note(["ownerEntityName" => "TestCases\Entity\Assets\Entities\Contacts\Contact"])->save();
    }

    protected function createNonpersistentNote() {
        return \obo\Tests\TestCases\Entity\Assets\Entities\Notes\NoteManager::note(["ownerEntityName" => "TestCases\Entity\Assets\Entities\Contacts\Contact"]);
    }

}

(new AutoCreateTest())->run();
