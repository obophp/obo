<?php

namespace obo\Tests;

use Tester\Assert;

require __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";

/**
 * @testCase
 */
class EntityTest extends \Tester\TestCase {

    const DEFAULT_CONTACT_ID = 1;
    const DEFAULT_CONTACT_NAME = "John Doe";
    const DEFAULT_ADDRESS_STREET = "West Olive Avenue";
    const DEFAULT_ADDRESS_CITY = "Burbank";
    const DEFAULT_ADDRESS_ZIP = "CA 91505-5512";
    const DEFAULT_CONTACT_PHONE = "777 777 777";
    const DEFAULT_CONTACT_EMAIL = "john.doe@mail.com";
    const DEFAULT_NOTE = "Default note";

    private static $simpleContactData = [
        "id" => self::DEFAULT_CONTACT_ID,
        "name" => self::DEFAULT_CONTACT_NAME
    ];

    private static $extendedContactData = [
        "name" => self::DEFAULT_CONTACT_NAME,
        "note" => self::DEFAULT_NOTE,
        "addresses__0_street" => self::DEFAULT_ADDRESS_STREET,
        "addresses__0_city" => self::DEFAULT_ADDRESS_CITY,
        "addresses__0_zip" => self::DEFAULT_ADDRESS_ZIP,
        "addresses__0_note_text" => self::DEFAULT_NOTE,
        "defaultPhone_value" => self::DEFAULT_CONTACT_PHONE,
        "phones__0_value" => self::DEFAULT_CONTACT_PHONE,
        "phones__1_value" => self::DEFAULT_CONTACT_PHONE,
    ];

    /**
     * @return \obo\Tests\Assets\Entities\Contacts\Contact
     */
    protected function getSimpleContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$simpleContactData);
    }

    protected function getExtendedContact() {
        return Assets\Entities\Contacts\ContactManager::entityFromArray(static::$extendedContactData);
    }

    public function testPropertiesAsArray() {
        $contact = $this->getSimpleContact();
        Assert::equal(static::$simpleContactData, $contact->propertiesAsArray(["id" => true, "name" => true]));
    }

    public function testEntityWithPrimaryPropertyValue () {
        \Tester\Assert::exception(function(){Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(999);}, "\obo\Exceptions\EntityNotFoundException");
        \Tester\Assert::exception(function(){Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(0);}, "\obo\Exceptions\EntityNotFoundException");
        \Tester\Assert::same($this->getSimpleContact(), Assets\Entities\Contacts\ContactManager::entityWithPrimaryPropertyValue(static::DEFAULT_CONTACT_ID));
    }

    public function testSetProperties() {
        $data = [
            "name" => self::DEFAULT_CONTACT_NAME,
            "note" => self::DEFAULT_NOTE,
            "addresses__0_street" => self::DEFAULT_ADDRESS_STREET,
            "addresses__0_city" => self::DEFAULT_ADDRESS_CITY,
            "addresses__0_zip" => self::DEFAULT_ADDRESS_ZIP,
            "addresses__0_note_text" => self::DEFAULT_NOTE,
            "defaultPhone_value" => self::DEFAULT_CONTACT_PHONE,
            "phones__0_value" => self::DEFAULT_CONTACT_PHONE,
            "phones__1_value" => self::DEFAULT_CONTACT_PHONE,
        ];

        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact([]);
        $contact->setValuesPropertiesFromArray(static::$extendedContactData);

        \Tester\Assert::same(["name" => self::DEFAULT_CONTACT_NAME, "note" => self::DEFAULT_NOTE], $contact->propertiesAsArray(["name" => true, "note" => true]) );
        \Tester\Assert::same(self::DEFAULT_CONTACT_PHONE, $contact->defaultPhone->value);
        \Tester\Assert::same(self::DEFAULT_CONTACT_PHONE, $contact->phones->___0->value);
        \Tester\Assert::same(self::DEFAULT_CONTACT_PHONE, $contact->phones->___1->value);
        \Tester\Assert::same(self::DEFAULT_NOTE, $contact->addresses->___0->note->text);
    }

    public function testChangePropertiesOfStoredEntity() {
        $dataStorageMock = $this->createDataStorageMockForChangePropertiesOfStoredEntity();
        \obo\Tests\Assets\Entities\Contacts\ContactManager::setDataStorage($dataStorageMock);
        \obo\Tests\Assets\Entities\Contacts\AddressManager::setDataStorage($dataStorageMock);

        $contact = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact(1);

        $data = ["name" => "new_" . \obo\Tests\EntityTest::DEFAULT_CONTACT_NAME, "addresses_1_street" => "new_" . self::DEFAULT_ADDRESS_STREET,];
        $contact->changeValuesPropertiesFromArray($data);

        \Tester\Assert::same($data, $contact->propertiesAsArray(["name" => true, "addresses_1_street" => true]));
    }

    public function createDataStorageMockForChangePropertiesOfStoredEntity() {
        $contactSpecification = Assets\Entities\Contacts\ContactManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\ContactManager::constructSelect())
                ->where("{id} = ?", 1);

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($contactSpecification))
                        ->andReturn([[
                            "id" => 1,
                            "name" => \obo\Tests\EntityTest::DEFAULT_CONTACT_NAME,
                            "note" => \obo\Tests\EntityTest::DEFAULT_NOTE,
                        ]]);

        $addressSpecification = \obo\Tests\Assets\Entities\Contacts\AddressManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\AddressManager::constructSelect())
                ->where("AND {id} IN (?) AND {owner} = ? AND {ownerEntity} = ?", ["1"], 1, \obo\Tests\Assets\Entities\Contacts\Contact::entityInformation()->name);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($addressSpecification))
                        ->andReturn([[
                            "id" => 1,
                            "street" => self::DEFAULT_ADDRESS_STREET,
                            "city" => self::DEFAULT_ADDRESS_CITY,
                            "zip" => self::DEFAULT_ADDRESS_ZIP,
                        ]]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

    public function testIdentityMapper() {
        $data = [
            "name" => self::DEFAULT_CONTACT_NAME,
            "note" => self::DEFAULT_NOTE,
        ];

        $e1 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);
        $e2 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);

        \Tester\Assert::true(!\obo\obo::$identityMapper->isMappedEntity($e1));
        \Tester\Assert::true(!\obo\obo::$identityMapper->isMappedEntity($e2));

        \Tester\Assert::notSame($e1, $e2);

        $data = [
            "id" => 1,
            "name" => self::DEFAULT_CONTACT_NAME,
            "note" => self::DEFAULT_NOTE,
        ];

        $e3 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);
        $e4 = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact($data);

        \Tester\Assert::true(\obo\obo::$identityMapper->isMappedEntity($e3));
        \Tester\Assert::true(\obo\obo::$identityMapper->isMappedEntity($e4));

        \Tester\Assert::same($e3, $e4);
    }

    public function testSave() {
        $contact = $this->getSimpleContact();
        Assert::false($contact->isBasedInRepository());
        $contact->save();
        Assert::true($contact->isBasedInRepository());
    }

    public function testDelete() {
        $contact = $this->getSimpleContact();
        Assert::false($contact->isDeleted());
        $contact->delete();
        Assert::true($contact->isDeleted());
    }

    public function testMetaData() {
        $metaDataHash = "5f66bec2ccf6363e028619f2b340f604";
        \Tester\Assert::same($metaDataHash, \md5(\preg_replace("#\"objectIdentificationKey\"\;s\:32\:\"([a-z0-9]{32})\"#", "\"objectIdentificationKey\";s:32:\"00000000000000000000000000000000\"", \serialize($this->getExtendedContact()->metaData()))));
    }

    public function testChanged() {
        \obo\Tests\Assets\Entities\Contacts\ContactManager::setDataStorage($this->createDataStorageMockForTestChanged());
        $entity = \obo\Tests\Assets\Entities\Contacts\ContactManager::contact(1);
        \Tester\Assert::false($entity->changed());
        $originalName = $entity->name;
        $entity->name = "ChangedName";
        \Tester\Assert::true($entity->changed());
        $entity->name = $originalName;
        \Tester\Assert::false($entity->changed());


        $originalNoPersitedProperty = $entity->noPersitedProperty;
        $entity->noPersitedProperty = "ChangedValue";
        \Tester\Assert::false($entity->changed(true));
        \Tester\Assert::true($entity->changed(false));
    }

    public function createDataStorageMockForTestChanged() {
        $specification = Assets\Entities\Contacts\ContactManager::queryCarrier()
                ->select(\obo\Tests\Assets\Entities\Contacts\ContactManager::constructSelect())
                ->where("{id} = ?", 1);

        $dataStorageMock = \Mockery::mock(new \obo\Tests\Assets\DataStorage);

        $dataStorageMock->shouldReceive("dataForQuery")
                        ->with(\equalTo($specification))
                        ->andReturn([[
                            "id" => 1,
                            "name" => \obo\Tests\EntityTest::DEFAULT_CONTACT_NAME,
                            "note" => \obo\Tests\EntityTest::DEFAULT_NOTE
                        ]]);

        $dataStorageMock->setDefaultDataForQueryBehavior($dataStorageMock);

        return $dataStorageMock;
    }

    public function testDataToStore() {
        $contact = $this->getSimpleContact();
        \Tester\Assert::same($contact->changedProperties($contact->entityInformation()->persistablePropertiesNames), $contact->dataToStore());

        $contact->save();
        \Tester\Assert::same([], $contact->dataToStore());

        $contact->name = "changed " . static::DEFAULT_CONTACT_NAME;
        \Tester\Assert::same(["name" => "changed John Doe"], $contact->dataToStore());

        $contact->administrativeEmail->value = static::DEFAULT_CONTACT_EMAIL;
        $contact->defaultPhone->value = static::DEFAULT_CONTACT_PHONE;
        $contact->save();

        $contact->administrativeEmail->save();
        $contact->defaultPhone->save();
        \Tester\Assert::same(["administrativeEmail" => 3], $contact->dataToStore());
    }

}

$testCase = new EntityTest();
$testCase->run();
