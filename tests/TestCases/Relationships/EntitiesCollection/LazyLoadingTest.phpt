<?php

namespace obo\Tests\TestCases\Relationships\EntitiesCollection;

use Tester\Assert;

require __DIR__ . "/../../../bootstrap.php";

/**
 * @testCase
 */
class LazyLoadingTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;
    const DEFAULT_CONTACT_NAME = "John Doe";
    const DEFAULT_CONTACT_PHONE = "777 777 777";
    const DEFAULT_NOTE = "Default note";

    private static $contactData = [
        "name" => self::DEFAULT_CONTACT_NAME
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getContact() {
        return \obo\Tests\Assets\Entities\Contacts\ContactManager::entityFromArray(static::$contactData);
    }

    public function testDuringTouchOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();

        $contact->phones;
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringCountOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();

        $contact->phones->count();
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringAddOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();

        $contact->phones->add(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE]));
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringAddNewOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();

        $phone = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringRemoveOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();
        $phone = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->phones->remove($phone);
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringReadOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();

        $phone1 = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        $phone2 = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->phones[$phone1->primaryPropertyValue()];
        $contact->phones[$phone2->primaryPropertyValue()];
        \Tester\Assert::notEqual($contact->phones->getSpecification()->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect()), $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringAsArrayOnNonpersistentOwner() {
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $contact = $this->getContact();

        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->phones->asArray([]);
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));

        $contact->phones->asArray();
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringTouchOnPersistentOwner() {
        $contact = $this->getContact()->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());

        $contact->phones;
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringCountOnpersistentOwner() {
        $contact = $this->getContact()->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());

        $contact->phones->count();
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity()));
    }

    public function testDuringAddOnPersistentOwner() {
        $contact = $this->getContact();
        $phone = \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::phone(["value" => static::DEFAULT_CONTACT_PHONE])->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());

        $contact->phones->add($phone);
        \Tester\Assert::null($phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringAddNewOnPersistentOwner() {
        $contact = $this->getContact()->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());

        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        \Tester\Assert::notEqual($contact->phones->getSpecification()->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect()), $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringRemoveOnPersistentOwner() {
        $contact = $this->getContact()->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());
        $phone = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->phones->remove($phone);
        \Tester\Assert::notEqual($contact->phones->getSpecification()->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect()), $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringReadOnPersistentOwner() {
        $contact = $this->getContact()->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());

        $phone1 = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        $phone2 = $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->phones[$phone1->primaryPropertyValue()];
        $contact->phones[$phone2->primaryPropertyValue()];
        \Tester\Assert::notEqual($contact->phones->getSpecification()->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect()), $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function testDuringAsArrayOnPersistentOwner() {
        $contact = $this->getContact()->save();
        \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::setDataStorage($phonesDataStorage = $this->createPhonesDataStorage());

        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);
        $contact->phones->addNew(["value" => static::DEFAULT_CONTACT_PHONE]);

        $contact->phones->asArray([]);
        \Tester\Assert::notEqual($contact->phones->getSpecification()->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect()), $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);

        $contact->phones->asArray();
        \Tester\Assert::equal($contact->phones->getSpecification()->select(\obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::constructSelect()), $phonesDataStorage->getLastEventForTypeAndKey(\obo\Tests\Assets\DataStorage::EVENT_TYPE_DATA_FOR_QUERY, \obo\Tests\Assets\Entities\Contacts\AdditionalInformation\PhoneManager::classNameManagedEntity())["data"]["queryCarrier"]);
    }

    public function createPhonesDataStorage() {
        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);
        return $dataStorageMock;
    }

}

(new LazyLoadingTest())->run();
